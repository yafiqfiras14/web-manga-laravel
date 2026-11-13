@extends('layouts.app')

@section('title', 'Riwayat putar — SoundKu')

@section('content')
    @php
        $items = $tracks->getCollection();
        $queue = $items->map(fn ($item) => $item->toTrack())->values()->all();
    @endphp

    <div data-queue="{{ track_json($queue) }}">
        <header class="page-header page-header-ish">
            <div class="page-cover-hero">
                <div class="page-cover page-cover-history">
                    <x-icon name="clock" :size="54" />
                </div>
                <div>
                    <span class="eyebrow"><x-icon name="clock" :size="14" /> Jejak dengar</span>
                    <h1>Terakhir diputar</h1>
                    <p>{{ $tracks->total() }} lagu pernah kamu putar di akun ini.</p>
                </div>
            </div>

            @if ($tracks->count() > 0)
                <div class="section-actions">
                    <button class="btn btn-primary" type="button" data-play-all>
                        <x-icon name="play" :size="16" /> Putar lagi
                    </button>
                    <button class="btn btn-outline btn-danger-outline" type="button" data-clear-history>
                        <x-icon name="trash" :size="15" /> Bersihkan
                    </button>
                </div>
            @endif
        </header>

        @if ($tracks->count() > 0)
            <div class="history-list" id="history-list">
                @foreach ($items as $index => $item)
                    @php $track = $item->toTrack(); @endphp
                    <div class="history-row" data-track-id="{{ $track['id'] }}">
                        <button class="history-art" type="button" data-play-index="{{ $index }}"
                                aria-label="Putar {{ $track['title'] }}">
                            @if (! empty($track['artwork']))
                                <img src="{{ $track['artwork'] }}" alt="" loading="lazy" decoding="async">
                            @else
                                <span class="art-placeholder"><x-icon name="music" :size="18" /></span>
                            @endif
                            <span class="history-art-play"><x-icon name="play" :size="16" /></span>
                        </button>

                        <div class="history-main">
                            <a class="row-title" href="{{ route('track.show', $track['id']) }}">{{ $track['title'] }}</a>
                            @if (! empty($track['artist_id']))
                                <a class="row-artist" href="{{ route('artist.show', $track['artist_id']) }}">{{ $track['artist'] }}</a>
                            @else
                                <span class="row-artist">{{ $track['artist'] }}</span>
                            @endif
                        </div>

                        <span class="history-time" title="{{ $item->played_at?->format('d M Y, H:i') }}">
                            {{ $item->played_at?->diffForHumans() }}
                        </span>

                        <button class="row-add" type="button"
                                data-add-track="{{ track_json($track) }}"
                                aria-label="Simpan {{ $track['title'] }} ke playlist"
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

                        <a class="row-link" href="{{ route('track.show', $track['id']) }}" aria-label="Lihat detail {{ $track['title'] }}">
                            <x-icon name="chevron-right" :size="16" />
                        </a>
                    </div>
                @endforeach
            </div>

            @include('partials.pagination', ['paginator' => $tracks])
        @else
            <div class="glass empty-state">
                <x-icon name="clock" :size="30" />
                <h2>Belum ada riwayat</h2>
                <p>Putar lagu apa pun, nanti bakal muncul di sini biar gampang dilanjutin.</p>
                <a class="btn btn-primary" href="{{ route('home') }}">Mulai dengar</a>
            </div>
        @endif
    </div>
@endsection
