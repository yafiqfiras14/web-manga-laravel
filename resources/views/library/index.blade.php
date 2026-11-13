@extends('layouts.app')

@section('title', 'Perpustakaan — SoundKu')

@section('content')
    @php
        $likedTracks = $liked->map(fn ($item) => $item->toTrack())->all();
        $historyTracks = $history->map(fn ($item) => $item->toTrack())->all();
        $firstName = explode(' ', trim(auth()->user()->name))[0];
    @endphp

    <header class="page-header library-header">
        <div>
            <span class="eyebrow"><x-icon name="queue" :size="14" /> Perpustakaan</span>
            <h1>Koleksi {{ $firstName }}</h1>
            <p>Semua playlist, lagu disukai, dan riwayat putar kamu ada di sini.</p>
        </div>
        <button class="btn btn-primary" type="button" data-open-create-playlist>
            <x-icon name="plus" :size="16" /> Playlist baru
        </button>
    </header>

    <div class="library-stats">
        <a class="library-stat" href="{{ route('library.liked') }}">
            <span class="library-stat-icon"><x-icon name="heart" :size="19" /></span>
            <span class="library-stat-body">
                <strong>{{ $likedCount }}</strong>
                <span>Lagu disukai</span>
            </span>
            <x-icon name="chevron-right" :size="16" class="library-stat-arrow" />
        </a>

        <a class="library-stat" href="{{ route('library.history') }}">
            <span class="library-stat-icon library-stat-icon-clock"><x-icon name="clock" :size="19" /></span>
            <span class="library-stat-body">
                <strong>{{ $historyCount }}</strong>
                <span>Riwayat putar</span>
            </span>
            <x-icon name="chevron-right" :size="16" class="library-stat-arrow" />
        </a>

        <button class="library-stat" type="button" data-open-create-playlist>
            <span class="library-stat-icon library-stat-icon-plus"><x-icon name="plus" :size="19" /></span>
            <span class="library-stat-body">
                <strong>{{ $playlists->count() }}</strong>
                <span>Playlist kamu</span>
            </span>
            <x-icon name="chevron-right" :size="16" class="library-stat-arrow" />
        </button>
    </div>

    <section class="section">
        <div class="section-head">
            <div>
                <h2><x-icon name="queue" :size="18" /> Playlist kamu</h2>
                <p>Kumpulan lagu sesuai suasana hati.</p>
            </div>
            <button class="btn btn-outline btn-sm" type="button" data-open-create-playlist>
                <x-icon name="plus" :size="14" /> Buat playlist
            </button>
        </div>

        @if ($playlists->isNotEmpty())
            <div class="playlist-grid">
                @foreach ($playlists as $playlist)
                    @include('partials.user-playlist-card', ['playlist' => $playlist])
                @endforeach
            </div>
        @else
            <div class="glass empty-state">
                <x-icon name="queue" :size="30" />
                <h2>Belum ada playlist</h2>
                <p>Bikin playlist pertama, lalu tambahkan lagu dari kartu lagu mana pun.</p>
                <button class="btn btn-primary" type="button" data-open-create-playlist>
                    <x-icon name="plus" :size="15" /> Buat playlist
                </button>
            </div>
        @endif
    </section>

    <section class="section" data-queue="{{ track_json($likedTracks) }}">
        <div class="section-head">
            <div>
                <h2><x-icon name="heart" :size="18" /> Lagu disukai</h2>
                <p>{{ $likedCount }} lagu tersimpan di akunmu.</p>
            </div>
            @if (count($likedTracks) > 0)
                <div class="section-actions">
                    <button class="btn btn-ghost btn-sm" type="button" data-play-all>
                        <x-icon name="play" :size="14" /> Putar
                    </button>
                    <a class="btn btn-outline btn-sm" href="{{ route('library.liked') }}">Lihat semua</a>
                </div>
            @endif
        </div>

        @if (count($likedTracks) > 0)
            <div class="track-grid">
                @foreach ($likedTracks as $index => $track)
                    @include('partials.track-card', ['track' => $track, 'index' => $index])
                @endforeach
            </div>
        @else
            <div class="glass empty-state empty-state-sm">
                <x-icon name="heart" :size="26" />
                <h2>Belum ada lagu disukai</h2>
                <p>Tekan ikon hati di kartu lagu untuk menyimpannya ke sini.</p>
            </div>
        @endif
    </section>

    <section class="section" data-queue="{{ track_json($historyTracks) }}">
        <div class="section-head">
            <div>
                <h2><x-icon name="clock" :size="18" /> Terakhir diputar</h2>
                <p>Lanjutin lagu yang terakhir kamu dengar.</p>
            </div>
            @if (count($historyTracks) > 0)
                <a class="btn btn-outline btn-sm" href="{{ route('library.history') }}">Lihat semua</a>
            @endif
        </div>

        @if (count($historyTracks) > 0)
            <div class="track-list">
                @foreach ($historyTracks as $index => $track)
                    @include('partials.track-row', ['track' => $track, 'index' => $index])
                @endforeach
            </div>
        @else
            <div class="glass empty-state empty-state-sm">
                <x-icon name="clock" :size="26" />
                <h2>Riwayat masih kosong</h2>
                <p>Setiap lagu yang kamu putar bakal muncul di sini.</p>
            </div>
        @endif
    </section>
@endsection
