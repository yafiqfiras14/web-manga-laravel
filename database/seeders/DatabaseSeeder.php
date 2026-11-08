<?php

namespace Database\Seeders;

use App\Models\FeaturedTrack;
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
            ]
        );

        $this->seedFeaturedTracks();
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
}
