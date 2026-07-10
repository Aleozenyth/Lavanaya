@extends('layouts.app')
@section('title', 'Pengajuan Saya')

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
        <h3 class="mb-0">Pengajuan Saya</h3>
        <a href="{{ route('submissions.create') }}" class="btn btn-primary">+ Buat Pengajuan Baru</a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nomor</th>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Nilai</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions as $submission)
                        <tr>
                            <td>{{ $submission->nomor_pengajuan }}</td>
                            <td>{{ date('d-m-Y', strtotime($submission->tanggal_pengajuan)) }}</td>
                            <td>{{ $submission->category->name }}</td>
                            <td>Rp {{ number_format($submission->nilai, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge bg-{{ $statusColors[$submission->status] ?? 'secondary' }}">
                                    {{ $submission->statusLabel() }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('submissions.show', $submission) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada pengajuan.</td>
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
