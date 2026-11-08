@extends('layouts.app')

@section('title', $query !== '' ? "Cari: {$query} — SoundKu" : 'Cari musik — SoundKu')

@section('content')
    @if ($error)
        <div class="alert alert-error">
            <x-icon name="wave" :size="18" />
            <span>{{ $error }}</span>
        </div>
    @endif

    <header class="page-header">
        <div>
            <span class="eyebrow"><x-icon name="search" :size="14" /> Pencarian</span>
            <h1>
                @if ($query !== '')
                    Hasil untuk &ldquo;{{ $query }}&rdquo;
                @else
                    Cari musik
                @endif
            </h1>
            <p>
                @if ($query !== '')
                    {{ count($tracks) }} track ditemukan langsung dari katalog publik SoundCloud.
                @else
                    Ketik judul lagu, nama artis, atau kata kunci genre di kotak pencarian.
                @endif
            </p>
        </div>
    </header>

    @if ($query === '')
        <section class="section">
            <div class="section-head">
                <div>
                    <h2>Mulai dari genre</h2>
                    <p>Pilih suasana, biar kami yang carikan lagunya.</p>
                </div>
            </div>
            @include('partials.genre-chips')
        </section>

        <section class="glass empty-state">
            <x-icon name="music" :size="30" />
            <h2>Mau dengerin apa hari ini?</h2>
            <p>Coba cari &ldquo;lofi&rdquo;, &ldquo;hindia&rdquo;, &ldquo;electronic&rdquo;, atau nama artis favoritmu.</p>
        </section>
    @else
        @if (count($tracks) > 0)
            <section class="section" data-queue="{{ track_json($tracks) }}">
                <div class="section-head">
                    <div>
                        <h2>Track</h2>
                        <p>Klik lagu untuk memutar, atau buka detailnya.</p>
                    </div>
                    <button class="btn btn-ghost btn-sm" type="button" data-play-all>
                        <x-icon name="play" :size="15" /> Putar semua
                    </button>
                </div>

                <div class="track-list">
                    @foreach ($tracks as $index => $track)
                        @include('partials.track-row', ['track' => $track, 'index' => $index])
                    @endforeach
                </div>
            </section>
        @else
            <section class="glass empty-state">
                <x-icon name="search" :size="30" />
                <h2>Nggak ketemu hasilnya</h2>
                <p>Coba kata kunci lain yang lebih singkat, atau cek ejaannya.</p>
                <a class="btn btn-outline" href="{{ route('search') }}">Mulai pencarian baru</a>
            </section>
        @endif

        @if (count($artists) > 0)
            <section class="section">
                <div class="section-head">
                    <div>
                        <h2>Artis</h2>
                        <p>Profil yang cocok dengan pencarianmu.</p>
                    </div>
                </div>
                <div class="artist-grid">
                    @foreach ($artists as $artist)
                        @include('partials.artist-card', ['artist' => $artist])
                    @endforeach
                </div>
            </section>
        @endif

        @if (count($playlists) > 0)
            <section class="section">
                <div class="section-head">
                    <div>
                        <h2>Playlist</h2>
                        <p>Kumpulan lagu dari komunitas SoundCloud.</p>
                    </div>
                </div>
                <div class="playlist-grid">
                    @foreach ($playlists as $playlist)
                        @include('partials.playlist-card', ['playlist' => $playlist])
                    @endforeach
                </div>
            </section>
        @endif
    @endif
@endsection
