<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\SoundCloudException;
use App\Http\Controllers\Controller;
use App\Models\FeaturedTrack;
use App\Services\SoundCloudService;
use Illuminate\Http\Request;

class FeaturedController extends Controller
{
    public function index()
    {
        $tracks = FeaturedTrack::ordered()->paginate(20);

        return view('admin.featured.index', compact('tracks'));
    }

    public function create()
    {
        return view('admin.featured.form', [
            'featured' => new FeaturedTrack(['is_active' => true, 'position' => 0]),
        ]);
    }

    public function store(Request $request, SoundCloudService $soundcloud)
    {
        $data = $request->validate([
            'url' => ['required', 'string', 'max:2048'],
        ]);

        try {
            $resolved = $soundcloud->resolve($data['url']);
        } catch (SoundCloudException $exception) {
            return back()->withErrors(['url' => $exception->getMessage()])->withInput();
        }

        if ($resolved === null) {
            return back()->withErrors([
                'url' => 'URL SoundCloud tidak bisa dibaca. Pastikan link track/playlist valid dan publik.',
            ])->withInput();
        }

        $added = 0;

        if (($resolved['kind'] ?? null) === 'track') {
            $this->saveTrack($soundcloud->normalizeTrack($resolved));
            $added = 1;
        } elseif (($resolved['kind'] ?? null) === 'playlist') {
            $playlist = $soundcloud->normalizePlaylist($resolved);

            foreach ($playlist['tracks'] as $track) {
                $this->saveTrack($track);
                $added++;
            }
        } else {
            return back()->withErrors([
                'url' => 'Masukkan URL track atau playlist SoundCloud (bukan profil artis).',
            ])->withInput();
        }

        return redirect()
            ->route('admin.featured.index')
            ->with('success', $added === 1
                ? 'Track berhasil ditambahkan ke pilihan editor.'
                : "{$added} track berhasil ditambahkan ke pilihan editor.");
    }

    public function edit(FeaturedTrack $featured)
    {
        return view('admin.featured.form', compact('featured'));
    }

    public function update(Request $request, FeaturedTrack $featured)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'artist' => ['nullable', 'string', 'max:255'],
            'genre' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['position'] = (int) ($data['position'] ?? 0);

        $featured->update($data);

        return redirect()
            ->route('admin.featured.index')
            ->with('success', 'Pilihan editor berhasil diperbarui.');
    }

    public function toggle(FeaturedTrack $featured)
    {
        $featured->update(['is_active' => ! $featured->is_active]);

        return back()->with('success', $featured->is_active
            ? 'Track ditampilkan di halaman utama.'
            : 'Track disembunyikan dari halaman utama.');
    }

    public function destroy(FeaturedTrack $featured)
    {
        $featured->delete();

        return back()->with('success', 'Track dihapus dari pilihan editor.');
    }

    /**
     * @param  array<string, mixed>  $track
     */
    private function saveTrack(array $track): void
    {
        $existing = FeaturedTrack::where('soundcloud_id', $track['id'])->first();

        FeaturedTrack::updateOrCreate(
            ['soundcloud_id' => $track['id']],
            [
                'title' => $track['title'],
                'artist' => $track['artist'],
                'artist_id' => $track['artist_id'],
                'artwork_url' => $track['artwork'],
                'permalink_url' => $track['permalink_url'],
                'genre' => $track['genre'],
                'duration' => $track['duration'],
                'position' => $existing?->position ?? ((int) FeaturedTrack::max('position') + 1),
                'is_active' => true,
            ]
        );
    }
}
