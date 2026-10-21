@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="page-header">
        <h1>Dashboard</h1>
        <a href="{{ route('admin.manga.create') }}" class="btn btn-primary">+ Tambah Manga</a>
    </div>

    <div class="stat-grid">
        <div class="stat-card">
            <span class="stat-number">{{ $stats['manga'] }}</span>
            <span class="stat-label">Manga</span>
        </div>
        <div class="stat-card">
            <span class="stat-number">{{ $stats['chapters'] }}</span>
            <span class="stat-label">Chapter</span>
        </div>
        <div class="stat-card">
            <span class="stat-number">{{ $stats['pages'] }}</span>
            <span class="stat-label">Halaman</span>
        </div>
    </div>

    <h2 class="section-title">Manga Terakhir Diubah</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Judul</th>
                <th>Status</th>
                <th>Chapter</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($recentManga as $item)
                <tr>
                    <td>{{ $item->title }}</td>
                    <td><span class="badge badge-{{ $item->status }}">{{ $item->statusLabel() }}</span></td>
                    <td>{{ $item->chapters_count ?? $item->chapters()->count() }}</td>
                    <td>
                        <a href="{{ route('admin.manga.edit', $item) }}" class="btn btn-sm">Edit</a>
                        <a href="{{ route('admin.chapters.create', $item) }}" class="btn btn-sm">+ Chapter</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="empty">Belum ada manga.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
