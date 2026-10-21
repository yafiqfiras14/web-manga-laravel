@extends('layouts.app')

@section('title', $query !== '' ? "Cari: {$query}" : 'MangaKu — Daftar Manga')

@section('content')
    @if ($query !== '')
        <h2 class="section-title">Hasil pencarian untuk &ldquo;{{ $query }}&rdquo;</h2>
    @else
        <h2 class="section-title">Daftar Manga</h2>
    @endif

    <div class="home-layout">
        <div>
            @if ($manga->isEmpty())
                <p class="empty">Manga nggak ditemukan.</p>
            @else
                <div class="manga-grid">
                    @foreach ($manga as $item)
                        @php $latest = $item->latestChapter(); @endphp
                        <a href="{{ route('manga.show', $item->slug) }}" class="manga-card">
                            <div class="cover-wrap">
                                @if ($item->cover)
                                    <img src="{{ asset('storage/'.$item->cover) }}" alt="{{ $item->title }}" loading="lazy">
                                @else
                                    <div class="cover-placeholder">{{ $item->title }}</div>
                                @endif
                                <span class="badge badge-{{ $item->status }}">{{ $item->statusLabel() }}</span>
                            </div>
                            <div class="manga-card-body">
                                <h3 class="manga-card-title">{{ $item->title }}</h3>
                                <p class="manga-card-meta">
                                    {{ $latest ? 'Ch. '.$latest->number : 'Belum ada chapter' }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="pagination">
                    {{ $manga->links() }}
                </div>
            @endif
        </div>

        <aside class="sidebar">
            <h3 class="sidebar-title">Chapter Terbaru</h3>
            <ul class="latest-list">
                @forelse ($latestChapters as $ch)
                    <li>
                        <a href="{{ route('chapter.show', [$ch->manga->slug, $ch->number]) }}">
                            <span class="latest-title">{{ $ch->manga->title }}</span>
                            <span class="latest-chapter">Ch. {{ rtrim(rtrim(number_format($ch->number, 1, '.', ''), '0'), '.') }}</span>
                        </a>
                    </li>
                @empty
                    <li class="empty">Belum ada chapter.</li>
                @endforelse
            </ul>
        </aside>
    </div>
@endsection
