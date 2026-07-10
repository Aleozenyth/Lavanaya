<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistem Pengajuan Transaksi')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="bg-light">
@auth
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">Sistem Pengajuan Transaksi</a>
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
                <span class="navbar-text text-white me-3">
                    {{ auth()->user()->name }} <span class="badge bg-secondary">{{ auth()->user()->role->label }}</span>
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-outline-light btn-sm">Logout</button>
                </form>
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
