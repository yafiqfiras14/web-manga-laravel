<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Models\User;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $playlists = $user->playlists()->withCount('tracks')->latest()->get();
        $liked = $user->likedTracks()->limit(40)->get();
        $history = $user->playHistories()->limit(12)->get();

        return view('library.index', [
            'playlists' => $playlists,
            'liked' => $liked,
            'history' => $history,
            'likedCount' => $user->likedTracks()->count(),
            'historyCount' => $user->playHistories()->count(),
        ]);
    }

    public function liked(Request $request)
    {
        $tracks = $request->user()->likedTracks()->limit(500)->get();

        return view('library.liked', ['tracks' => $tracks]);
    }

    public function history(Request $request)
    {
        $tracks = $request->user()->playHistories()->paginate(50);

        return view('library.history', ['tracks' => $tracks]);
    }

    public function account(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        return view('library.account', [
            'playlistCount' => $user->playlists()->count(),
            'likedCount' => $user->likedTracks()->count(),
            'historyCount' => $user->playHistories()->count(),
        ]);
    }
}
