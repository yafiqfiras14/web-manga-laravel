@extends('layouts.app')

@section('title', 'SoundKu — Streaming musik dari SoundCloud')

@section('content')
    @if ($error)
        <div class="alert alert-error">
            <x-icon name="wave" :size="18" />
            <span>{{ $error }}</span>
            <a href="{{ route('home') }}" class="alert-action">Coba lagi</a>
        </div>
    @endif

    @if ($featured)
        @php
            $isEditorPick = collect($editorPicks)->contains(fn ($track) => $track['id'] === $featured['id']);
        @endphp

        <section class="hero" data-queue="{{ track_json([$featured]) }}">
            <div class="hero-media">
                @if (! empty($featured['artwork']))
                    <img src="{{ $featured['artwork'] }}" alt="{{ $featured['title'] }}" fetchpriority="high">
                @else
                    <div class="art-placeholder"><x-icon name="music" :size="64" /></div>
                @endif
            </div>

            <div class="hero-body">
                <span class="eyebrow">
                    <x-icon name="sparkles" :size="14" />
                    {{ $isEditorPick ? 'Pilihan editor' : 'Trending #1 hari ini' }}
                </span>

                <h1 class="hero-title">{{ $featured['title'] }}</h1>

                <p class="hero-sub">
                    @if (! empty($featured['artist_id']))
                        <a href="{{ route('artist.show', $featured['artist_id']) }}">{{ $featured['artist'] }}</a>
                    @else
                        <span>{{ $featured['artist'] }}</span>
                    @endif

                    @if (! empty($featured['genre']))
                        <span class="dot"></span> <span>{{ $featured['genre'] }}</span>
                    @endif

                    @if (! empty($featured['plays']))
                        <span class="dot"></span> <span>{{ format_count((int) $featured['plays']) }} diputar</span>
                    @endif

                    @if (! empty($featured['duration']))
                        <span class="dot"></span> <span>{{ format_duration((int) $featured['duration']) }}</span>
                    @endif
                </p>

                <div class="hero-actions">
                    <button class="btn btn-primary btn-lg" type="button" data-play-index="0">
                        <x-icon name="play" :size="18" /> Putar sekarang
                    </button>
                    <a class="btn btn-glass btn-lg" href="{{ route('track.show', $featured['id']) }}">Lihat detail</a>
                    <button class="icon-btn fav-btn hero-fav" type="button"
                            data-fav="{{ track_json($featured) }}"
                            data-fav-id="{{ $featured['id'] }}"
                            aria-label="Favoritkan {{ $featured['title'] }}" aria-pressed="false">
                        <x-icon name="heart" :size="19" />
                    </button>
                </div>

                @if (! empty($featured['is_preview']))
                    <p class="hint">SoundCloud hanya menyediakan preview 30 detik untuk track ini.</p>
                @endif
            </div>

            <div class="hero-equalizer" aria-hidden="true">
                <span></span><span></span><span></span><span></span><span></span>
            </div>
        </section>
    @else
        <section class="glass empty-state empty-hero">
            <x-icon name="headphones" :size="30" />
            <h1>Belum ada lagu untuk ditampilkan</h1>
            <p>Tambahkan pilihan editor lewat panel admin, atau coba muat ulang halaman.</p>
            <a href="{{ route('search', ['q' => 'lofi']) }}" class="btn btn-primary">Cari musik</a>
        </section>
    @endif

    <section class="section section-genres">
        <div class="section-head">
            <div>
                <h2>Jelajahi genre</h2>
                <p>Dari lo-fi buat fokus sampai techno buat lembur.</p>
            </div>
        </div>
        @include('partials.genre-chips')
    </section>

    @if (count($trending) > 0)
        <section class="section" data-queue="{{ track_json($trending) }}">
            <div class="section-head">
                <div>
                    <h2>Trending saat ini</h2>
                    <p>Lagu yang paling sering diputar di SoundCloud minggu ini.</p>
                </div>
                <button class="btn btn-ghost btn-sm" type="button" data-play-all>
                    <x-icon name="play" :size="15" /> Putar semua
                </button>
            </div>

            <div class="track-grid">
                @foreach ($trending as $index => $track)
                    @include('partials.track-card', ['track' => $track, 'index' => $index])
                @endforeach
            </div>
        </section>
    @endif

    @if (count($editorPicks) > 0)
        <section class="section" data-queue="{{ track_json($editorPicks) }}">
            <div class="section-head">
                <div>
                    <h2><x-icon name="sparkles" :size="18" /> Pilihan editor</h2>
                    <p>Dikurasi langsung dari panel admin SoundKu.</p>
                </div>
                <button class="btn btn-ghost btn-sm" type="button" data-play-all>
                    <x-icon name="play" :size="15" /> Putar semua
                </button>
            </div>

            <div class="track-grid">
                @foreach ($editorPicks as $index => $track)
                    @include('partials.track-card', ['track' => $track, 'index' => $index])
                @endforeach
            </div>
        </section>
    @endif

    @if (count($chill) > 0)
        <section class="section" data-queue="{{ track_json($chill) }}">
            <div class="section-head">
                <div>
                    <h2>{{ config('music.sections.chill.title') }}</h2>
                    <p>{{ config('music.sections.chill.subtitle') }}</p>
                </div>
                <button class="btn btn-ghost btn-sm" type="button" data-play-all>
                    <x-icon name="play" :size="15" /> Putar semua
                </button>
            </div>

            <div class="track-grid">
                @foreach ($chill as $index => $track)
                    @include('partials.track-card', ['track' => $track, 'index' => $index])
                @endforeach
            </div>
        </section>
    @endif

    @if (count($recent) > 0)
        <section class="section" id="recent-section" data-queue="{{ track_json($recent) }}">
            <div class="section-head">
                <div>
                    <h2><x-icon name="clock" :size="18" /> Baru diputar</h2>
                    <p>Lanjutin lagi tanpa harus nyari ulang.</p>
                </div>
                <button class="btn btn-ghost btn-sm" type="button" id="recent-clear">Hapus riwayat</button>
            </div>
            <div class="track-grid">
                @foreach ($recent as $index => $track)
                    @include('partials.track-card', ['track' => $track, 'index' => $index])
                @endforeach
            </div>
        </section>
    @endif
@endsection
