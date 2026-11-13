<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Models\PlaylistTrack;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserPlaylistController extends Controller
{
    public function show(Request $request, Playlist $playlist)
    {
        $this->authorizeOwner($request, $playlist);

        $tracks = $playlist->tracks()->get();
        $playlist->loadCount('tracks');

        return view('library.playlist', [
            'playlist' => $playlist,
            'tracks' => $tracks,
            'duration' => (int) $tracks->sum('duration'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePlaylist($request);

        $playlist = $request->user()->playlists()->create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'playlist' => $playlist->fresh()->toSummary(),
                'message' => 'Playlist “'.$playlist->name.'” dibuat.',
            ], 201);
        }

        return redirect()
            ->route('library.playlists.show', $playlist)
            ->with('success', 'Playlist “'.$playlist->name.'” dibuat.');
    }

    public function update(Request $request, Playlist $playlist)
    {
        $this->authorizeOwner($request, $playlist);

        $playlist->update($this->validatePlaylist($request));

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'playlist' => $playlist->fresh()->toSummary(),
                'message' => 'Playlist diperbarui.',
            ]);
        }

        return back()->with('success', 'Playlist diperbarui.');
    }

    public function destroy(Request $request, Playlist $playlist)
    {
        $this->authorizeOwner($request, $playlist);

        $name = $playlist->name;
        $playlist->delete();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => 'Playlist “'.$name.'” dihapus.']);
        }

        return redirect()
            ->route('library')
            ->with('success', 'Playlist “'.$name.'” dihapus.');
    }

    public function addTrack(Request $request, Playlist $playlist): JsonResponse
    {
        $this->authorizeOwner($request, $playlist);

        $request->validate([
            'track' => ['required', 'array'],
            'track.id' => ['required', 'string', 'max:64'],
            'track.title' => ['required', 'string', 'max:255'],
        ]);

        $track = $request->input('track');
        $soundcloudId = (string) $track['id'];

        if ($playlist->tracks()->where('soundcloud_id', $soundcloudId)->exists()) {
            return response()->json([
                'ok' => true,
                'already' => true,
                'track_count' => $playlist->tracks()->count(),
                'message' => 'Lagu itu sudah ada di playlist.',
            ]);
        }

        $position = (int) $playlist->tracks()->max('position') + 1;

        $playlist->tracks()->create([
            ...PlaylistTrack::metadataFromTrack($track),
            'position' => $position,
        ]);

        if (! $playlist->cover_url && ! empty($track['artwork'])) {
            $playlist->update(['cover_url' => $track['artwork']]);
        }

        return response()->json([
            'ok' => true,
            'already' => false,
            'track_count' => $playlist->tracks()->count(),
            'playlist' => $playlist->fresh()->toSummary(),
            'message' => 'Ditambahkan ke playlist “'.$playlist->name.'”.',
        ]);
    }

    public function removeTrack(Request $request, Playlist $playlist, PlaylistTrack $track)
    {
        $this->authorizeOwner($request, $playlist);
        abort_unless($track->playlist_id === $playlist->id, 404);

        $track->delete();

        $playlist->tracks()->get()->each(function (PlaylistTrack $item, int $index): void {
            if ($item->position !== $index) {
                $item->update(['position' => $index]);
            }
        });

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'track_count' => $playlist->tracks()->count(),
                'message' => 'Lagu dihapus dari playlist.',
            ]);
        }

        return back()->with('success', 'Lagu dihapus dari playlist.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePlaylist(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'min:1', 'max:80'],
            'description' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Nama playlist wajib diisi.',
            'name.max' => 'Nama playlist maksimal 80 karakter.',
        ]);
    }

    private function authorizeOwner(Request $request, Playlist $playlist): void
    {
        abort_unless($playlist->user_id === $request->user()->id, 403);
    }
}
