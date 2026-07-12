<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistem Pengajuan Transaksi')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:wght@500;600;700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app-custom.css') }}">
</head>
<body>
@auth
    <nav class="navbar navbar-expand-lg ledger-header">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <small>Modul Persetujuan Internal</small>
                Sistem Pengajuan Transaksi Pengeluaran
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="nav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>
                    @if(auth()->user()->role->name === 'staff')
                        <li class="nav-item"><a class="nav-link" href="{{ route('submissions.index') }}">Pengajuan Saya</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('submissions.create') }}">Buat Pengajuan</a></li>
                    @endif
                    @if(in_array(auth()->user()->role->name, ['spv','manager','direktur']))
                        <li class="nav-item"><a class="nav-link" href="{{ route('approvals.index') }}">Approval Masuk</a></li>
                    @endif
                    @if(auth()->user()->role->name === 'finance')
                        <li class="nav-item"><a class="nav-link" href="{{ route('payments.index') }}">Proses Pembayaran</a></li>
                    @endif
                </ul>
                <div class="d-flex align-items-center gap-3">
                    <span class="role-tag">{{ auth()->user()->role->label }}</span>
                    <span class="text-white-50 small d-none d-md-inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="mb-0">
                        @csrf
                        <button class="btn btn-sm btn-outline-light">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
@endauth

<div class="container my-4">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
