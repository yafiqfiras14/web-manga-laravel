<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#08090d">
    <title>@yield('title', 'Admin') — SoundKu</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="admin-body">
    <header class="admin-topbar">
        <div class="admin-topbar-inner">
            <a href="{{ route('admin.dashboard') }}" class="brand brand-sm">
                <span class="brand-mark"><x-icon name="wave" :size="18" /></span>
                <span class="brand-text">Sound<span>Ku</span></span>
                <span class="admin-tag">Admin</span>
            </a>

            <nav class="admin-nav">
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">Dashboard</a>
                <a href="{{ route('admin.featured.index') }}" class="{{ request()->routeIs('admin.featured.*') ? 'is-active' : '' }}">Pilihan editor</a>
            </nav>

            <div class="admin-actions">
                <a href="{{ route('home') }}" target="_blank" rel="noopener" class="btn btn-ghost btn-sm">
                    <x-icon name="external" :size="14" /> Lihat situs
                </a>
                <form action="{{ route('admin.logout') }}" method="POST" class="inline-form">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm">
                        <x-icon name="logout" :size="14" /> Logout
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="admin-container">
        @if (session('success'))
            <div class="alert alert-success">
                <x-icon name="check" :size="18" /> <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <x-icon name="wave" :size="18" />
                <div>
                    <strong>Ada yang perlu dicek:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
