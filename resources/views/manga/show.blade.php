@extends('layouts.app')

@section('title', $manga->title)

@section('content')
    <div class="detail-layout">
        <div class="detail-cover">
            @if ($manga->cover)
                <img src="{{ asset('storage/'.$manga->cover) }}" alt="{{ $manga->title }}">
            @else
                <div class="cover-placeholder cover-placeholder-lg">{{ $manga->title }}</div>
            @endif
        </div>

        <div class="detail-info">
            <h1 class="detail-title">{{ $manga->title }}</h1>
            <p class="detail-meta">
                <span class="badge badge-{{ $manga->status }}">{{ $manga->statusLabel() }}</span>
                @if ($manga->author)
                    <span>Author: {{ $manga->author }}</span>
                @endif
            </p>

            @if ($manga->synopsis)
                <div class="synopsis">
                    <h3>Sinopsis</h3>
                    <p>{!! nl2br(e($manga->synopsis)) !!}</p>
                </div>
            @endif

            <h3 class="section-title">Daftar Chapter ({{ $manga->chapters->count() }})</h3>
            <ul class="chapter-list">
                @forelse ($manga->chapters as $chapter)
                    <li>
                        <a href="{{ route('chapter.show', [$manga->slug, $chapter->number]) }}">
                            <span>Chapter {{ rtrim(rtrim(number_format($chapter->number, 1, '.', ''), '0'), '.') }}{{ $chapter->title ? ' — '.$chapter->title : '' }}</span>
                            <span class="chapter-date">
                                {{ $chapter->published_at?->format('d M Y') ?? '-' }}
                            </span>
                        </a>
                    </li>
                @empty
                    <li class="empty">Belum ada chapter.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
