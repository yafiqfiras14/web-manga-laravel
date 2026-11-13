@php
    $index = $index ?? 0;
    $track = $track ?? [];
@endphp

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
