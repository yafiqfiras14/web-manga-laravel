<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#08090d">
    <meta name="description" content="SoundKu — streaming musik dari SoundCloud. Cari lagu, jelajahi genre, dan buat antrean tanpa ribet.">
    <title>@yield('title', 'SoundKu — Streaming musik dari SoundCloud')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('head')
</head>
<body class="@yield('body-class')">
    <div class="app">
        <aside class="sidebar" id="sidebar">
            <a class="brand" href="{{ route('home') }}" aria-label="SoundKu beranda">
                <span class="brand-mark"><x-icon name="wave" :size="22" /></span>
                <span class="brand-text">Sound<span>Ku</span></span>
            </a>

            <nav class="nav" aria-label="Navigasi utama">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'is-active' : '' }}">
                    <x-icon name="home" :size="19" /> <span>Beranda</span>
                </a>
                <a href="{{ route('search') }}" class="nav-link {{ request()->routeIs('search') ? 'is-active' : '' }}">
                    <x-icon name="search" :size="19" /> <span>Cari musik</span>
                </a>
                <a href="{{ route('favorites') }}" class="nav-link {{ request()->routeIs('favorites') ? 'is-active' : '' }}">
                    <x-icon name="heart" :size="19" /> <span>Favorit</span>
                </a>
                <a href="{{ route('genre.show', 'all-music') }}" class="nav-link {{ request()->routeIs('genre.show') ? 'is-active' : '' }}">
                    <x-icon name="compass" :size="19" /> <span>Jelajahi genre</span>
                </a>
            </nav>

            <div class="sidebar-section">
                <span class="sidebar-label">Genre pilihan</span>
                <div class="sidebar-genres">
                    @foreach (collect(config('music.genres'))->except('all-music') as $slug => $genre)
                        <a href="{{ route('genre.show', $slug) }}">{{ $genre['label'] }}</a>
                    @endforeach
                </div>
            </div>

            <div class="sidebar-footer">
                <div class="sidebar-note">
                    <x-icon name="headphones" :size="18" />
                    <p>Audio diputar langsung dari katalog publik SoundCloud.</p>
                </div>
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-admin">Panel admin</a>
                @else
                    <a href="{{ route('admin.login') }}" class="sidebar-admin">Login admin</a>
                @endauth
            </div>
        </aside>

        <div class="sidebar-scrim" id="sidebar-scrim" hidden></div>

        <div class="main">
            <header class="topbar">
                <button class="icon-btn menu-toggle" id="sidebar-toggle" type="button" aria-label="Buka menu">
                    <x-icon name="menu" :size="21" />
                </button>

                <form class="search-box" action="{{ route('search') }}" method="GET" role="search" autocomplete="off">
                    <x-icon name="search" :size="18" class="search-icon" />
                    <input
                        type="search"
                        id="global-search"
                        name="q"
                        value="{{ request()->routeIs('search') ? request('q') : '' }}"
                        placeholder="Cari lagu, artis, atau playlist..."
                        aria-label="Cari musik"
                    >
                    <kbd>/</kbd>
                    <div class="suggestions" id="search-suggestions" hidden></div>
                </form>

                <div class="topbar-actions">
                    @auth
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost btn-sm">Admin</a>
                    @else
                        <a href="{{ route('admin.login') }}" class="btn btn-ghost btn-sm">Masuk</a>
                    @endauth
                    <a href="https://soundcloud.com" target="_blank" rel="noopener noreferrer" class="btn btn-outline btn-sm soundcloud-link">
                        <x-icon name="external" :size="14" /> <span>SoundCloud</span>
                    </a>
                </div>
            </header>

            <main class="content">
                @if (session('success'))
                    <div class="alert alert-success">
                        <x-icon name="check" :size="18" /> <span>{{ session('success') }}</span>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @include('partials.player')

    <script src="https://cdn.jsdelivr.net/npm/hls.js@1/dist/hls.min.js" defer></script>
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="{{ asset('js/player.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
