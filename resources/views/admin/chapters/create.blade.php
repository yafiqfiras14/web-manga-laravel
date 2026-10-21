@extends('layouts.admin')

@section('title', 'Tambah Chapter — '.$manga->title)

@section('content')
    <div class="page-header">
        <h1>Tambah Chapter: {{ $manga->title }}</h1>
        <a href="{{ route('admin.manga.edit', $manga) }}" class="btn">&larr; Kembali</a>
    </div>

    <form action="{{ route('admin.chapters.store', $manga) }}" method="POST" enctype="multipart/form-data" class="form-card">
        @csrf

        <label for="number">Nomor Chapter *</label>
        <input type="number" id="number" name="number" step="0.1" min="0" value="{{ old('number') }}" required>

        <label for="title">Judul Chapter</label>
        <input type="text" id="title" name="title" value="{{ old('title') }}">

        <label for="published_at">Tanggal Rilis</label>
        <input type="date" id="published_at" name="published_at" value="{{ old('published_at', now()->format('Y-m-d')) }}">

        <label for="pages">Halaman Manga (bisa pilih banyak sekaligus) *</label>
        <input type="file" id="pages" name="pages[]" accept="image/*" multiple required>
        <p class="hint">Urutan file yang dipilih = urutan halaman. Tekan Ctrl/Shift buat multi-select.</p>

        <button type="submit" class="btn btn-primary">Simpan Chapter</button>
    </form>
@endsection
