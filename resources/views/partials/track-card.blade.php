@php
    $index = $index ?? 0;
    $track = $track ?? [];
@endphp

<article class="track-card">
    <div class="track-art">
        @if (! empty($track['artwork']))
            <img src="{{ $track['artwork'] }}" alt="{{ $track['title'] }}" loading="lazy" decoding="async">
        @else
            <div class="art-placeholder"><x-icon name="music" :size="34" /></div>
        @endif

        <button class="card-play" type="button" data-play-index="{{ $index }}"
                aria-label="Putar {{ $track['title'] }}">
            <x-icon name="play" :size="22" />
        </button>

        @if (! empty($track['is_preview']))
            <span class="preview-badge">30 detik</span>
        @endif

        <button class="fav-btn card-fav" type="button"
                data-fav="{{ track_json($track) }}"
                data-fav-id="{{ $track['id'] }}"
                aria-label="Tambah {{ $track['title'] }} ke favorit"
                aria-pressed="false">
            <x-icon name="heart" :size="17" />
        </button>
    </div>

    <div class="track-card-body">
        <a class="track-card-title" href="{{ route('track.show', $track['id']) }}"
           title="{{ $track['title'] }}">{{ $track['title'] }}</a>

        @if (! empty($track['artist_id']))
            <a class="track-card-artist" href="{{ route('artist.show', $track['artist_id']) }}">{{ $track['artist'] }}</a>
        @else
            <span class="track-card-artist">{{ $track['artist'] }}</span>
        @endif
    </div>
</article>
