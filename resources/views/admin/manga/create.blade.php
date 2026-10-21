@extends('layouts.admin')

@section('title', 'Tambah Manga')

@section('content')
    <div class="page-header">
        <h1>Tambah Manga</h1>
        <a href="{{ route('admin.manga.index') }}" class="btn">&larr; Kembali</a>
    </div>

    <form action="{{ route('admin.manga.store') }}" method="POST" enctype="multipart/form-data" class="form-card">
        @csrf
        @include('admin.manga.form')
        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
@endsection
