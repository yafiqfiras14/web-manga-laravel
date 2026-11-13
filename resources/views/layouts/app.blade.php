<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#08090d" id="theme-color-meta">
    <meta name="description" content="SoundKu — streaming musik dari SoundCloud. Cari lagu, bikin playlist, dan simpan favorit di akunmu.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SoundKu — Streaming musik dari SoundCloud')</title>

    <script>
        (function () {
            try {
                var stored = localStorage.getItem('soundku:theme');
                var theme = stored === 'light' || stored === 'dark'
                    ? stored
                    : (window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark');

                document.documentElement.dataset.theme = theme;
                document.documentElement.style.colorScheme = theme;
                document.getElementById('theme-color-meta').setAttribute('content', theme === 'light' ? '#f4f5fa' : '#08090d');
            } catch (error) {
                document.documentElement.dataset.theme = 'dark';
            }
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('head')
</head>
<body class="@yield('body-class')">
    @php
        $navPlaylists = auth()->user()->playlists()->withCount('tracks')->latest()->get();
        $navConfigPlaylists = $navPlaylists->map(fn ($playlist) => $playlist->toSummary())->values()->all();
        $activePlaylistId = request()->route('playlist')?->id ?? null;
    @endphp

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
            </nav>

            <div class="sidebar-library">
                <div class="sidebar-library-head">
                    <span class="sidebar-label">Perpustakaan</span>
                    <button class="sidebar-create" type="button" data-open-create-playlist aria-label="Buat playlist baru" title="Buat playlist">
                        <x-icon name="plus" :size="15" />
                    </button>
                </div>

                <div class="sidebar-lib-links">
                    <a href="{{ route('library') }}" class="sidebar-lib-link {{ request()->routeIs('library') ? 'is-active' : '' }}">
                        <x-icon name="queue" :size="17" /> <span>Semua koleksi</span>
                    </a>
                    <a href="{{ route('library.liked') }}" class="sidebar-lib-link {{ request()->routeIs('library.liked') || request()->routeIs('favorites') ? 'is-active' : '' }}">
                        <x-icon name="heart" :size="17" /> <span>Lagu disukai</span>
                    </a>
                    <a href="{{ route('library.history') }}" class="sidebar-lib-link {{ request()->routeIs('library.history') ? 'is-active' : '' }}">
                        <x-icon name="clock" :size="17" /> <span>Terakhir diputar</span>
                    </a>
                </div>

                <div class="sidebar-playlists" id="sidebar-playlists">
                    @foreach ($navPlaylists->take(8) as $playlist)
                        <a href="{{ route('library.playlists.show', $playlist) }}"
                           class="sidebar-playlist {{ $activePlaylistId === $playlist->id ? 'is-active' : '' }}">
                            <span class="sidebar-playlist-art">
                                @if ($playlist->cover_url)
                                    <img src="{{ $playlist->cover_url }}" alt="" loading="lazy">
                                @else
                                    <x-icon name="queue" :size="15" />
                                @endif
                            </span>
                            <span class="sidebar-playlist-main">
                                <strong>{{ $playlist->name }}</strong>
                                <small>{{ $playlist->tracks_count }} lagu</small>
                            </span>
                        </a>
                    @endforeach

                    @if ($navPlaylists->isEmpty())
                        <p class="sidebar-playlist-empty">Belum ada playlist. Bikin yang pertama!</p>
                    @endif
                </div>
            </div>

            <div class="sidebar-section sidebar-genres-section">
                <span class="sidebar-label">Jelajahi genre</span>
                <div class="sidebar-genres">
                    @foreach (collect(config('music.genres'))->except('all-music') as $slug => $genre)
                        <a href="{{ route('genre.show', $slug) }}">{{ $genre['label'] }}</a>
                    @endforeach
                </div>
            </div>

            <div class="sidebar-footer">
                <div class="user-card">
                    <a href="{{ route('account') }}" class="user-card-main">
                        <span class="user-avatar">{{ auth()->user()->initials() }}</span>
                        <span class="user-meta">
                            <strong>{{ auth()->user()->name }}</strong>
                            <small>{{ auth()->user()->email }}</small>
                        </span>
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="inline-form">
                        @csrf
                        <button type="submit" class="user-logout" title="Keluar" aria-label="Keluar dari akun">
                            <x-icon name="logout" :size="16" />
                        </button>
                    </form>
                </div>

                @if (auth()->user()->is_admin)
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-admin">
                        <x-icon name="sparkles" :size="13" /> Panel admin
                    </a>
                @endif
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
                    <button class="icon-btn theme-toggle" type="button" data-theme-toggle aria-label="Ganti tema" title="Ganti tema">
                        <x-icon name="sun" :size="18" class="theme-icon theme-icon-sun" />
                        <x-icon name="moon" :size="18" class="theme-icon theme-icon-moon" />
                    </button>
                    <button class="btn btn-outline btn-sm topbar-create" type="button" data-open-create-playlist>
                        <x-icon name="plus" :size="15" /> <span>Buat playlist</span>
                    </button>
                    <a href="{{ route('account') }}" class="topbar-user" aria-label="Akun {{ auth()->user()->name }}">
                        <span class="user-avatar user-avatar-sm">{{ auth()->user()->initials() }}</span>
                        <span class="topbar-user-name">{{ auth()->user()->name }}</span>
                    </a>
                </div>
            </header>

            <main class="content">
                @if (session('success'))
                    <div class="alert alert-success">
                        <x-icon name="check" :size="18" /> <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-error">
                        <x-icon name="wave" :size="18" /> <span>{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @include('partials.player')
    @include('partials.playlist-modal')

    <script>
        window.SoundKuConfig = {
            user: {
                id: {{ auth()->id() }},
                name: @json(auth()->user()->name),
                isAdmin: @json((bool) auth()->user()->is_admin),
            },
            likedIds: @json(auth()->user()->likedIds()),
            playlists: @json($navConfigPlaylists),
            urls: {
                like: @json(route('api.library.like')),
                history: @json(route('api.library.history.store')),
                clearHistory: @json(route('api.library.history.clear')),
                createPlaylist: @json(route('library.playlists.store')),
                addTrack: @json(url('/library/playlists/__ID__/tracks')),
                playlist: @json(url('/library/playlists/__ID__')),
            },
            csrfToken: @json(csrf_token()),
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/hls.js@1/dist/hls.min.js" defer></script>
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="{{ asset('js/player.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
