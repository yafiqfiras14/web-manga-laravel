<?php

namespace App\Http\Controllers;

use App\Exceptions\SoundCloudException;
use App\Models\FeaturedTrack;
use App\Services\SoundCloudService;

class HomeController extends Controller
{
    public function index(SoundCloudService $soundcloud)
    {
        $editorPicks = FeaturedTrack::active()
            ->ordered()
            ->get()
            ->map(fn (FeaturedTrack $track): array => $track->toTrack())
            ->all();

        $trending = [];
        $chill = [];
        $error = null;

        try {
            $trending = $soundcloud->chart('all-music', 14);
            $chill = $soundcloud->searchTracks(config('music.sections.chill.query'), 8);
        } catch (SoundCloudException $exception) {
            $error = $exception->getMessage();
        }

        $featured = $editorPicks[0] ?? $trending[0] ?? null;

        return view('home', [
            'featured' => $featured,
            'editorPicks' => $editorPicks,
            'trending' => $trending,
            'chill' => $chill,
            'error' => $error,
        ]);
    }
}
