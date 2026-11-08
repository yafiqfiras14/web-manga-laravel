@extends('layouts.app')

@section('title', $genreConfig['label'].' — SoundKu')

@section('content')
    @if ($error)
        <div class="alert alert-error">
            <x-icon name="wave" :size="18" />
            <span>{{ $error }}</span>
        </div>
    @endif

    <header class="page-header page-header-genre">
        <div>
            <span class="eyebrow"><x-icon name="compass" :size="14" /> Genre</span>
            <h1>{{ $genreConfig['label'] }}</h1>
            <p>{{ $genreConfig['label'] }} pilihan dari chart dan pencarian SoundCloud.</p>
        </div>
        @if (count($tracks) > 0)
            <button class="btn btn-primary" type="button" data-play-all data-queue="{{ track_json($tracks) }}">
                <x-icon name="play" :size="16" /> Putar semua
            </button>
        @endif
    </header>

    @include('partials.genre-chips')

    <section class="section" data-queue="{{ track_json($tracks) }}">
        <div class="section-head">
            <div>
                <h2>Rekomendasi {{ $genreConfig['label'] }}</h2>
                <p>{{ count($tracks) }} track siap diputar.</p>
            </div>
        </div>

        @if (count($tracks) > 0)
            <div class="track-grid">
                @foreach ($tracks as $index => $track)
                    @include('partials.track-card', ['track' => $track, 'index' => $index])
                @endforeach
            </div>
        @else
            <div class="glass empty-state">
                <x-icon name="disc" :size="30" />
                <h2>Belum ada track untuk genre ini</h2>
                <p>Coba genre lain, atau kembali lagi beberapa saat lagi.</p>
                <a href="{{ route('home') }}" class="btn btn-outline">Kembali ke beranda</a>
            </div>
        @endif
    </section>
@endsection
