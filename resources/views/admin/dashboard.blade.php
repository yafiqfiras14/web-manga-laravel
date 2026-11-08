@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="page-header">
        <div>
            <h1>Dashboard</h1>
            <p>Ringkasan konten yang tampil di SoundKu.</p>
        </div>
        <a href="{{ route('admin.featured.create') }}" class="btn btn-primary">
            <x-icon name="plus" :size="16" /> Tambah lagu
        </a>
    </div>

    <div class="stat-grid">
        <div class="stat-card">
            <span class="stat-icon"><x-icon name="sparkles" :size="18" /></span>
            <span class="stat-number">{{ $stats['featured'] }}</span>
            <span class="stat-label">Total pilihan editor</span>
        </div>
        <div class="stat-card">
            <span class="stat-icon"><x-icon name="check" :size="18" /></span>
            <span class="stat-number">{{ $stats['active'] }}</span>
            <span class="stat-label">Tampil di beranda</span>
        </div>
        <div class="stat-card">
            <span class="stat-icon"><x-icon name="users" :size="18" /></span>
            <span class="stat-number">{{ $stats['users'] }}</span>
            <span class="stat-label">Akun admin</span>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <div>
                <h2>Pilihan editor terbaru</h2>
                <p>Lagu yang dikurasi manual untuk beranda.</p>
            </div>
            <a href="{{ route('admin.featured.index') }}" class="btn btn-ghost btn-sm">Lihat semua</a>
        </div>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Track</th>
                        <th>Genre</th>
                        <th>Durasi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentTracks as $track)
                        <tr>
                            <td>
                                <div class="table-track">
                                    @if ($track->artwork_url)
                                        <img src="{{ $track->artwork_url }}" alt="" class="table-art">
                                    @else
                                        <div class="table-art art-placeholder"><x-icon name="music" :size="14" /></div>
                                    @endif
                                    <div>
                                        <span class="table-title">{{ $track->title }}</span>
                                        <span class="table-meta">{{ $track->artist }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $track->genre ?: '—' }}</td>
                            <td>{{ format_duration((int) $track->duration) }}</td>
                            <td>
                                <span class="badge {{ $track->is_active ? 'badge-active' : 'badge-inactive' }}">
                                    {{ $track->is_active ? 'Tampil' : 'Disembunyikan' }}
                                </span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('admin.featured.edit', $track) }}" class="btn btn-sm btn-outline">Edit</a>
                                    <form action="{{ route('admin.featured.toggle', $track) }}" method="POST" class="inline-form">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-ghost">
                                            {{ $track->is_active ? 'Sembunyikan' : 'Tampilkan' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty">Belum ada pilihan editor. Tambahkan lagu pertama kamu.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
