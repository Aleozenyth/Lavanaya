@extends('layouts.app')
@section('title', 'Detail Pengajuan')

@php
    $statusColors = [
        'draft' => 'secondary',
        'submitted' => 'info',
        'waiting_spv' => 'warning',
        'waiting_manager' => 'warning',
        'waiting_director' => 'warning',
        'waiting_finance' => 'primary',
        'paid' => 'success',
        'rejected' => 'danger',
    ];
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Detail Pengajuan {{ $submission->nomor_pengajuan }}</h3>
        <span class="badge bg-{{ $statusColors[$submission->status] ?? 'secondary' }} fs-6">
            {{ $submission->statusLabel() }}
        </span>
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
                            <td>{{ date('d-m-Y', strtotime($submission->tanggal_pengajuan)) }}</td>
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
                            <td>Rp {{ number_format($submission->nilai, 0, ',', '.') }}</td>
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
                        <p class="mb-1">Status: <span class="badge bg-{{ $submission->payment->status === 'paid' ? 'success' : 'danger' }}">{{ ucfirst($submission->payment->status) }}</span></p>
                        <p class="mb-1">Diproses oleh: {{ $submission->payment->processor->name }}</p>
                        @if($submission->payment->notes)
                            <p class="mb-1">Catatan: {{ $submission->payment->notes }}</p>
                        @endif
                        @if($submission->payment->paid_at)
                            <p class="mb-0 text-muted small">{{ $submission->payment->paid_at->format('d-m-Y H:i') }}</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header">Riwayat Approval</div>
                <ul class="list-group list-group-flush">
                    @forelse($submission->approvals as $approval)
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <strong class="text-uppercase">{{ $approval->approver_role }}</strong>
                                <span class="badge bg-{{ $approval->status === 'approved' ? 'success' : ($approval->status === 'rejected' ? 'danger' : 'secondary') }}">
                                    {{ ucfirst($approval->status) }}
                                </span>
                            </div>
                            @if($approval->approver)
                                <div class="small text-muted">oleh {{ $approval->approver->name }}</div>
                            @endif
                            @if($approval->notes)
                                <div class="small mt-1">"{{ $approval->notes }}"</div>
                            @endif
                            @if($approval->acted_at)
                                <div class="small text-muted">{{ date('d-m-Y H:i', strtotime($approval->acted_at)) }}</div>
                            @endif
                        </li>
                    @empty
                        <li class="list-group-item text-muted">Tidak ada tahap approval (langsung ke Finance).</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Kembali</a>
    </div>
@endsection
