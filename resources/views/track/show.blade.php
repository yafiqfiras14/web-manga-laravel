@extends('layouts.app')

@section('title', $track['title'].' — '.$track['artist'].' — SoundKu')

@section('content')
    <div class="breadcrumb">
        <a href="{{ route('home') }}">Beranda</a>
        <x-icon name="chevron-right" :size="13" />
        <span>Track</span>
    </div>

    <section class="track-detail" data-queue="{{ track_json([$track]) }}">
        <div class="track-detail-art">
            @if (! empty($track['artwork']))
                <img src="{{ $track['artwork'] }}" alt="{{ $track['title'] }}">
            @else
                <div class="art-placeholder"><x-icon name="music" :size="64" /></div>
            @endif

            <button class="detail-play" type="button" data-play-index="0" aria-label="Putar {{ $track['title'] }}">
                <x-icon name="play" :size="28" />
            </button>
        </div>

        <div class="track-detail-info">
            <span class="eyebrow"><x-icon name="music" :size="14" /> Track</span>
            <h1>{{ $track['title'] }}</h1>

            <div class="track-detail-artist">
                @if (! empty($track['artist_id']))
                    <a href="{{ route('artist.show', $track['artist_id']) }}" class="artist-inline">
                        @if (! empty($track['artist_avatar']))
                            <img src="{{ $track['artist_avatar'] }}" alt="" class="artist-avatar-xs">
                        @endif
                        <span>{{ $track['artist'] }}</span>
                    </a>
                @else
                    <span>{{ $track['artist'] }}</span>
                @endif

                @if (! empty($track['is_preview']))
                    <span class="badge badge-preview">Preview 30 detik</span>
                @endif
            </div>

            <div class="track-stats">
                <span><x-icon name="headphones" :size="15" /> {{ format_count((int) ($track['plays'] ?? 0)) }} diputar</span>
                <span><x-icon name="heart" :size="15" /> {{ format_count((int) ($track['likes'] ?? 0)) }} suka</span>
                @if (! empty($track['genre']))
                    <span><x-icon name="disc" :size="15" /> {{ $track['genre'] }}</span>
                @endif
                <span><x-icon name="clock" :size="15" /> {{ format_duration((int) ($track['duration'] ?? 0)) }}</span>
            </div>

            <div class="track-actions">
                <button class="btn btn-primary btn-lg" type="button" data-play-index="0">
                    <x-icon name="play" :size="18" /> Putar sekarang
                </button>

                <button class="btn btn-outline btn-lg fav-btn" type="button"
                        data-fav="{{ track_json($track) }}"
                        data-fav-id="{{ $track['id'] }}"
                        aria-label="Favoritkan {{ $track['title'] }}" aria-pressed="false">
                    <x-icon name="heart" :size="17" /> <span>Favorit</span>
                </button>

                <button class="btn btn-glass btn-lg" type="button" data-add-track="{{ track_json($track) }}">
                    <x-icon name="plus" :size="16" /> <span>Simpan ke playlist</span>
                </button>

                @if (! empty($track['permalink_url']))
                    <a class="btn btn-glass btn-lg" href="{{ $track['permalink_url'] }}" target="_blank" rel="noopener noreferrer">
                        <x-icon name="external" :size="16" /> Buka di SoundCloud
                    </a>
                @endif
            </div>

            @if (! empty($track['description']))
                <div class="track-description">
                    <h2>Deskripsi</h2>
                    <p>{{ \Illuminate\Support\Str::limit($track['description'], 900) }}</p>
                </div>
            @endif
        </div>
    </section>

    @if (count($related) > 0)
        <section class="section" data-queue="{{ track_json($related) }}">
            <div class="section-head">
                <div>
                    <h2>Mirip dengan ini</h2>
                    <p>Rekomendasi dari SoundCloud berdasarkan track yang sedang kamu buka.</p>
                </div>
                <button class="btn btn-ghost btn-sm" type="button" data-play-all>
                    <x-icon name="play" :size="15" /> Putar semua
                </button>
            </div>

            <div class="track-grid">
                @foreach ($related as $index => $item)
                    @include('partials.track-card', ['track' => $item, 'index' => $index])
                @endforeach
            </div>
        </section>
    @endif
@endsection
