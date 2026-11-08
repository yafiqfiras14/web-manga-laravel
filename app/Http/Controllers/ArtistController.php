<?php

namespace App\Http\Controllers;

use App\Exceptions\SoundCloudException;
use App\Services\SoundCloudService;

class ArtistController extends Controller
{
    public function show(string $id, SoundCloudService $soundcloud)
    {
        $artist = $soundcloud->user($id);

        abort_if($artist === null, 404, 'Artis tidak ditemukan atau SoundCloud sedang tidak bisa diakses.');

        $tracks = [];

        try {
            $tracks = $soundcloud->userTracks($id, 30);
        } catch (SoundCloudException) {
            // Halaman profil tetap tampil walau daftar track gagal dimuat.
        }

        return view('artist.show', compact('artist', 'tracks'));
    }
}
