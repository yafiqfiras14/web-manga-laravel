<?php

namespace App\Http\Controllers;

use App\Exceptions\SoundCloudException;
use App\Services\SoundCloudService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request, SoundCloudService $soundcloud)
    {
        $query = trim((string) $request->query('q'));
        $tracks = [];
        $artists = [];
        $playlists = [];
        $error = null;

        if ($query !== '') {
            try {
                $tracks = $soundcloud->searchTracks($query, 24);
                $artists = $soundcloud->searchUsers($query, 6);
                $playlists = $soundcloud->searchPlaylists($query, 6);
            } catch (SoundCloudException $exception) {
                $error = $exception->getMessage();
            }
        }

        return view('search', compact('query', 'tracks', 'artists', 'playlists', 'error'));
    }
}
