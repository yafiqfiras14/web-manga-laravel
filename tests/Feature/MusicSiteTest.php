<?php

namespace Tests\Feature;

use App\Models\FeaturedTrack;
use App\Models\PlayHistory;
use App\Models\User;
use App\Services\SoundCloudService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MusicSiteTest extends TestCase
{
    use RefreshDatabase;

    private function fakeSoundCloud(): void
    {
        $track = $this->trackPayload();

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

    /**
     * @return array<string, mixed>
     */
    private function trackPayload(): array
    {
        return [
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
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->get('/search')->assertRedirect('/login');
        $this->get('/library')->assertRedirect('/login');
        $this->get('/account')->assertRedirect('/login');
    }

    public function test_login_page_renders(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Masuk ke SoundKu')
            ->assertSee('demo@soundku.test');
    }

    public function test_register_page_renders(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Buat akun');
    }

    public function test_user_can_register(): void
    {
        $this->post('/register', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'budi@example.com']);
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create(['password' => 'password']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
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

        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertOk()
            ->assertSee('Contoh Lagu')
            ->assertSee('Trending saat ini');
    }

    public function test_search_page_renders_results(): void
    {
        $this->fakeSoundCloud();

        $this->actingAs(User::factory()->create())
            ->get('/search?q=lofi')
            ->assertOk()
            ->assertSee('Contoh Lagu')
            ->assertSee('Contoh Artis')
            ->assertSee('Playlist Contoh');
    }

    public function test_empty_search_shows_suggestions(): void
    {
        $this->fakeSoundCloud();

        $this->actingAs(User::factory()->create())
            ->get('/search')
            ->assertOk()
            ->assertSee('Mulai dari genre');
    }

    public function test_track_artist_and_playlist_pages_render(): void
    {
        $this->fakeSoundCloud();

        $user = User::factory()->create();

        $this->actingAs($user)->get('/track/12345')->assertOk()->assertSee('Contoh Lagu');
        $this->actingAs($user)->get('/artist/777')->assertOk()->assertSee('Contoh Artis');
        $this->actingAs($user)->get('/playlist/999')->assertOk()->assertSee('Playlist Contoh');
    }

    public function test_genre_page_renders(): void
    {
        $this->fakeSoundCloud();

        $this->actingAs(User::factory()->create())
            ->get('/genre/chillout')
            ->assertOk()
            ->assertSee('Chill / Lo-fi');
    }

    public function test_unknown_genre_returns_404(): void
    {
        $this->fakeSoundCloud();

        $this->actingAs(User::factory()->create())
            ->get('/genre/ngawur')
            ->assertNotFound();
    }

    public function test_api_stream_returns_json(): void
    {
        $this->fakeSoundCloud();

        $this->actingAs(User::factory()->create())
            ->getJson('/api/stream/12345')
            ->assertOk()
            ->assertJsonPath('type', 'progressive')
            ->assertJsonPath('is_preview', false);
    }

    public function test_favorites_page_redirects_to_liked_songs(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/favorites')
            ->assertRedirect('/library/liked');
    }

    public function test_library_pages_render_user_collections(): void
    {
        $user = User::factory()->create();

        $playlist = $user->playlists()->create(['name' => 'Chill malam']);
        $playlist->tracks()->create([
            'soundcloud_id' => '12345',
            'title' => 'Contoh Lagu',
            'artist' => 'Contoh Artis',
            'duration' => 215,
            'position' => 0,
        ]);

        $user->likedTracks()->create([
            'soundcloud_id' => '12345',
            'title' => 'Contoh Lagu',
            'artist' => 'Contoh Artis',
            'duration' => 215,
        ]);

        $user->playHistories()->create([
            'soundcloud_id' => '12345',
            'title' => 'Contoh Lagu',
            'artist' => 'Contoh Artis',
            'duration' => 215,
            'played_at' => now(),
        ]);

        $this->actingAs($user)->get('/library')->assertOk()->assertSee('Chill malam');
        $this->actingAs($user)->get('/library/liked')->assertOk()->assertSee('Contoh Lagu');
        $this->actingAs($user)->get('/library/history')->assertOk()->assertSee('Contoh Lagu');
        $this->actingAs($user)->get('/account')->assertOk()->assertSee($user->email);
        $this->actingAs($user)->get("/library/playlists/{$playlist->id}")->assertOk()->assertSee('Chill malam');
    }

    public function test_user_can_create_update_and_delete_playlist(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/library/playlists', ['name' => 'Playlist Baru']);

        $response->assertCreated()->assertJsonPath('playlist.name', 'Playlist Baru');

        $playlist = $user->playlists()->firstOrFail();
        $this->assertSame('Playlist Baru', $playlist->name);

        $this->actingAs($user)
            ->putJson("/library/playlists/{$playlist->id}", ['name' => 'Playlist Ganti'])
            ->assertOk()
            ->assertJsonPath('playlist.name', 'Playlist Ganti');

        $this->actingAs($user)
            ->deleteJson("/library/playlists/{$playlist->id}")
            ->assertOk();

        $this->assertDatabaseMissing('playlists', ['id' => $playlist->id]);
    }

    public function test_user_can_add_and_remove_tracks_from_playlist(): void
    {
        $user = User::factory()->create();
        $playlist = $user->playlists()->create(['name' => 'Chill malam']);

        $this->actingAs($user)
            ->postJson("/library/playlists/{$playlist->id}/tracks", ['track' => $this->trackPayload()])
            ->assertOk()
            ->assertJsonPath('track_count', 1);

        $this->assertDatabaseHas('playlist_tracks', [
            'playlist_id' => $playlist->id,
            'soundcloud_id' => '12345',
        ]);

        $track = $playlist->tracks()->firstOrFail();

        $this->actingAs($user)
            ->deleteJson("/library/playlists/{$playlist->id}/tracks/{$track->id}")
            ->assertOk()
            ->assertJsonPath('track_count', 0);

        $this->assertDatabaseMissing('playlist_tracks', ['id' => $track->id]);
    }

    public function test_user_cannot_manage_another_users_playlist(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $playlist = $owner->playlists()->create(['name' => 'Punya orang']);

        $this->actingAs($other)
            ->get("/library/playlists/{$playlist->id}")
            ->assertForbidden();
    }

    public function test_like_toggle_and_history_endpoints(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/library/like', ['track' => $this->trackPayload()])
            ->assertOk()
            ->assertJsonPath('liked', true);

        $this->assertDatabaseHas('liked_tracks', [
            'user_id' => $user->id,
            'soundcloud_id' => '12345',
        ]);

        $this->actingAs($user)
            ->postJson('/api/library/like', ['track' => $this->trackPayload()])
            ->assertOk()
            ->assertJsonPath('liked', false);

        $this->assertDatabaseMissing('liked_tracks', [
            'user_id' => $user->id,
            'soundcloud_id' => '12345',
        ]);

        $this->actingAs($user)
            ->postJson('/api/library/history', ['track' => $this->trackPayload()])
            ->assertOk();

        $this->actingAs($user)
            ->postJson('/api/library/history', ['track' => $this->trackPayload()])
            ->assertOk();

        $this->assertSame(1, PlayHistory::query()->where('user_id', $user->id)->count());

        $this->actingAs($user)
            ->deleteJson('/api/library/history')
            ->assertOk();

        $this->assertSame(0, PlayHistory::query()->where('user_id', $user->id)->count());
    }

    public function test_admin_pages_require_authentication(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->get('/admin/featured')->assertRedirect('/login');
    }

    public function test_admin_login_only_accepts_admin_accounts(): void
    {
        $admin = $this->admin();
        $regular = User::factory()->create(['password' => 'password']);

        $this->post('/admin/login', [
            'email' => $regular->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();

        $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect('/admin');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_regular_users_cannot_access_admin(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertRedirect('/');
    }

    public function test_admin_can_view_featured_tracks(): void
    {
        FeaturedTrack::create([
            'soundcloud_id' => '12345',
            'title' => 'Contoh Lagu',
            'artist' => 'Contoh Artis',
            'duration' => 215,
            'position' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin())
            ->get('/admin/featured')
            ->assertOk()
            ->assertSee('Contoh Lagu')
            ->assertSee('Pilihan editor');
    }

    public function test_admin_can_toggle_featured_track(): void
    {
        $track = FeaturedTrack::create([
            'soundcloud_id' => '12345',
            'title' => 'Contoh Lagu',
            'artist' => 'Contoh Artis',
            'duration' => 215,
            'position' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin())
            ->patch("/admin/featured/{$track->id}/toggle")
            ->assertRedirect();

        $this->assertFalse($track->fresh()->is_active);
    }

    public function test_admin_can_add_track_from_soundcloud_url(): void
    {
        $this->fakeSoundCloud();

        $this->actingAs($this->admin())
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

        $this->actingAs($this->admin())
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

        $track = FeaturedTrack::create([
            'soundcloud_id' => '12345',
            'title' => 'Contoh Lagu',
            'artist' => 'Contoh Artis',
            'duration' => 215,
            'position' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin())
            ->delete("/admin/featured/{$track->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('featured_tracks', ['id' => $track->id]);
    }
}
