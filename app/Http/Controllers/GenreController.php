<?php

namespace App\Http\Controllers;

use App\Exceptions\SoundCloudException;
use App\Services\SoundCloudService;

class GenreController extends Controller
{
    public function show(string $genre, SoundCloudService $soundcloud)
    {
        $config = config("music.genres.{$genre}");

        abort_if($config === null, 404);

        $tracks = [];
        $error = null;

        try {
            $tracks = $soundcloud->chart($genre, 24);
        } catch (SoundCloudException $exception) {
            $error = $exception->getMessage();
        }

        return view('genre.show', [
            'genre' => $genre,
            'genreConfig' => $config,
            'tracks' => $tracks,
            'error' => $error,
        ]);
    }
}
