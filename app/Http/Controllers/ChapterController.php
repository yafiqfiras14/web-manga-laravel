<?php

namespace App\Http\Controllers;

use App\Models\Manga;

class ChapterController extends Controller
{
    public function show(string $slug, string $number)
    {
        $manga = Manga::where('slug', $slug)->firstOrFail();

        $chapter = $manga->chapters()
            ->where('number', $number)
            ->with('pages')
            ->firstOrFail();

        $prev = $manga->chapters()
            ->where('number', '<', $chapter->number)
            ->orderByDesc('number')
            ->first();

        $next = $manga->chapters()
            ->where('number', '>', $chapter->number)
            ->orderBy('number')
            ->first();

        return view('chapter.show', compact('manga', 'chapter', 'prev', 'next'));
    }
}
