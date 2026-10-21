<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MangaKu')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-inner">
            <a href="{{ route('home') }}" class="brand">Manga<span>Ku</span></a>
            <form action="{{ route('home') }}" method="GET" class="search-form">
                <input type="text" name="q" placeholder="Cari judul / author..." value="{{ request('q') }}">
                <button type="submit">Cari</button>
            </form>
            <div class="nav-links">
                @auth
                    <a href="{{ route('admin.dashboard') }}">Admin</a>
                @else
                    <a href="{{ route('admin.login') }}">Login</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="container">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @yield('content')
    </main>

    <footer class="footer">
        MangaKu &mdash; baca manga online. Dibikin pake Laravel.
    </footer>
</body>
</html>
