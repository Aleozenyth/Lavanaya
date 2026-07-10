<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\Submission;
use App\Services\WorkflowService;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    private const STATUS_MAP = [
        'spv' => 'waiting_spv',
        'manager' => 'waiting_manager',
        'direktur' => 'waiting_director',
    ];

    public function __construct(private WorkflowService $workflow)
    {
    }

    /** Daftar pengajuan yang masuk untuk role approver saat ini */
    public function index(Request $request)
    {
        $role = $request->user()->role->name;
        $status = self::STATUS_MAP[$role] ?? null;

        abort_unless($status, 403);

        $submissions = Submission::where('status', $status)
            ->with(['category', 'user'])
            ->latest('submitted_at')
            ->paginate(10);

        return view('approvals.index', compact('submissions', 'role'));
    }

    public function act(Request $request, Submission $submission)
    {
        $role = $request->user()->role->name;

        $validated = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $approval = $submission->approvals()
            ->where('approver_role', $role)
            ->where('status', 'pending')
            ->orderBy('sequence')
            ->firstOrFail();

        $this->workflow->act($submission, $approval, $validated['decision'], $validated['notes'] ?? null, $request->user()->id);

        return redirect()->route('approvals.index')
            ->with('success', 'Pengajuan ' . $submission->nomor_pengajuan . ' berhasil diproses.');
    }
}
