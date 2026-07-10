<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Submission;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    public function __construct(private WorkflowService $workflow)
    {
    }

    /** Staff: daftar + riwayat pengajuan miliknya */
    public function index(Request $request)
    {
        $submissions = Submission::where('user_id', $request->user()->id)
            ->with('category')
            ->latest()
            ->paginate(10);

        return view('submissions.index', compact('submissions'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('submissions.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal_pengajuan' => ['required', 'date'],
            'category_id' => ['required', 'exists:categories,id'],
            'nilai' => ['required', 'numeric', 'min:1'],
            'deskripsi' => ['nullable', 'string'],
            'lampiran' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'], // 5MB
        ], [
            'lampiran.max' => 'Ukuran file maksimal 5 MB.',
            'lampiran.mimes' => 'File harus berformat PDF, JPG, JPEG, atau PNG.',
        ]);

        $path = $request->file('lampiran')->store('lampiran', 'public');

        $submission = Submission::create([
            'nomor_pengajuan' => $this->workflow->generateNomorPengajuan(),
            'tanggal_pengajuan' => $validated['tanggal_pengajuan'],
            'user_id' => $request->user()->id,
            'category_id' => $validated['category_id'],
            'nilai' => $validated['nilai'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'lampiran_path' => $path,
            'lampiran_original_name' => $request->file('lampiran')->getClientOriginalName(),
            'status' => 'draft',
        ]);

        $this->workflow->submit($submission->fresh('category'));

        return redirect()->route('submissions.show', $submission)
            ->with('success', 'Pengajuan berhasil dibuat dan dikirim untuk approval.');
    }

    public function show(Request $request, Submission $submission)
    {
        $this->authorizeView($request, $submission);

        $submission->load(['category', 'user', 'approvals.approver', 'payment']);

        return view('submissions.show', compact('submission'));
    }

    public function download(Request $request, Submission $submission)
    {
        $this->authorizeView($request, $submission);

        return Storage::disk('public')->download($submission->lampiran_path, $submission->lampiran_original_name);
    }

    private function authorizeView(Request $request, Submission $submission): void
    {
        $user = $request->user();
        $role = $user->role->name;

        // Staff hanya boleh lihat pengajuan miliknya sendiri.
        if ($role === 'staff' && $submission->user_id !== $user->id) {
            abort(403);
        }
    }
}
