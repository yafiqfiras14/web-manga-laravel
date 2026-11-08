@php $artist = $artist ?? []; @endphp

<a class="artist-card" href="{{ route('artist.show', $artist['id']) }}">
    <div class="artist-avatar">
        @if (! empty($artist['avatar']))
            <img src="{{ $artist['avatar'] }}" alt="{{ $artist['username'] }}" loading="lazy" decoding="async">
        @else
            <div class="art-placeholder"><x-icon name="users" :size="28" /></div>
        @endif
    </div>
    <span class="artist-name">{{ $artist['username'] }}</span>
    <span class="artist-meta">{{ format_count((int) ($artist['followers'] ?? 0)) }} pengikut</span>
</a>
