<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#08090d" id="theme-color-meta">
    <title>Login Admin — SoundKu</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
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

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="login-body">
    <button class="icon-btn auth-theme-toggle" type="button" data-theme-toggle aria-label="Ganti tema" title="Ganti tema">
        <x-icon name="sun" :size="18" class="theme-icon theme-icon-sun" />
        <x-icon name="moon" :size="18" class="theme-icon theme-icon-moon" />
    </button>

    <div class="login-shell">
        <aside class="login-aside">
            <a href="{{ route('home') }}" class="brand">
                <span class="brand-mark"><x-icon name="wave" :size="22" /></span>
                <span class="brand-text">Sound<span>Ku</span></span>
            </a>

            <div class="login-aside-copy">
                <span class="eyebrow"><x-icon name="sparkles" :size="14" /> Panel admin</span>
                <h1>Atur lagu yang tampil di beranda.</h1>
                <p>Tempel link track atau playlist SoundCloud, lalu atur urutan dan statusnya. Semua metadata diambil otomatis.</p>
            </div>

            <div class="login-equalizer" aria-hidden="true">
                <span></span><span></span><span></span><span></span><span></span><span></span>
            </div>
        </aside>

        <main class="login-main">
            <div class="login-card">
                <h2>Masuk ke admin</h2>
                <p class="login-sub">Gunakan akun admin yang dibuat oleh seeder.</p>

                @if ($errors->any())
                    <div class="alert alert-error">{{ $errors->first() }}</div>
                @endif

                <form action="{{ route('admin.login.attempt') }}" method="POST">
                    @csrf

                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">

                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">

                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" value="1"> Ingat saya
                    </label>

                    <button type="submit" class="btn btn-primary btn-block">Masuk</button>
                </form>

                <a href="{{ route('home') }}" class="login-back">&larr; Kembali ke situs</a>
            </div>
        </main>
    </div>

    <script src="{{ asset('js/app.js') }}" defer></script>
</body>
</html>
