<?php

namespace Tests\Feature;

use App\Models\FeaturedTrack;
use App\Models\User;
use App\Services\SoundCloudService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MusicSiteTest extends TestCase
{
    use RefreshDatabase;

    private function fakeSoundCloud(): void
    {
        $track = [
            'id' => '12345',
            'title' => 'Contoh Lagu',
            'artist' => 'Contoh Artis',
            'artist_id' => '777',
            'artist_avatar' => null,
            'artwork' => 'https://i1.sndcdn.com/artworks-contoh-t500x500.jpg',
            'duration' => 215,
            'genre' => 'Lo-fi',
            'likes' => 10,
            'plays' => 1200,
            'created_at' => '2026-01-01T00:00:00Z',
            'permalink_url' => 'https://soundcloud.com/contoh/lagu',
            'streamable' => true,
            'policy' => 'ALLOW',
            'description' => 'Deskripsi contoh.',
            'is_preview' => false,
        ];

        $artist = [
            'id' => '777',
            'username' => 'Contoh Artis',
            'avatar' => null,
            'followers' => 500,
            'track_count' => 12,
            'permalink_url' => 'https://soundcloud.com/contoh',
            'description' => 'Bio contoh.',
            'city' => 'Bandung',
            'country_code' => 'ID',
        ];

        $playlist = [
            'id' => '999',
            'title' => 'Playlist Contoh',
            'curator' => 'Contoh Artis',
            'curator_id' => '777',
            'artwork' => null,
            'track_count' => 1,
            'duration' => 215,
            'genre' => 'Lo-fi',
            'description' => 'Playlist contoh.',
            'permalink_url' => 'https://soundcloud.com/contoh/sets/contoh',
            'tracks' => [$track],
        ];

        $mock = \Mockery::mock(SoundCloudService::class);

        $mock->shouldReceive('chart')->andReturn([$track]);
        $mock->shouldReceive('searchTracks')->andReturn([$track]);
        $mock->shouldReceive('searchUsers')->andReturn([$artist]);
        $mock->shouldReceive('searchPlaylists')->andReturn([$playlist]);
        $mock->shouldReceive('track')->andReturn($track);
        $mock->shouldReceive('relatedTracks')->andReturn([$track]);
        $mock->shouldReceive('user')->andReturn($artist);
        $mock->shouldReceive('userTracks')->andReturn([$track]);
        $mock->shouldReceive('playlist')->andReturn($playlist);
        $mock->shouldReceive('stream')->andReturn([
            'type' => 'progressive',
            'url' => 'https://cf-media.sndcdn.com/contoh.mp3',
            'is_preview' => false,
        ]);
        $mock->shouldReceive('hlsPlaylist')->andReturn("#EXTM3U\n");
        $mock->shouldReceive('isAllowedMediaUrl')->andReturnTrue();
        $mock->shouldReceive('resolve')->andReturnUsing(function (string $url) {
            return str_contains($url, '/sets/')
                ? ['kind' => 'playlist', 'data' => []]
                : ['kind' => 'track', 'data' => []];
        });
        $mock->shouldReceive('normalizeTrack')->andReturn($track);
        $mock->shouldReceive('normalizePlaylist')->andReturn($playlist);

        $this->app->instance(SoundCloudService::class, $mock);
    }

    public function test_home_page_renders(): void
    {
        $this->fakeSoundCloud();

        FeaturedTrack::create([
            'soundcloud_id' => '12345',
            'title' => 'Contoh Lagu',
            'artist' => 'Contoh Artis',
            'artist_id' => '777',
            'duration' => 215,
            'position' => 0,
            'is_active' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Contoh Lagu')
            ->assertSee('Trending saat ini');
    }

    public function test_search_page_renders_results(): void
    {
        $this->fakeSoundCloud();

        $this->get('/search?q=lofi')
            ->assertOk()
            ->assertSee('Contoh Lagu')
            ->assertSee('Contoh Artis')
            ->assertSee('Playlist Contoh');
    }

    public function test_empty_search_shows_suggestions(): void
    {
        $this->fakeSoundCloud();

        $this->get('/search')
            ->assertOk()
            ->assertSee('Mulai dari genre');
    }

    public function test_track_artist_and_playlist_pages_render(): void
    {
        $this->fakeSoundCloud();

        $this->get('/track/12345')->assertOk()->assertSee('Contoh Lagu');
        $this->get('/artist/777')->assertOk()->assertSee('Contoh Artis');
        $this->get('/playlist/999')->assertOk()->assertSee('Playlist Contoh');
    }

    public function test_genre_page_renders(): void
    {
        $this->fakeSoundCloud();

        $this->get('/genre/chillout')->assertOk()->assertSee('Chill / Lo-fi');
    }

    public function test_unknown_genre_returns_404(): void
    {
        $this->fakeSoundCloud();

        $this->get('/genre/ngawur')->assertNotFound();
    }

    public function test_api_stream_returns_json(): void
    {
        $this->fakeSoundCloud();

        $this->getJson('/api/stream/12345')
            ->assertOk()
            ->assertJsonPath('type', 'progressive')
            ->assertJsonPath('is_preview', false);
    }

    public function test_favorites_page_renders(): void
    {
        $this->get('/favorites')->assertOk()->assertSee('Lagu favorit');
    }

    public function test_admin_pages_require_authentication(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/featured')->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_featured_tracks(): void
    {
        $user = User::factory()->create();

        FeaturedTrack::create([
            'soundcloud_id' => '12345',
            'title' => 'Contoh Lagu',
            'artist' => 'Contoh Artis',
            'duration' => 215,
            'position' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get('/admin/featured')
            ->assertOk()
            ->assertSee('Contoh Lagu')
            ->assertSee('Pilihan editor');
    }

    public function test_admin_can_toggle_featured_track(): void
    {
        $user = User::factory()->create();

        $track = FeaturedTrack::create([
            'soundcloud_id' => '12345',
            'title' => 'Contoh Lagu',
            'artist' => 'Contoh Artis',
            'duration' => 215,
            'position' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->patch("/admin/featured/{$track->id}/toggle")
            ->assertRedirect();

        $this->assertFalse($track->fresh()->is_active);
    }

    public function test_admin_can_add_track_from_soundcloud_url(): void
    {
        $this->fakeSoundCloud();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/admin/featured', ['url' => 'https://soundcloud.com/contoh/lagu'])
            ->assertRedirect('/admin/featured');

        $this->assertDatabaseHas('featured_tracks', [
            'soundcloud_id' => '12345',
            'title' => 'Contoh Lagu',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_add_playlist_from_soundcloud_url(): void
    {
        $this->fakeSoundCloud();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/admin/featured', ['url' => 'https://soundcloud.com/contoh/sets/contoh'])
            ->assertRedirect('/admin/featured');

        $this->assertDatabaseHas('featured_tracks', [
            'soundcloud_id' => '12345',
            'title' => 'Contoh Lagu',
        ]);
    }

    public function test_admin_can_delete_featured_track(): void
    {
        $this->fakeSoundCloud();

        $user = User::factory()->create();

        $track = FeaturedTrack::create([
            'soundcloud_id' => '12345',
            'title' => 'Contoh Lagu',
            'artist' => 'Contoh Artis',
            'duration' => 215,
            'position' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->delete("/admin/featured/{$track->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('featured_tracks', ['id' => $track->id]);
    }
}
