<?php

namespace App\Http\Controllers;

use App\Models\Manga;

class MangaController extends Controller
{
    public function show(string $slug)
    {
        $manga = Manga::where('slug', $slug)
            ->with(['chapters' => fn ($q) => $q->orderByDesc('number')])
            ->firstOrFail();

        return view('manga.show', compact('manga'));
    }
}
