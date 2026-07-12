@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
    <div class="mb-4">
        <span class="d-block mono text-uppercase small" style="color:var(--brass-deep); letter-spacing:.1em;">Ringkasan Akun</span>
        <h3 class="mb-0">{{ ucfirst($role) }}</h3>
    </div>

    <div class="row g-3">
        @foreach($stats as $key => $value)
            <div class="col-md-3">
                <div class="ledger-stat">
                    <div class="label">{{ str_replace('_', ' ', $key) }}</div>
                    <div class="value mono">{{ $value }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4 d-flex gap-2">
        @if($role === 'staff')
            <a href="{{ route('submissions.create') }}" class="btn btn-primary">+ Buat Pengajuan Baru</a>
            <a href="{{ route('submissions.index') }}" class="btn btn-outline-secondary">Lihat Semua Pengajuan Saya</a>
        @elseif(in_array($role, ['spv','manager','direktur']))
            <a href="{{ route('approvals.index') }}" class="btn btn-primary">Lihat Approval Masuk</a>
        @elseif($role === 'finance')
            <a href="{{ route('payments.index') }}" class="btn btn-primary">Proses Pembayaran</a>
        @endif
    </div>
@endsection
