@extends('layouts.auth')

@section('title', 'Buat akun — SoundKu')
@section('eyebrow', 'Akun baru')
@section('aside-title', 'Biar musikmu nggak ilang.')
@section('aside-copy', 'Sekali daftar, semua lagu favorit, playlist, dan riwayat putar tersimpan di akunmu — bukan cuma di browser.')

@section('card')
    <span class="eyebrow"><x-icon name="sparkles" :size="14" /> Gratis, tanpa kartu kredit</span>
    <h2>Buat akun</h2>
    <p class="login-sub">Isi data singkat, langsung bisa mendengarkan.</p>

    @if ($errors->any())
        <div class="alert alert-error">
            <div>
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <form action="{{ route('register.attempt') }}" method="POST">
        @csrf

        <label for="name">Nama</label>
        <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Nama kamu">

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="kamu@email.com">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter">

        <label for="password_confirmation">Ulangi password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" placeholder="Ketik ulang password">

        <button type="submit" class="btn btn-primary btn-block">Daftar &amp; mulai dengar</button>
    </form>

    <p class="auth-switch">
        Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a>
    </p>
@endsection
