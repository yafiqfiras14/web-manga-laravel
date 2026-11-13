<?php

namespace Database\Seeders;

use App\Models\FeaturedTrack;
use App\Models\LikedTrack;
use App\Models\PlayHistory;
use App\Models\User;
use App\Services\SoundCloudService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@soundku.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ]
        );

        $demo = User::updateOrCreate(
            ['email' => 'demo@soundku.test'],
            [
                'name' => 'Demo Listener',
                'password' => Hash::make('password'),
                'is_admin' => false,
            ]
        );

        $this->seedFeaturedTracks();
        $this->seedDemoLibrary($demo);
    }

    /**
     * Isi pilihan editor dengan beberapa track trending supaya beranda tidak
     * kosong saat pertama kali dibuka. Kalau SoundCloud tidak bisa diakses,
     * seeding tetap dianggap berhasil (kamu bisa menambah lagu dari panel admin).
     */
    private function seedFeaturedTracks(): void
    {
        if (FeaturedTrack::query()->exists()) {
            return;
        }

        try {
            $tracks = app(SoundCloudService::class)->chart('all-music', 8);
        } catch (\Throwable) {
            $this->command?->warn('SoundCloud tidak bisa diakses, pilihan editor dilewati.');

            return;
        }

        $position = 0;

        foreach ($tracks as $track) {
            if (empty($track['id']) || empty($track['streamable'])) {
                continue;
            }

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
                    'position' => $position++,
                    'is_active' => true,
                ]
            );
        }

        $this->command?->info("{$position} track trending ditambahkan ke pilihan editor.");
    }

    /**
     * Akun demo dengan playlist, lagu disukai, dan riwayat supaya halaman
     * perpustakaan langsung terlihat hidup saat presentasi.
     */
    private function seedDemoLibrary(User $demo): void
    {
        if ($demo->playlists()->exists()) {
            return;
        }

        $tracks = FeaturedTrack::active()
            ->ordered()
            ->limit(10)
            ->get()
            ->map(fn (FeaturedTrack $track): array => $track->toTrack())
            ->all();

        if (count($tracks) === 0) {
            return;
        }

        $grouped = [
            'Chill malam' => array_slice($tracks, 0, 4),
            'Favorit aku' => array_slice($tracks, 4, 4),
        ];

        foreach ($grouped as $name => $items) {
            if (count($items) === 0) {
                continue;
            }

            $playlist = $demo->playlists()->create(['name' => $name]);
            $playlist->update(['cover_url' => $items[0]['artwork'] ?? null]);

            foreach ($items as $position => $track) {
                $playlist->tracks()->create([
                    ...\App\Models\PlaylistTrack::metadataFromTrack($track),
                    'position' => $position,
                ]);
            }
        }

        foreach (array_slice($tracks, 0, 6) as $track) {
            $demo->likedTracks()->create(LikedTrack::metadataFromTrack($track));
        }

        foreach (array_slice($tracks, 0, 4) as $index => $track) {
            $demo->playHistories()->create([
                ...PlayHistory::metadataFromTrack($track),
                'played_at' => now()->subMinutes($index * 12),
            ]);
        }
    }
}
