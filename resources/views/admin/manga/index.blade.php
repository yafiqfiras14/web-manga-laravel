@extends('layouts.admin')

@section('title', 'Kelola Manga')

@section('content')
    <div class="page-header">
        <h1>Kelola Manga</h1>
        <a href="{{ route('admin.manga.create') }}" class="btn btn-primary">+ Tambah Manga</a>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Cover</th>
                <th>Judul</th>
                <th>Status</th>
                <th>Chapter</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($manga as $item)
                <tr>
                    <td>
                        @if ($item->cover)
                            <img src="{{ asset('storage/'.$item->cover) }}" class="thumb" alt="">
                        @else
                            <div class="cover-placeholder thumb-placeholder">-</div>
                        @endif
                    </td>
                    <td>{{ $item->title }}</td>
                    <td><span class="badge badge-{{ $item->status }}">{{ $item->statusLabel() }}</span></td>
                    <td>{{ $item->chapters_count }}</td>
                    <td class="actions">
                        <a href="{{ route('manga.show', $item->slug) }}" class="btn btn-sm" target="_blank">Lihat</a>
                        <a href="{{ route('admin.manga.edit', $item) }}" class="btn btn-sm">Edit</a>
                        <a href="{{ route('admin.chapters.create', $item) }}" class="btn btn-sm">+ Chapter</a>
                        <form action="{{ route('admin.manga.destroy', $item) }}" method="POST" class="inline-form" onsubmit="return confirm('Hapus manga beserta semua chapter & halamannya?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">Belum ada manga.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">
        {{ $manga->links() }}
    </div>
@endsection
