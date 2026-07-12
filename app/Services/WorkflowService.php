<?php

namespace App\Services;

use App\Models\Approval;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Submission;
use Illuminate\Support\Facades\DB;

class WorkflowService
{
    // Threshold sesuai soal
    public const THRESHOLD_SPV_MANAGER = 5_000_000;
    public const THRESHOLD_DIRECTOR = 10_000_000;

    /**
     * Menentukan urutan role approval berdasarkan kategori & nilai pengajuan.
     *
     * Kondisi 1: Kategori = PO Produk -> langsung Direktur
     * Kondisi 2: Bukan PO Produk & nilai > 5jt -> SPV -> Manager
     * Kondisi 3: nilai > 10jt -> setelah Manager lanjut ke Direktur
     * (Asumsi) nilai <= 5jt & bukan PO Produk -> cukup approval SPV saja
     *
     * @return string[] daftar role secara berurutan, contoh: ['spv','manager','direktur']
     */
    public function determineApprovalChain(Category $category, float $nilai): array
    {
        if ($category->isPoProduk()) {
            return ['direktur'];
        }

        if ($nilai > self::THRESHOLD_DIRECTOR) {
            return ['spv', 'manager', 'direktur'];
        }

        if ($nilai > self::THRESHOLD_SPV_MANAGER) {
            return ['spv', 'manager'];
        }

        return ['spv'];
    }

    /**
     * Status "waiting_xxx" pertama sesuai role pertama dalam chain.
     */
    public function firstWaitingStatus(array $chain): string
    {
        return match ($chain[0]) {
            'spv' => 'waiting_spv',
            'manager' => 'waiting_manager',
            'direktur' => 'waiting_director',
            default => 'submitted',
        };
    }

    private function waitingStatusForRole(string $role): string
    {
        return match ($role) {
            'spv' => 'waiting_spv',
            'manager' => 'waiting_manager',
            'direktur' => 'waiting_director',
            default => 'submitted',
        };
    }

    /**
     * Cek ketersediaan budget kategori untuk bulan berjalan (Kondisi 4 & 7).
     *
     * Menerima Carbon/DateTimeInterface ATAU string tanggal ("Y-m-d"),
     * supaya tidak bergantung pada cast model selalu aktif di semua environment.
     */
    public function getBudgetFor(Category $category, $date): ?Budget
    {
        $date = $date instanceof \DateTimeInterface
            ? $date
            : \Carbon\Carbon::parse((string) $date);

        return Budget::where('category_id', $category->id)
            ->where('year', $date->format('Y'))
            ->where('month', (int) $date->format('n'))
            ->first();
    }

    /**
     * Submit pengajuan: cek budget, buat rantai approval, set status awal.
     */
    public function submit(Submission $submission): Submission
    {
        return DB::transaction(function () use ($submission) {
            $category = $submission->category;
            $budget = $this->getBudgetFor($category, $submission->tanggal_pengajuan);

            // Kondisi 4: budget kategori tidak mencukupi -> langsung ditolak
            if ($budget && ! $budget->hasEnoughBudget((float) $submission->nilai)) {
                $submission->update([
                    'status' => 'rejected',
                    'rejection_reason' => 'Budget kategori tidak mencukupi.',
                    'submitted_at' => now(),
                ]);

                return $submission;
            }

            $chain = $this->determineApprovalChain($category, (float) $submission->nilai);

            foreach ($chain as $i => $role) {
                Approval::create([
                    'submission_id' => $submission->id,
                    'sequence' => $i + 1,
                    'approver_role' => $role,
                    'status' => 'pending',
                ]);
            }

            $submission->update([
                'status' => $this->firstWaitingStatus($chain),
                'submitted_at' => now(),
            ]);

            return $submission;
        });
    }

    /**
     * Proses approve/reject oleh SPV/Manager/Direktur.
     */
    public function act(Submission $submission, Approval $approval, string $decision, ?string $notes, int $actorUserId): void
    {
        DB::transaction(function () use ($submission, $approval, $decision, $notes, $actorUserId) {
            $approval->update([
                'status' => $decision, // 'approved' atau 'rejected'
                'notes' => $notes,
                'approver_user_id' => $actorUserId,
                'acted_at' => now(),
            ]);

            // Kondisi 5: reject di step manapun -> Rejected
            if ($decision === 'rejected') {
                $submission->update([
                    'status' => 'rejected',
                    'rejection_reason' => $notes ?: 'Ditolak oleh ' . $approval->approver_role,
                ]);
                return;
            }

            $next = $submission->approvals()
                ->where('sequence', '>', $approval->sequence)
                ->where('status', 'pending')
                ->orderBy('sequence')
                ->first();

            if ($next) {
                $submission->update(['status' => $this->waitingStatusForRole($next->approver_role)]);
            } else {
                // Kondisi 6: semua approval selesai -> Menunggu Finance
                $submission->update(['status' => 'waiting_finance']);
            }
        });
    }

    /**
     * Finance memproses pembayaran (Kondisi 7).
     */
    public function processFinance(Submission $submission, string $decision, ?string $notes, int $actorUserId): void
    {
        DB::transaction(function () use ($submission, $decision, $notes, $actorUserId) {
            $category = $submission->category;
            $budget = $this->getBudgetFor($category, $submission->tanggal_pengajuan);

            if ($decision === 'paid') {
                if ($budget && ! $budget->hasEnoughBudget((float) $submission->nilai)) {
                    // saldo tidak cukup -> ditolak
                    $submission->update([
                        'status' => 'rejected',
                        'rejection_reason' => 'Saldo tidak mencukupi saat proses Finance.',
                    ]);

                    \App\Models\Payment::create([
                        'submission_id' => $submission->id,
                        'processed_by' => $actorUserId,
                        'amount' => $submission->nilai,
                        'status' => 'rejected',
                        'notes' => $notes,
                    ]);

                    return;
                }

                if ($budget) {
                    $budget->increment('used_amount', (float) $submission->nilai);
                }

                $submission->update(['status' => 'paid']);

                \App\Models\Payment::create([
                    'submission_id' => $submission->id,
                    'processed_by' => $actorUserId,
                    'amount' => $submission->nilai,
                    'status' => 'paid',
                    'notes' => $notes,
                    'paid_at' => now(),
                ]);
            } else {
                $submission->update([
                    'status' => 'rejected',
                    'rejection_reason' => $notes ?: 'Ditolak oleh Finance.',
                ]);

                \App\Models\Payment::create([
                    'submission_id' => $submission->id,
                    'processed_by' => $actorUserId,
                    'amount' => $submission->nilai,
                    'status' => 'rejected',
                    'notes' => $notes,
                ]);
            }
        });
    }

    /**
     * Generate nomor pengajuan otomatis: PGJ-YYYYMM-XXXX
     */
    public function generateNomorPengajuan(): string
    {
        $prefix = 'PGJ-' . now()->format('Ym') . '-';
        $last = Submission::where('nomor_pengajuan', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        $next = 1;
        if ($last) {
            $lastNumber = (int) substr($last->nomor_pengajuan, -4);
            $next = $lastNumber + 1;
        }

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
