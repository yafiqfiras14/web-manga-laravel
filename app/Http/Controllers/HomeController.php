<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Manga;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->query('q'));

        $manga = Manga::query()
            ->when($query !== '', function ($q) use ($query) {
                $q->where(function ($w) use ($query) {
                    $w->where('title', 'like', "%{$query}%")
                        ->orWhere('author', 'like', "%{$query}%");
                });
            })
            ->orderByDesc('updated_at')
            ->paginate(12)
            ->withQueryString();

        $latestChapters = Chapter::with('manga')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->take(10)
            ->get();

        return view('home', compact('manga', 'latestChapters', 'query'));
    }
}
