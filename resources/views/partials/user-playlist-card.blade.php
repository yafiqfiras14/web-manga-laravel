@php
    $playlist = $playlist ?? null;
    $trackCount = $playlist?->tracks_count ?? $playlist?->tracks()->count() ?? 0;
@endphp

@if ($playlist)
    <a class="user-playlist-card" href="{{ route('library.playlists.show', $playlist) }}">
        <div class="user-playlist-art">
            @if ($playlist->cover_url)
                <img src="{{ $playlist->cover_url }}" alt="" loading="lazy" decoding="async">
            @else
                <div class="art-placeholder"><x-icon name="queue" :size="30" /></div>
            @endif
            <span class="user-playlist-badge"><x-icon name="music" :size="12" /> {{ $trackCount }}</span>
        </div>
        <strong class="user-playlist-name">{{ $playlist->name }}</strong>
        <span class="user-playlist-meta">{{ $playlist->description ?: 'Playlist pribadi' }}</span>
    </a>
@endif
