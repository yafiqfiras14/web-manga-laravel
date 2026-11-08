@props(['genres' => null])

@php
    $genres = $genres ?? config('music.genres');
    $active = request()->routeIs('genre.show') ? request()->route('genre') : null;
@endphp

<nav class="genre-chips" aria-label="Genre">
    @foreach ($genres as $slug => $genre)
        <a href="{{ route('genre.show', $slug) }}"
           class="chip {{ $active === $slug ? 'is-active' : '' }}">
            {{ $genre['label'] }}
        </a>
    @endforeach
</nav>
