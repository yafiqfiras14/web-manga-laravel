@extends('layouts.auth')

@section('title', 'Masuk — SoundKu')

@section('card')
    <span class="eyebrow"><x-icon name="wave" :size="14" /> Selamat datang lagi</span>
    <h2>Masuk ke SoundKu</h2>
    <p class="login-sub">Lanjutkan playlist dan lagu yang kamu simpan.</p>

    @if ($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('login.attempt') }}" method="POST">
        @csrf

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="kamu@email.com">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="••••••••">

        <label class="checkbox-label">
            <input type="checkbox" name="remember" value="1"> Ingat saya
        </label>

        <button type="submit" class="btn btn-primary btn-block">Masuk</button>
    </form>

    <p class="auth-switch">
        Belum punya akun? <a href="{{ route('register') }}">Daftar gratis</a>
    </p>

    <div class="auth-demo">
        <strong>Akun demo</strong>
        <span>demo@soundku.test · password</span>
    </div>
@endsection
