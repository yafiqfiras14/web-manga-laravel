@extends('layouts.app')

@section('title', 'Lagu disukai — SoundKu')

@section('content')
    @php
        $items = $tracks->map(fn ($item) => $item->toTrack())->all();
    @endphp

    <div data-queue="{{ track_json($items) }}">
        <header class="page-header page-header-ish">
            <div class="page-cover-hero">
                <div class="page-cover page-cover-liked">
                    <x-icon name="heart" :size="54" />
                </div>
                <div>
                    <span class="eyebrow"><x-icon name="heart" :size="14" /> Koleksi pribadi</span>
                    <h1>Lagu disukai</h1>
                    <p>{{ count($items) }} lagu yang kamu simpan di akun ini.</p>
                </div>
            </div>

            @if (count($items) > 0)
                <button class="btn btn-primary btn-lg" type="button" data-play-all>
                    <x-icon name="play" :size="17" /> Putar semua
                </button>
            @endif
        </header>

        @if (count($items) > 0)
            <div class="track-list" id="liked-list" data-liked-list>
                @foreach ($items as $index => $track)
                    @include('partials.track-row', ['track' => $track, 'index' => $index])
                @endforeach
            </div>
        @else
            <div class="glass empty-state">
                <x-icon name="heart" :size="30" />
                <h2>Belum ada lagu disukai</h2>
                <p>Tekan ikon hati di kartu lagu atau halaman detail untuk menyimpannya ke sini.</p>
                <a class="btn btn-primary" href="{{ route('home') }}">Cari lagu</a>
            </div>
        @endif
    </div>
@endsection
