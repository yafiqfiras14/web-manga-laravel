@extends('layouts.app')

@section('title', $playlist->name.' — Playlist — SoundKu')

@section('content')
    @php
        $items = $tracks->map(fn ($item) => $item->toTrack())->values()->all();
    @endphp

    <div class="breadcrumb">
        <a href="{{ route('library') }}">Perpustakaan</a>
        <x-icon name="chevron-right" :size="13" />
        <span>Playlist</span>
    </div>

    <div data-queue="{{ track_json($items) }}">
        <header class="playlist-header user-playlist-header">
            <div class="playlist-header-art">
                @if ($playlist->cover_url)
                    <img src="{{ $playlist->cover_url }}" alt="{{ $playlist->name }}">
                @else
                    <div class="art-placeholder"><x-icon name="queue" :size="52" /></div>
                @endif
            </div>

            <div class="playlist-header-info">
                <span class="eyebrow"><x-icon name="queue" :size="14" /> Playlist pribadi</span>
                <h1>{{ $playlist->name }}</h1>

                @if ($playlist->description)
                    <p class="playlist-description">{{ $playlist->description }}</p>
                @endif

                <div class="track-stats">
                    <span><x-icon name="user" :size="14" /> {{ auth()->user()->name }}</span>
                    <span><x-icon name="music" :size="15" /> {{ $playlist->tracks_count }} lagu</span>
                    <span><x-icon name="clock" :size="15" /> {{ format_duration($duration) }}</span>
                </div>

                <div class="playlist-actions">
                    <button class="btn btn-primary" type="button" data-play-all {{ count($items) === 0 ? 'disabled' : '' }}>
                        <x-icon name="play" :size="16" /> Putar
                    </button>

                    <details class="playlist-edit">
                        <summary class="btn btn-outline">
                            <x-icon name="pencil" :size="15" /> Edit
                        </summary>

                        <form class="playlist-edit-form" action="{{ route('library.playlists.update', $playlist) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <label for="playlist-name">Nama playlist</label>
                            <input type="text" id="playlist-name" name="name" value="{{ old('name', $playlist->name) }}" maxlength="80" required>

                            <label for="playlist-description">Deskripsi <span>(opsional)</span></label>
                            <textarea id="playlist-description" name="description" rows="3" maxlength="500" placeholder="Ceritain playlist ini soal apa...">{{ old('description', $playlist->description) }}</textarea>

                            <div class="playlist-edit-actions">
                                <button class="btn btn-primary btn-sm" type="submit">Simpan</button>
                            </div>
                        </form>
                    </details>

                    <form action="{{ route('library.playlists.destroy', $playlist) }}" method="POST" class="inline-form"
                          data-confirm="Hapus playlist “{{ $playlist->name }}”? Tindakan ini nggak bisa dibatalin.">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-ghost btn-danger-outline" type="submit">
                            <x-icon name="trash" :size="15" /> Hapus
                        </button>
                    </form>
                </div>
            </div>
        </header>

        @if (count($items) > 0)
            <div class="track-list playlist-track-list">
                @foreach ($tracks as $index => $item)
                    @php $track = $item->toTrack(); @endphp
                    <div class="track-row" data-track-id="{{ $track['id'] }}">
                        <div class="row-leading">
                            <span class="row-index">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <div class="row-art-wrap">
                                @if (! empty($track['artwork']))
                                    <img class="row-art" src="{{ $track['artwork'] }}" alt="" loading="lazy" decoding="async">
                                @else
                                    <div class="row-art art-placeholder"><x-icon name="music" :size="16" /></div>
                                @endif
                                <button class="row-play" type="button" data-play-index="{{ $index }}"
                                        aria-label="Putar {{ $track['title'] }}">
                                    <x-icon name="play" :size="15" />
                                </button>
                            </div>
                        </div>

                        <div class="row-main">
                            <a class="row-title" href="{{ route('track.show', $track['id']) }}">{{ $track['title'] }}</a>
                            @if (! empty($track['artist_id']))
                                <a class="row-artist" href="{{ route('artist.show', $track['artist_id']) }}">{{ $track['artist'] }}</a>
                            @else
                                <span class="row-artist">{{ $track['artist'] }}</span>
                            @endif
                        </div>

                        <span class="row-genre">{{ $track['genre'] ?: '—' }}</span>
                        <span class="row-duration">{{ format_duration((int) ($track['duration'] ?? 0)) }}</span>

                        <button class="row-add" type="button"
                                data-add-track="{{ track_json($track) }}"
                                aria-label="Simpan {{ $track['title'] }} ke playlist lain"
                                title="Simpan ke playlist">
                            <x-icon name="plus" :size="15" />
                        </button>

                        <button class="fav-btn row-fav" type="button"
                                data-fav="{{ track_json($track) }}"
                                data-fav-id="{{ $track['id'] }}"
                                aria-label="Tambah {{ $track['title'] }} ke favorit"
                                aria-pressed="false">
                            <x-icon name="heart" :size="16" />
                        </button>

                        <form action="{{ route('library.playlists.tracks.destroy', [$playlist, $item]) }}" method="POST"
                              class="inline-form" data-confirm="Hapus “{{ $track['title'] }}” dari playlist ini?">
                            @csrf
                            @method('DELETE')
                            <button class="row-remove" type="submit" aria-label="Hapus {{ $track['title'] }} dari playlist" title="Hapus dari playlist">
                                <x-icon name="trash" :size="15" />
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @else
            <div class="glass empty-state">
                <x-icon name="queue" :size="30" />
                <h2>Playlist ini masih kosong</h2>
                <p>Tambahkan lagu lewat tombol <strong>+</strong> di kartu lagu, halaman detail, atau player.</p>
                <a class="btn btn-primary" href="{{ route('home') }}">Cari lagu</a>
            </div>
        @endif
    </div>
@endsection
