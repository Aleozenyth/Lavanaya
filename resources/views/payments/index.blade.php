@extends('layouts.app')
@section('title', 'Proses Pembayaran')

@section('content')
    <h3 class="mb-4">Pengajuan Menunggu Finance</h3>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nomor</th>
                        <th>Pengaju</th>
                        <th>Kategori</th>
                        <th>Nilai</th>
                        <th>Tanggal Diajukan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions as $submission)
                        @php
                            $budget = app(\App\Services\WorkflowService::class)->getBudgetFor($submission->category, $submission->tanggal_pengajuan);
                        @endphp
                        <tr>
                            <td class="ref-number">
                                <a href="{{ route('submissions.show', $submission) }}">{{ $submission->nomor_pengajuan }}</a>
                            </td>
                            <td>{{ $submission->user->name }}</td>
                            <td>{{ $submission->category->name }}</td>
                            <td class="money">Rp {{ number_format($submission->nilai, 0, ',', '.') }}</td>
                            <td class="mono small text-muted">{{ $submission->submitted_at ? \Carbon\Carbon::parse($submission->submitted_at)->format('d-m-Y H:i') : '-' }}</td>
                            <td>
                                @if($budget)
                                    <div class="small text-muted mb-1 money">
                                        Sisa saldo: Rp {{ number_format($budget->remaining, 0, ',', '.') }}
                                    </div>
                                @endif
                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#payModal{{ $submission->id }}">
                                    Proses Bayar
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectPayModal{{ $submission->id }}">
                                    Reject
                                </button>
                            </td>
                        </tr>

                        <!-- Pay Modal -->
                        <div class="modal fade" id="payModal{{ $submission->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form method="POST" action="{{ route('payments.process', $submission) }}">
                                    @csrf
                                    <input type="hidden" name="decision" value="paid">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Proses Pembayaran {{ $submission->nomor_pengajuan }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Nilai: Rp {{ number_format($submission->nilai, 0, ',', '.') }}</p>
                                            <label class="form-label">Catatan (opsional)</label>
                                            <textarea name="notes" class="form-control" rows="3"></textarea>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-success">Konfirmasi Bayar</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Reject Modal -->
                        <div class="modal fade" id="rejectPayModal{{ $submission->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form method="POST" action="{{ route('payments.process', $submission) }}">
                                    @csrf
                                    <input type="hidden" name="decision" value="rejected">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Reject Pembayaran {{ $submission->nomor_pengajuan }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <label class="form-label">Alasan <span class="text-danger">*</span></label>
                                            <textarea name="notes" class="form-control" rows="3" required></textarea>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-danger">Reject</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Tidak ada pengajuan yang menunggu Finance.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $submissions->links() }}
    </div>
@endsection
