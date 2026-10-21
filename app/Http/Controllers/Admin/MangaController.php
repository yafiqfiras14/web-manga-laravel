<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Manga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MangaController extends Controller
{
    public function index()
    {
        $manga = Manga::withCount('chapters')->orderByDesc('updated_at')->paginate(10);

        return view('admin.manga.index', compact('manga'));
    }

    public function create()
    {
        return view('admin.manga.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['nullable', 'string', 'max:255'],
            'synopsis' => ['nullable', 'string'],
            'status' => ['required', 'in:ongoing,completed,hiatus'],
            'cover' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('cover')) {
            $data['cover'] = $request->file('cover')->store('covers', 'public');
        }

        Manga::create($data);

        return redirect()->route('admin.manga.index')->with('success', 'Manga berhasil ditambahkan.');
    }

    public function edit(Manga $manga)
    {
        $manga->load(['chapters' => fn ($q) => $q->orderByDesc('number')->withCount('pages')]);

        return view('admin.manga.edit', compact('manga'));
    }

    public function update(Request $request, Manga $manga)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['nullable', 'string', 'max:255'],
            'synopsis' => ['nullable', 'string'],
            'status' => ['required', 'in:ongoing,completed,hiatus'],
            'cover' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('cover')) {
            if ($manga->cover) {
                Storage::disk('public')->delete($manga->cover);
            }
            $data['cover'] = $request->file('cover')->store('covers', 'public');
        }

        $manga->update($data);

        return redirect()->route('admin.manga.index')->with('success', 'Manga berhasil diupdate.');
    }

    public function destroy(Manga $manga)
    {
        foreach ($manga->chapters()->with('pages')->get() as $chapter) {
            foreach ($chapter->pages as $page) {
                Storage::disk('public')->delete($page->image_path);
            }
        }

        if ($manga->cover) {
            Storage::disk('public')->delete($manga->cover);
        }

        $manga->delete();

        return redirect()->route('admin.manga.index')->with('success', 'Manga berhasil dihapus.');
    }
}
