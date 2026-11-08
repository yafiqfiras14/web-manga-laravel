@extends('layouts.admin')

@section('title', $featured->exists ? 'Edit Pilihan Editor' : 'Tambah Pilihan Editor')

@section('content')
    <div class="page-header">
        <div>
            <h1>{{ $featured->exists ? 'Edit lagu' : 'Tambah lagu' }}</h1>
            <p>{{ $featured->exists ? 'Perbarui metadata dan urutan tampil di beranda.' : 'Tempel link track atau playlist SoundCloud.' }}</p>
        </div>
        <a href="{{ route('admin.featured.index') }}" class="btn btn-ghost">
            <x-icon name="arrow-left" :size="15" /> Kembali
        </a>
    </div>

    <div class="form-card">
        @if ($featured->exists)
            <div class="form-preview">
                @if ($featured->artwork_url)
                    <img src="{{ $featured->artwork_url }}" alt="" class="form-preview-art">
                @else
                    <div class="form-preview-art art-placeholder"><x-icon name="music" :size="28" /></div>
                @endif
                <div>
                    <h2>{{ $featured->title }}</h2>
                    <p>{{ $featured->artist }}</p>
                    @if ($featured->permalink_url)
                        <a href="{{ $featured->permalink_url }}" target="_blank" rel="noopener noreferrer" class="form-link">
                            <x-icon name="external" :size="13" /> Buka di SoundCloud
                        </a>
                    @endif
                </div>
            </div>

            <form action="{{ route('admin.featured.update', $featured) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-grid">
                    <div class="form-field">
                        <label for="title">Judul</label>
                        <input type="text" id="title" name="title" value="{{ old('title', $featured->title) }}" required maxlength="255">
                    </div>

                    <div class="form-field">
                        <label for="artist">Artis</label>
                        <input type="text" id="artist" name="artist" value="{{ old('artist', $featured->artist) }}" maxlength="255">
                    </div>

                    <div class="form-field">
                        <label for="genre">Genre</label>
                        <input type="text" id="genre" name="genre" value="{{ old('genre', $featured->genre) }}" maxlength="100">
                    </div>

                    <div class="form-field">
                        <label for="position">Urutan tampil</label>
                        <input type="number" id="position" name="position" value="{{ old('position', $featured->position) }}" min="0" max="9999">
                        <small>Angka lebih kecil tampil lebih dulu.</small>
                    </div>
                </div>

                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $featured->is_active) ? 'checked' : '' }}>
                    Tampilkan di beranda
                </label>

                <div class="form-note">
                    <x-icon name="clock" :size="15" />
                    <span>Durasi {{ format_duration((int) $featured->duration) }} &middot; SoundCloud ID {{ $featured->soundcloud_id }}</span>
                </div>

                <button type="submit" class="btn btn-primary">Simpan perubahan</button>
            </form>
        @else
            <form action="{{ route('admin.featured.store') }}" method="POST">
                @csrf

                <div class="form-field">
                    <label for="url">URL SoundCloud</label>
                    <input type="url" id="url" name="url" value="{{ old('url') }}"
                           placeholder="https://soundcloud.com/artist/nama-track" required>
                    <small>
                        Bisa link track atau playlist publik. Metadata (judul, artis, cover, durasi) diambil otomatis dari SoundCloud.
                    </small>
                </div>

                <div class="form-note">
                    <x-icon name="sparkles" :size="15" />
                    <span>Kalau URL berupa playlist, semua track di dalamnya akan ditambahkan sekaligus.</span>
                </div>

                <button type="submit" class="btn btn-primary">
                    <x-icon name="plus" :size="15" /> Tambahkan
                </button>
            </form>
        @endif
    </div>
@endsection
