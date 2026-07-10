@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
    <h3 class="mb-4">Dashboard - {{ ucfirst($role) }}</h3>

    <div class="row g-3">
        @foreach($stats as $key => $value)
            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="text-muted small text-capitalize">{{ str_replace('_', ' ', $key) }}</div>
                        <div class="fs-2 fw-bold">{{ $value }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4">
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
