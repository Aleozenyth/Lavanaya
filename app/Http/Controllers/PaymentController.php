<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Services\WorkflowService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private WorkflowService $workflow)
    {
    }

    public function index()
    {
        $submissions = Submission::where('status', 'waiting_finance')
            ->with(['category', 'user'])
            ->latest('submitted_at')
            ->paginate(10);

        return view('payments.index', compact('submissions'));
    }

    public function process(Request $request, Submission $submission)
    {
        abort_unless($submission->status === 'waiting_finance', 422, 'Pengajuan tidak dalam status menunggu Finance.');

        $validated = $request->validate([
            'decision' => ['required', 'in:paid,rejected'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->workflow->processFinance($submission, $validated['decision'], $validated['notes'] ?? null, $request->user()->id);

        return redirect()->route('payments.index')
            ->with('success', 'Pengajuan ' . $submission->nomor_pengajuan . ' berhasil diproses Finance.');
    }
}
