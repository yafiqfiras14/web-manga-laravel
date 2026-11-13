<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#08090d" id="theme-color-meta">
    <title>@yield('title', 'Masuk — SoundKu')</title>

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
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="login-body">
    <button class="icon-btn auth-theme-toggle" type="button" data-theme-toggle aria-label="Ganti tema" title="Ganti tema">
        <x-icon name="sun" :size="18" class="theme-icon theme-icon-sun" />
        <x-icon name="moon" :size="18" class="theme-icon theme-icon-moon" />
    </button>

    <div class="login-shell">
        <aside class="login-aside">
            <a href="{{ route('login') }}" class="brand">
                <span class="brand-mark"><x-icon name="wave" :size="22" /></span>
                <span class="brand-text">Sound<span>Ku</span></span>
            </a>

            <div class="login-aside-copy">
                <span class="eyebrow"><x-icon name="sparkles" :size="14" /> @yield('eyebrow', 'Portal pendengar')</span>
                <h1>@yield('aside-title', 'Musik kamu, tersimpan rapi.')</h1>
                <p>@yield('aside-copy', 'Buat akun gratis untuk menyimpan lagu favorit, bikin playlist sendiri, dan lanjutin lagu yang terakhir kamu dengar.')</p>

                <ul class="auth-benefits">
                    <li><x-icon name="heart" :size="16" /> Lagu yang disukai tersimpan di akunmu.</li>
                    <li><x-icon name="queue" :size="16" /> Playlist buatan sendiri, rapi dan bisa diatur.</li>
                    <li><x-icon name="clock" :size="16" /> Riwayat putar selalu bisa dilanjutkan.</li>
                </ul>
            </div>

            <div class="login-equalizer" aria-hidden="true">
                <span></span><span></span><span></span><span></span><span></span><span></span>
            </div>
        </aside>

        <main class="login-main">
            <div class="login-card">
                @yield('card')
            </div>
        </main>
    </div>

    <script src="{{ asset('js/app.js') }}" defer></script>
</body>
</html>
