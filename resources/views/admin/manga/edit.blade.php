@extends('layouts.admin')

@section('title', 'Edit: '.$manga->title)

@section('content')
    <div class="page-header">
        <h1>Edit Manga</h1>
        <a href="{{ route('admin.manga.index') }}" class="btn">&larr; Kembali</a>
    </div>

    <form action="{{ route('admin.manga.update', $manga) }}" method="POST" enctype="multipart/form-data" class="form-card">
        @csrf
        @method('PUT')
        @include('admin.manga.form')
        <button type="submit" class="btn btn-primary">Update</button>
    </form>

    <div class="page-header">
        <h2>Chapter ({{ $manga->chapters->count() }})</h2>
        <a href="{{ route('admin.chapters.create', $manga) }}" class="btn btn-primary">+ Tambah Chapter</a>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>No.</th>
                <th>Judul</th>
                <th>Tanggal Rilis</th>
                <th>Halaman</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($manga->chapters as $chapter)
                <tr>
                    <td>{{ rtrim(rtrim(number_format($chapter->number, 1, '.', ''), '0'), '.') }}</td>
                    <td>{{ $chapter->title ?? '-' }}</td>
                    <td>{{ $chapter->published_at?->format('d M Y') ?? '-' }}</td>
                    <td>{{ $chapter->pages_count }}</td>
                    <td class="actions">
                        <a href="{{ route('chapter.show', [$manga->slug, $chapter->number]) }}" class="btn btn-sm" target="_blank">Lihat</a>
                        <form action="{{ route('admin.chapters.destroy', [$manga, $chapter]) }}" method="POST" class="inline-form" onsubmit="return confirm('Hapus chapter ini beserta semua halamannya?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">Belum ada chapter.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
