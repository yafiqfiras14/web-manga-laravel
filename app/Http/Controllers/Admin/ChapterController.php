<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Manga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChapterController extends Controller
{
    public function create(Manga $manga)
    {
        return view('admin.chapters.create', compact('manga'));
    }

    public function store(Request $request, Manga $manga)
    {
        $data = $request->validate([
            'number' => ['required', 'numeric', 'min:0', 'max:999999'],
            'title' => ['nullable', 'string', 'max:255'],
            'published_at' => ['nullable', 'date'],
            'pages' => ['required', 'array', 'min:1'],
            'pages.*' => ['image', 'max:10240'],
        ], [
            'pages.required' => 'Minimal upload 1 halaman.',
            'pages.*.image' => 'Semua file harus berupa gambar.',
            'pages.*.max' => 'Ukuran gambar maksimal 10MB.',
        ]);

        if ($manga->chapters()->where('number', $data['number'])->exists()) {
            return back()->withErrors(['number' => 'Nomor chapter ini sudah ada.'])->onlyInput();
        }

        $chapter = $manga->chapters()->create([
            'number' => $data['number'],
            'title' => $data['title'] ?? null,
            'published_at' => $data['published_at'] ?? now()->toDateString(),
        ]);

        $dir = 'pages/'.$manga->slug.'/chapter-'.$chapter->id;

        foreach ($request->file('pages') as $i => $file) {
            $path = $file->store($dir, 'public');
            $chapter->pages()->create([
                'image_path' => $path,
                'position' => $i + 1,
            ]);
        }

        return redirect()->route('admin.manga.edit', $manga)->with('success', "Chapter {$chapter->number} berhasil ditambahkan.");
    }

    public function destroy(Manga $manga, Chapter $chapter)
    {
        foreach ($chapter->pages as $page) {
            Storage::disk('public')->delete($page->image_path);
        }

        $chapter->delete();

        return redirect()->route('admin.manga.edit', $manga)->with('success', 'Chapter berhasil dihapus.');
    }
}
