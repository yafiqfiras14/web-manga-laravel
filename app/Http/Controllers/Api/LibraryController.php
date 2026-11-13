<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LikedTrack;
use App\Models\PlayHistory;
use App\Models\Playlist;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    public function toggleLike(Request $request): JsonResponse
    {
        $request->validate([
            'track' => ['required', 'array'],
            'track.id' => ['required', 'string', 'max:64'],
            'track.title' => ['required', 'string', 'max:255'],
        ]);

        $track = $request->input('track');
        $soundcloudId = (string) $track['id'];

        /** @var User $user */
        $user = $request->user();

        $existing = $user->likedTracks()->where('soundcloud_id', $soundcloudId)->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            $user->likedTracks()->create(LikedTrack::metadataFromTrack($track));
            $liked = true;
        }

        return response()->json([
            'ok' => true,
            'liked' => $liked,
            'count' => $user->likedTracks()->count(),
            'message' => $liked ? 'Masuk ke Lagu yang disukai.' : 'Dihapus dari Lagu yang disukai.',
        ]);
    }

    public function storeHistory(Request $request): JsonResponse
    {
        $request->validate([
            'track' => ['required', 'array'],
            'track.id' => ['required', 'string', 'max:64'],
            'track.title' => ['required', 'string', 'max:255'],
        ]);

        $track = $request->input('track');

        /** @var User $user */
        $user = $request->user();

        $user->playHistories()->updateOrCreate(
            ['soundcloud_id' => (string) $track['id']],
            [
                ...PlayHistory::metadataFromTrack($track),
                'played_at' => now(),
            ]
        );

        // Simpan maksimal 200 lagu terakhir supaya tabel tidak membengkak.
        $keep = $user->playHistories()->limit(200)->pluck('id');
        if ($keep->count() === 200) {
            $user->playHistories()->whereNotIn('id', $keep)->delete();
        }

        return response()->json(['ok' => true]);
    }

    public function clearHistory(Request $request): JsonResponse
    {
        $request->user()->playHistories()->delete();

        return response()->json(['ok' => true, 'message' => 'Riwayat putar dibersihkan.']);
    }

    public function playlists(Request $request): JsonResponse
    {
        $playlists = $request->user()
            ->playlists()
            ->withCount('tracks')
            ->latest()
            ->get()
            ->map(fn (Playlist $playlist): array => $playlist->toSummary())
            ->all();

        return response()->json(['playlists' => $playlists]);
    }
}
