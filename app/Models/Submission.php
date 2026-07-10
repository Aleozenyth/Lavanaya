<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_pengajuan',
        'tanggal_pengajuan',
        'user_id',
        'category_id',
        'nilai',
        'deskripsi',
        'lampiran_path',
        'lampiran_original_name',
        'status',
        'rejection_reason',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengajuan' => 'date',
            'submitted_at' => 'datetime',
            'nilai' => 'decimal:2',
        ];
    }

    public const STATUS_LABELS = [
        'draft' => 'Draft',
        'submitted' => 'Submitted',
        'waiting_spv' => 'Waiting SPV Approval',
        'waiting_manager' => 'Waiting Manager Approval',
        'waiting_director' => 'Waiting Director Approval',
        'waiting_finance' => 'Waiting Finance',
        'paid' => 'Paid',
        'rejected' => 'Rejected',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class)->orderBy('sequence');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    /**
     * Approval yang sedang menunggu aksi (step pertama yang masih pending).
     */
    public function currentApproval(): ?Approval
    {
        return $this->approvals()->where('status', 'pending')->orderBy('sequence')->first();
    }
}
