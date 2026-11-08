@php $playlist = $playlist ?? []; @endphp

<a class="playlist-card" href="{{ route('playlist.show', $playlist['id']) }}">
    <div class="playlist-art">
        @if (! empty($playlist['artwork']))
            <img src="{{ $playlist['artwork'] }}" alt="{{ $playlist['title'] }}" loading="lazy" decoding="async">
        @else
            <div class="art-placeholder"><x-icon name="disc" :size="30" /></div>
        @endif
        <span class="playlist-count">
            <x-icon name="queue" :size="13" /> {{ $playlist['track_count'] }}
        </span>
    </div>
    <span class="playlist-title">{{ $playlist['title'] }}</span>
    <span class="playlist-meta">oleh {{ $playlist['curator'] }}</span>
</a>
