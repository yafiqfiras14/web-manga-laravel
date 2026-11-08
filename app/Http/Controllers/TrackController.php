<?php

namespace App\Http\Controllers;

use App\Exceptions\SoundCloudException;
use App\Services\SoundCloudService;

class TrackController extends Controller
{
    public function show(string $id, SoundCloudService $soundcloud)
    {
        $track = $soundcloud->track($id);

        abort_if($track === null, 404, 'Track tidak ditemukan atau SoundCloud sedang tidak bisa diakses.');

        $related = [];

        try {
            $related = array_values(array_filter(
                $soundcloud->relatedTracks($id, 8),
                fn (array $item): bool => $item['id'] !== $track['id']
            ));
        } catch (SoundCloudException) {
            // Track tetap bisa dibuka walau rekomendasi gagal dimuat.
        }

        return view('track.show', compact('track', 'related'));
    }
}
