@extends('layouts.app')

@section('title', 'Akun — SoundKu')

@section('content')
    @php
        $user = auth()->user();
        $firstName = explode(' ', trim($user->name))[0];
    @endphp

    <header class="page-header">
        <div>
            <span class="eyebrow"><x-icon name="user" :size="14" /> Akun</span>
            <h1>Hai, {{ $firstName }}</h1>
            <p>Kelola akun dan lihat ringkasan koleksi musikmu.</p>
        </div>
        @if ($user->is_admin)
            <a class="btn btn-outline" href="{{ route('admin.dashboard') }}">
                <x-icon name="sparkles" :size="15" /> Buka panel admin
            </a>
        @endif
    </header>

    <div class="account-grid">
        <section class="glass account-profile">
            <div class="account-avatar">{{ $user->initials() }}</div>
            <h2>{{ $user->name }}</h2>
            <p>{{ $user->email }}</p>

            @if ($user->is_admin)
                <span class="account-badge"><x-icon name="sparkles" :size="12" /> Admin SoundKu</span>
            @else
                <span class="account-badge"><x-icon name="wave" :size="12" /> Pendengar SoundKu</span>
            @endif

            <dl class="account-details">
                <div>
                    <dt>Nama</dt>
                    <dd>{{ $user->name }}</dd>
                </div>
                <div>
                    <dt>Email</dt>
                    <dd>{{ $user->email }}</dd>
                </div>
                <div>
                    <dt>Bergabung</dt>
                    <dd>{{ $user->created_at?->format('d M Y') }}</dd>
                </div>
            </dl>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="btn btn-outline btn-block" type="submit">
                    <x-icon name="logout" :size="15" /> Keluar dari akun
                </button>
            </form>
        </section>

        <section class="account-main">
            <div class="library-stats account-stats">
                <a class="library-stat" href="{{ route('library.liked') }}">
                    <span class="library-stat-icon"><x-icon name="heart" :size="19" /></span>
                    <span class="library-stat-body">
                        <strong>{{ $likedCount }}</strong>
                        <span>Lagu disukai</span>
                    </span>
                    <x-icon name="chevron-right" :size="16" class="library-stat-arrow" />
                </a>

                <a class="library-stat" href="{{ route('library') }}">
                    <span class="library-stat-icon library-stat-icon-plus"><x-icon name="queue" :size="19" /></span>
                    <span class="library-stat-body">
                        <strong>{{ $playlistCount }}</strong>
                        <span>Playlist dibuat</span>
                    </span>
                    <x-icon name="chevron-right" :size="16" class="library-stat-arrow" />
                </a>

                <a class="library-stat" href="{{ route('library.history') }}">
                    <span class="library-stat-icon library-stat-icon-clock"><x-icon name="clock" :size="19" /></span>
                    <span class="library-stat-body">
                        <strong>{{ $historyCount }}</strong>
                        <span>Lagu pernah diputar</span>
                    </span>
                    <x-icon name="chevron-right" :size="16" class="library-stat-arrow" />
                </a>
            </div>

            <div class="glass account-note">
                <span class="account-note-icon"><x-icon name="wave" :size="22" /></span>
                <div>
                    <h3>Semua tersimpan di akun</h3>
                    <p>Lagu disukai, playlist, dan riwayat putar tersimpan di akunmu — bukan cuma di browser. Login dari perangkat lain, koleksinya tetap sama.</p>
                </div>
            </div>

            <div class="glass account-note">
                <span class="account-note-icon"><x-icon name="heart" :size="22" /></span>
                <div>
                    <h3>Bangun koleksi kamu</h3>
                    <p>Tekan ikon hati untuk menyukai lagu, atau tombol <strong>+</strong> untuk memasukkannya ke playlist. Semua bisa diatur dari halaman Perpustakaan.</p>
                </div>
            </div>

            <div class="account-links">
                <a class="btn btn-outline" href="{{ route('library') }}">
                    <x-icon name="queue" :size="15" /> Buka perpustakaan
                </a>
                <a class="btn btn-ghost" href="{{ route('home') }}">
                    <x-icon name="home" :size="15" /> Kembali ke beranda
                </a>
            </div>
        </section>
    </div>
@endsection
