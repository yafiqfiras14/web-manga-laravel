<?php

namespace App\Http\Controllers;

use App\Services\SoundCloudService;

class PlaylistController extends Controller
{
    public function show(string $id, SoundCloudService $soundcloud)
    {
        $playlist = $soundcloud->playlist($id);

        abort_if($playlist === null, 404, 'Playlist tidak ditemukan atau SoundCloud sedang tidak bisa diakses.');

        return view('playlist.show', compact('playlist'));
    }
}
