@extends('layouts.app')

@section('title', $playlist['title'].' — SoundKu')

@section('content')
    <div class="breadcrumb">
        <a href="{{ route('home') }}">Beranda</a>
        <x-icon name="chevron-right" :size="13" />
        <span>Playlist</span>
    </div>

    <header class="playlist-header">
        <div class="playlist-header-art">
            @if (! empty($playlist['artwork']))
                <img src="{{ $playlist['artwork'] }}" alt="{{ $playlist['title'] }}">
            @else
                <div class="art-placeholder"><x-icon name="disc" :size="56" /></div>
            @endif
        </div>

        <div class="playlist-header-info">
            <span class="eyebrow"><x-icon name="queue" :size="14" /> Playlist</span>
            <h1>{{ $playlist['title'] }}</h1>

            <div class="track-stats">
                <span>oleh {{ $playlist['curator'] }}</span>
                <span><x-icon name="music" :size="15" /> {{ $playlist['track_count'] }} track</span>
                @if (! empty($playlist['duration']))
                    <span><x-icon name="clock" :size="15" /> {{ format_duration((int) $playlist['duration']) }}</span>
                @endif
                @if (! empty($playlist['genre']))
                    <span><x-icon name="disc" :size="15" /> {{ $playlist['genre'] }}</span>
                @endif
            </div>

            @if (! empty($playlist['description']))
                <p class="artist-bio">{{ \Illuminate\Support\Str::limit($playlist['description'], 500) }}</p>
            @endif

            <div class="track-actions">
                @if (count($playlist['tracks']) > 0)
                    <button class="btn btn-primary" type="button" data-play-all
                            data-queue="{{ track_json($playlist['tracks']) }}">
                        <x-icon name="play" :size="16" /> Putar playlist
                    </button>
                @endif

                @if (! empty($playlist['permalink_url']))
                    <a class="btn btn-outline" href="{{ $playlist['permalink_url'] }}" target="_blank" rel="noopener noreferrer">
                        <x-icon name="external" :size="15" /> Buka di SoundCloud
                    </a>
                @endif
            </div>
        </div>
    </header>

    <section class="section" data-queue="{{ track_json($playlist['tracks']) }}">
        <div class="section-head">
            <div>
                <h2>Daftar lagu</h2>
                <p>{{ count($playlist['tracks']) }} track di playlist ini.</p>
            </div>
        </div>

        @if (count($playlist['tracks']) > 0)
            <div class="track-list">
                @foreach ($playlist['tracks'] as $index => $track)
                    @include('partials.track-row', ['track' => $track, 'index' => $index])
                @endforeach
            </div>
        @else
            <div class="glass empty-state">
                <x-icon name="queue" :size="30" />
                <h2>Playlist ini kosong</h2>
                <p>Belum ada track publik yang bisa diputar dari playlist ini.</p>
            </div>
        @endif
    </section>
@endsection
