@extends('layouts.app')
@section('title', 'Detail Pengajuan')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 ref-number">{{ $submission->nomor_pengajuan }}</h3>
        <x-stamp :status="$submission->status" />
    </div>

    <div class="row g-4">
        <div class="col-md-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header">Informasi Pengajuan</div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th style="width:220px">Nomor Pengajuan</th>
                            <td>{{ $submission->nomor_pengajuan }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Pengajuan</th>
                            <td>{{ \Carbon\Carbon::parse($submission->tanggal_pengajuan)->format('d-m-Y') }}</td>
                        </tr>
                        <tr>
                            <th>Nama Pengaju</th>
                            <td>{{ $submission->user->name }}</td>
                        </tr>
                        <tr>
                            <th>Kategori</th>
                            <td>{{ $submission->category->name }}</td>
                        </tr>
                        <tr>
                            <th>Nilai Pengajuan</th>
                            <td class="money">Rp {{ number_format($submission->nilai, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Deskripsi</th>
                            <td>{{ $submission->deskripsi ?: '-' }}</td>
                        </tr>
                        <tr>
                            <th>Lampiran</th>
                            <td>
                                @if($submission->lampiran_path)
                                    <a href="{{ route('submissions.download', $submission) }}">
                                        <i class="bi bi-paperclip"></i> {{ $submission->lampiran_original_name }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @if($submission->status === 'rejected' && $submission->rejection_reason)
                            <tr>
                                <th>Alasan Ditolak</th>
                                <td class="text-danger">{{ $submission->rejection_reason }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            @if($submission->payment)
                <div class="card shadow-sm">
                    <div class="card-header">Informasi Pembayaran (Finance)</div>
                    <div class="card-body">
                        <p class="mb-2">Status: <x-stamp :status="$submission->payment->status" /></p>
                        <p class="mb-1">Diproses oleh: {{ $submission->payment->processor->name }}</p>
                        @if($submission->payment->notes)
                            <p class="mb-1">Catatan: {{ $submission->payment->notes }}</p>
                        @endif
                        @if($submission->payment->paid_at)
                            <p class="mb-0 text-muted small mono">{{ \Carbon\Carbon::parse($submission->payment->paid_at)->format('d-m-Y H:i') }}</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header">Riwayat Persetujuan</div>
                <div class="card-body">
                    @forelse($submission->approvals as $approval)
                        @php
                            $dotClass = $approval->status === 'approved' ? 'dot-approved' : ($approval->status === 'rejected' ? 'dot-rejected' : 'dot-pending');
                        @endphp
                        <div class="route-item {{ $dotClass }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <strong class="text-uppercase small">{{ $approval->approver_role }}</strong>
                                <x-stamp :status="$approval->status" />
                            </div>
                            @if($approval->approver)
                                <div class="small text-muted mt-1">oleh {{ $approval->approver->name }}</div>
                            @endif
                            @if($approval->notes)
                                <div class="small mt-1">Catatan: {{ $approval->notes }}</div>
                            @endif
                            @if($approval->acted_at)
                                <div class="small text-muted mono">{{ \Carbon\Carbon::parse($approval->acted_at)->format('d-m-Y H:i') }}</div>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted mb-0">Tidak ada tahap approval (langsung ke Finance).</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Kembali</a>
    </div>
@endsection
