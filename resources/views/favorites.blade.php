@extends('layouts.app')

@section('title', 'Favorit — SoundKu')

@section('content')
    <header class="page-header">
        <div>
            <span class="eyebrow"><x-icon name="heart" :size="14" /> Koleksi pribadi</span>
            <h1>Lagu favorit</h1>
            <p>Disimpan di browser ini. Nggak perlu akun, nggak hilang saat refresh.</p>
        </div>
        <button class="btn btn-outline" type="button" id="favorites-play-all" hidden>
            <x-icon name="play" :size="15" /> Putar semua
        </button>
    </header>

    <div class="track-list" id="favorites-list" data-queue="[]"></div>

    <div class="glass empty-state" id="favorites-empty">
        <x-icon name="heart" :size="30" />
        <h2>Belum ada lagu favorit</h2>
        <p>Tekan ikon hati di kartu lagu atau halaman detail untuk menyimpannya ke sini.</p>
        <a class="btn btn-primary" href="{{ route('home') }}">Cari lagu</a>
    </div>
@endsection
