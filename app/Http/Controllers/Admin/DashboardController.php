<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Manga;
use App\Models\MangaPage;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'manga' => Manga::count(),
            'chapters' => Chapter::count(),
            'pages' => MangaPage::count(),
        ];

        $recentManga = Manga::orderByDesc('updated_at')->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentManga'));
    }
}
