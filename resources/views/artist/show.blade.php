@extends('layouts.app')

@section('title', $artist['username'].' — SoundKu')

@section('content')
    <div class="breadcrumb">
        <a href="{{ route('home') }}">Beranda</a>
        <x-icon name="chevron-right" :size="13" />
        <span>Artis</span>
    </div>

    <header class="artist-header">
        <div class="artist-header-avatar">
            @if (! empty($artist['avatar']))
                <img src="{{ $artist['avatar'] }}" alt="{{ $artist['username'] }}">
            @else
                <div class="art-placeholder"><x-icon name="users" :size="48" /></div>
            @endif
        </div>

        <div class="artist-header-info">
            <span class="eyebrow"><x-icon name="users" :size="14" /> Artis</span>
            <h1>{{ $artist['username'] }}</h1>

            <div class="track-stats">
                <span><x-icon name="users" :size="15" /> {{ format_count((int) ($artist['followers'] ?? 0)) }} pengikut</span>
                <span><x-icon name="music" :size="15" /> {{ format_count((int) ($artist['track_count'] ?? 0)) }} track</span>
                @if (! empty($artist['city']))
                    <span><x-icon name="compass" :size="15" /> {{ $artist['city'] }}</span>
                @endif
            </div>

            @if (! empty($artist['description']))
                <p class="artist-bio">{{ \Illuminate\Support\Str::limit($artist['description'], 500) }}</p>
            @endif

            <div class="track-actions">
                @if (count($tracks) > 0)
                    <button class="btn btn-primary" type="button" data-play-all data-queue="{{ track_json($tracks) }}">
                        <x-icon name="play" :size="16" /> Putar semua track
                    </button>
                @endif

                @if (! empty($artist['permalink_url']))
                    <a class="btn btn-outline" href="{{ $artist['permalink_url'] }}" target="_blank" rel="noopener noreferrer">
                        <x-icon name="external" :size="15" /> Profil SoundCloud
                    </a>
                @endif
            </div>
        </div>
    </header>

    <section class="section" data-queue="{{ track_json($tracks) }}">
        <div class="section-head">
            <div>
                <h2>Track</h2>
                <p>{{ count($tracks) }} track terbaru dari {{ $artist['username'] }}.</p>
            </div>
        </div>

        @if (count($tracks) > 0)
            <div class="track-list">
                @foreach ($tracks as $index => $track)
                    @include('partials.track-row', ['track' => $track, 'index' => $index])
                @endforeach
            </div>
        @else
            <div class="glass empty-state">
                <x-icon name="music" :size="30" />
                <h2>Belum ada track publik</h2>
                <p>Artis ini belum punya track yang bisa diputar dari sini.</p>
            </div>
        @endif
    </section>
@endsection
