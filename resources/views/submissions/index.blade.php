@extends('layouts.app')
@section('title', 'Pengajuan Saya')

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
                            <td class="ref-number">{{ $submission->nomor_pengajuan }}</td>
                            <td>{{ \Carbon\Carbon::parse($submission->tanggal_pengajuan)->format('d-m-Y') }}</td>
                            <td>{{ $submission->category->name }}</td>
                            <td class="money">Rp {{ number_format($submission->nilai, 0, ',', '.') }}</td>
                            <td>
                                <x-stamp :status="$submission->status" />
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
