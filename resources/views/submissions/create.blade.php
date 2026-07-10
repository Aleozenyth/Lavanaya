@extends('layouts.app')
@section('title', 'Buat Pengajuan')

@section('content')
    <h3 class="mb-4">Buat Pengajuan Transaksi Pengeluaran</h3>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('submissions.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Tanggal Pengajuan</label>
                    <input type="date" name="tanggal_pengajuan" class="form-control @error('tanggal_pengajuan') is-invalid @enderror"
                           value="{{ old('tanggal_pengajuan', now()->format('Y-m-d')) }}" required>
                    @error('tanggal_pengajuan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Kategori "PO Produk" akan langsung diarahkan ke approval Direktur.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nilai Pengajuan (Rp)</label>
                    <input type="number" step="0.01" name="nilai" class="form-control @error('nilai') is-invalid @enderror"
                           value="{{ old('nilai') }}" required>
                    @error('nilai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">
                        &gt; Rp 5.000.000: approval SPV &amp; Manager. &gt; Rp 10.000.000: ditambah approval Direktur.
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="3">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Lampiran Dokumen (PDF/JPG/JPEG/PNG, maks 5MB)</label>
                    <input type="file" name="lampiran" class="form-control @error('lampiran') is-invalid @enderror" required>
                    @error('lampiran')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
                <a href="{{ route('submissions.index') }}" class="btn btn-outline-secondary">Batal</a>
            </form>
        </div>
    </div>
@endsection
