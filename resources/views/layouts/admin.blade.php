<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') &mdash; MangaKu</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="admin-body">
    <nav class="navbar">
        <div class="navbar-inner">
            <a href="{{ route('admin.dashboard') }}" class="brand">Manga<span>Ku</span> Admin</a>
            <div class="nav-links">
                <a href="{{ route('home') }}" target="_blank">Lihat Situs</a>
                <form action="{{ route('admin.logout') }}" method="POST" class="inline-form">
                    @csrf
                    <button type="submit" class="btn btn-link">Logout ({{ auth()->user()->name }})</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="container">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
