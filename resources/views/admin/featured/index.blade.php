@extends('layouts.admin')

@section('title', 'Pilihan Editor')

@section('content')
    <div class="page-header">
        <div>
            <h1>Pilihan editor</h1>
            <p>Lagu di sini muncul di bagian &ldquo;Pilihan editor&rdquo; pada beranda.</p>
        </div>
        <a href="{{ route('admin.featured.create') }}" class="btn btn-primary">
            <x-icon name="plus" :size="16" /> Tambah lagu
        </a>
    </div>

    <div class="panel">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 56px;">#</th>
                        <th>Track</th>
                        <th>Genre</th>
                        <th>Durasi</th>
                        <th>Urutan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tracks as $track)
                        <tr>
                            <td class="muted">{{ $track->position }}</td>
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
                            <td>{{ $track->position }}</td>
                            <td>
                                <span class="badge {{ $track->is_active ? 'badge-active' : 'badge-inactive' }}">
                                    {{ $track->is_active ? 'Tampil' : 'Disembunyikan' }}
                                </span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('track.show', $track->soundcloud_id) }}" target="_blank"
                                       rel="noopener" class="btn btn-sm btn-ghost" title="Lihat di situs">
                                        <x-icon name="external" :size="14" />
                                    </a>
                                    <a href="{{ route('admin.featured.edit', $track) }}" class="btn btn-sm btn-outline">
                                        <x-icon name="pencil" :size="14" /> Edit
                                    </a>
                                    <form action="{{ route('admin.featured.toggle', $track) }}" method="POST" class="inline-form">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-ghost">
                                            {{ $track->is_active ? 'Sembunyikan' : 'Tampilkan' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.featured.destroy', $track) }}" method="POST" class="inline-form"
                                          onsubmit="return confirm('Hapus track ini dari pilihan editor?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                            <x-icon name="trash" :size="14" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty">
                                Belum ada lagu. <a href="{{ route('admin.featured.create') }}">Tambahkan lagu pertama</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="pagination">
        {{ $tracks->links('partials.pagination') }}
    </div>
@endsection
