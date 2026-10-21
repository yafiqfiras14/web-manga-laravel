@extends('layouts.app')

@section('title', $manga->title.' Ch. '.$chapter->number)

@section('content')
    @php
        $fmt = fn ($n) => rtrim(rtrim(number_format($n, 1, '.', ''), '0'), '.');
    @endphp

    <div class="reader-header">
        <a href="{{ route('manga.show', $manga->slug) }}" class="reader-back">&larr; {{ $manga->title }}</a>
        <h1>Chapter {{ $fmt($chapter->number) }}{{ $chapter->title ? ' — '.$chapter->title : '' }}</h1>
        <div class="reader-nav">
            @if ($prev)
                <a href="{{ route('chapter.show', [$manga->slug, $prev->number]) }}">&larr; Ch {{ $fmt($prev->number) }}</a>
            @else
                <span class="disabled">&larr; Ch awal</span>
            @endif
            <a href="{{ route('manga.show', $manga->slug) }}">Daftar</a>
            @if ($next)
                <a href="{{ route('chapter.show', [$manga->slug, $next->number]) }}">Ch {{ $fmt($next->number) }} &rarr;</a>
            @else
                <span class="disabled">Ch terbaru &rarr;</span>
            @endif
        </div>
    </div>

    <div class="reader-pages">
        @forelse ($chapter->pages as $page)
            <img src="{{ asset('storage/'.$page->image_path) }}" alt="Halaman {{ $page->position }}" loading="lazy">
        @empty
            <p class="empty">Belum ada halaman di chapter ini.</p>
        @endforelse
    </div>

    <div class="reader-nav reader-nav-bottom">
        @if ($prev)
            <a href="{{ route('chapter.show', [$manga->slug, $prev->number]) }}">&larr; Ch {{ $fmt($prev->number) }}</a>
        @else
            <span class="disabled">&larr; Ch awal</span>
        @endif
        <a href="{{ route('manga.show', $manga->slug) }}">Daftar Chapter</a>
        @if ($next)
            <a href="{{ route('chapter.show', [$manga->slug, $next->number]) }}">Ch {{ $fmt($next->number) }} &rarr;</a>
        @else
            <span class="disabled">Ch terbaru &rarr;</span>
        @endif
    </div>
@endsection
