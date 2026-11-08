<?php

namespace App\Services;

use App\Exceptions\SoundCloudException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Client kecil untuk API v2 SoundCloud.
 *
 * SoundCloud tidak lagi membagikan client_id publik lewat dokumentasi, jadi
 * service ini bisa menemukannya otomatis dari bundle JavaScript soundcloud.com
 * (hasilnya di-cache). Kalau kamu punya client_id resmi, isi SOUNDCLOUD_CLIENT_ID
 * di .env dan nilai itu yang akan dipakai.
 */
class SoundCloudService
{
    private const API = 'https://api-v2.soundcloud.com';

    private const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) '
        .'AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Safari/537.36';

    private ?string $clientId;

    public function __construct()
    {
        $configured = trim((string) config('services.soundcloud.client_id'));

        $this->clientId = $configured !== '' ? $configured : null;
    }

    /* --------------------------------------------------------------------- */
    /* Client ID                                                              */
    /* --------------------------------------------------------------------- */

    public function clientId(): string
    {
        if ($this->clientId !== null) {
            return $this->clientId;
        }

        $this->clientId = Cache::remember(
            'soundcloud:client_id',
            now()->addHours(6),
            fn (): string => $this->discoverClientId()
        );

        return $this->clientId;
    }

    private function discoverClientId(): string
    {
        try {
            $html = $this->http()->get('https://soundcloud.com')->throw()->body();
        } catch (\Throwable $e) {
            throw new SoundCloudException(
                'Tidak bisa membuka soundcloud.com. Periksa koneksi internet atau isi SOUNDCLOUD_CLIENT_ID di .env.',
                0,
                $e
            );
        }

        preg_match_all('#src="(https://a-v2\.sndcdn\.com/assets/[^"]+\.js)"#', $html, $matches);
        $scripts = array_values(array_unique($matches[1] ?? []));

        if ($scripts === []) {
            throw new SoundCloudException('Bundle JavaScript SoundCloud tidak ditemukan.');
        }

        foreach (array_slice($scripts, 0, 20) as $script) {
            try {
                $js = $this->http()->get($script)->body();
            } catch (\Throwable) {
                continue;
            }

            if (preg_match('/client_id\s*[:=]\s*"([A-Za-z0-9]{32})"/', $js, $match)) {
                return $match[1];
            }
        }

        throw new SoundCloudException(
            'Client ID SoundCloud tidak ditemukan otomatis. Isi SOUNDCLOUD_CLIENT_ID di .env.'
        );
    }

    /* --------------------------------------------------------------------- */
    /* Pencarian & chart                                                      */
    /* --------------------------------------------------------------------- */

    /** @return array<int, array<string, mixed>> */
    public function searchTracks(string $query, int $limit = 24): array
    {
        $query = trim($query);

        if ($query === '') {
            return [];
        }

        return $this->cached('search:tracks:'.md5($query.'|'.$limit), 300, function () use ($query, $limit) {
            $data = $this->api('/search/tracks', [
                'q' => $query,
                'limit' => $limit,
                'linked_partitioning' => 1,
            ]);

            return $this->normalizeTracks($data['collection'] ?? []);
        });
    }

    /** @return array<int, array<string, mixed>> */
    public function searchUsers(string $query, int $limit = 6): array
    {
        $query = trim($query);

        if ($query === '') {
            return [];
        }

        return $this->cached('search:users:'.md5($query.'|'.$limit), 300, function () use ($query, $limit) {
            $data = $this->api('/search/users', [
                'q' => $query,
                'limit' => $limit,
            ]);

            return array_map(
                fn (array $user): array => $this->normalizeUser($user),
                $data['collection'] ?? []
            );
        });
    }

    /** @return array<int, array<string, mixed>> */
    public function searchPlaylists(string $query, int $limit = 6): array
    {
        $query = trim($query);

        if ($query === '') {
            return [];
        }

        return $this->cached('search:playlists:'.md5($query.'|'.$limit), 300, function () use ($query, $limit) {
            $data = $this->api('/search/playlists', [
                'q' => $query,
                'limit' => $limit,
            ]);

            return array_map(
                fn (array $playlist): array => $this->normalizePlaylist($playlist),
                $data['collection'] ?? []
            );
        });
    }

    /**
     * Chart SoundCloud untuk sebuah genre. Chart per-genre sering kosong,
     * jadi otomatis fallback ke pencarian.
     *
     * @return array<int, array<string, mixed>>
     */
    public function chart(string $genreSlug = 'all-music', int $limit = 12): array
    {
        $genre = config("music.genres.{$genreSlug}") ?? config('music.genres.all-music');
        $genreSlug = config("music.genres.{$genreSlug}") ? $genreSlug : 'all-music';

        return $this->cached('chart:'.$genreSlug.':'.$limit, 900, function () use ($genre, $limit) {
            $tracks = [];

            try {
                $data = $this->api('/charts', [
                    'kind' => 'trending',
                    'genre' => $genre['urn'],
                    'limit' => $limit,
                ]);

                $tracks = $this->normalizeTracks(
                    array_values(array_filter(array_map(
                        fn (array $item): ?array => $item['track'] ?? null,
                        $data['collection'] ?? []
                    )))
                );
            } catch (SoundCloudException) {
                $tracks = [];
            }

            if ($tracks === []) {
                $tracks = $this->searchTracks($genre['query'] ?? 'music', $limit);
            }

            return $tracks;
        });
    }

    /* --------------------------------------------------------------------- */
    /* Track, artis, playlist                                                 */
    /* --------------------------------------------------------------------- */

    /** @return array<string, mixed>|null */
    public function track(string|int $id): ?array
    {
        $id = $this->cleanId($id);

        if ($id === null) {
            return null;
        }

        return $this->cached('track:'.$id, 600, function () use ($id) {
            try {
                return $this->normalizeTrack($this->rawTrack($id));
            } catch (SoundCloudException) {
                return null;
            }
        });
    }

    /** @return array<int, array<string, mixed>> */
    public function relatedTracks(string|int $id, int $limit = 8): array
    {
        $id = $this->cleanId($id);

        if ($id === null) {
            return [];
        }

        return $this->cached('related:'.$id.':'.$limit, 600, function () use ($id, $limit) {
            try {
                $data = $this->api("/tracks/{$id}/related", ['limit' => $limit]);

                return $this->normalizeTracks($data['collection'] ?? []);
            } catch (SoundCloudException) {
                return [];
            }
        });
    }

    /** @return array<string, mixed>|null */
    public function user(string|int $id): ?array
    {
        $id = $this->cleanId($id);

        if ($id === null) {
            return null;
        }

        return $this->cached('user:'.$id, 900, function () use ($id) {
            try {
                return $this->normalizeUser($this->api("/users/{$id}"));
            } catch (SoundCloudException) {
                return null;
            }
        });
    }

    /** @return array<int, array<string, mixed>> */
    public function userTracks(string|int $id, int $limit = 24): array
    {
        $id = $this->cleanId($id);

        if ($id === null) {
            return [];
        }

        return $this->cached('user-tracks:'.$id.':'.$limit, 600, function () use ($id, $limit) {
            try {
                $data = $this->api("/users/{$id}/tracks", [
                    'limit' => $limit,
                    'linked_partitioning' => 1,
                ]);

                return $this->normalizeTracks($data['collection'] ?? []);
            } catch (SoundCloudException) {
                return [];
            }
        });
    }

    /** @return array<string, mixed>|null */
    public function playlist(string|int $id): ?array
    {
        $id = $this->cleanId($id);

        if ($id === null) {
            return null;
        }

        return $this->cached('playlist:'.$id, 600, function () use ($id) {
            try {
                return $this->normalizePlaylist($this->api("/playlists/{$id}"));
            } catch (SoundCloudException) {
                return null;
            }
        });
    }

    /**
     * Resolve URL SoundCloud (track / playlist / user) menjadi data mentah.
     *
     * @return array<string, mixed>|null
     */
    public function resolve(string $url): ?array
    {
        $url = trim($url);

        if ($url === '' || ! preg_match('#^https?://(www\.)?soundcloud\.com/#i', $url)) {
            return null;
        }

        try {
            return $this->api('/resolve', ['url' => $url]);
        } catch (SoundCloudException) {
            return null;
        }
    }

    /* --------------------------------------------------------------------- */
    /* Streaming                                                              */
    /* --------------------------------------------------------------------- */

    /**
     * Cari URL audio untuk sebuah track.
     *
     * @return array{type: string, url: string, is_preview: bool}
     */
    public function stream(string|int $id): array
    {
        $id = $this->cleanId($id);

        if ($id === null) {
            throw new SoundCloudException('ID track tidak valid.');
        }

        return $this->cached('stream:'.$id, 180, function () use ($id) {
            $track = $this->rawTrack($id);

            if (empty($track['streamable'])) {
                throw new SoundCloudException('Track ini tidak bisa diputar.');
            }

            $authorization = $track['track_authorization'] ?? null;
            $transcodings = $track['media']['transcodings'] ?? [];

            $progressive = collect($transcodings)->first(
                fn (array $transcoding): bool => ($transcoding['format']['protocol'] ?? '') === 'progressive'
            );

            if ($progressive) {
                $url = $this->resolveTranscoding($progressive['url'], $authorization);

                return [
                    'type' => 'progressive',
                    'url' => $url,
                    'is_preview' => ($track['policy'] ?? null) === 'SNIP'
                        || str_contains((string) $progressive['url'], '/preview/'),
                ];
            }

            $hls = collect($transcodings)->first(
                fn (array $transcoding): bool => ($transcoding['format']['protocol'] ?? '') === 'hls'
            );

            if ($hls) {
                $url = $this->resolveTranscoding($hls['url'], $authorization);

                return [
                    'type' => 'hls',
                    'url' => $url,
                    'is_preview' => ($track['policy'] ?? null) === 'SNIP',
                ];
            }

            throw new SoundCloudException('Format audio track ini tidak didukung.');
        });
    }

    /**
     * Ambil playlist HLS dan tulis ulang URL segmennya agar lewat proxy kita.
     */
    public function hlsPlaylist(string|int $id): string
    {
        $id = $this->cleanId($id);

        if ($id === null) {
            throw new SoundCloudException('ID track tidak valid.');
        }

        return $this->cached('hls:'.$id, 120, function () use ($id) {
            $stream = $this->stream($id);

            if ($stream['type'] !== 'hls') {
                throw new SoundCloudException('Track ini tidak memakai HLS.');
            }

            $response = $this->http()->get($stream['url']);

            if ($response->failed()) {
                throw new SoundCloudException('Gagal mengambil playlist HLS SoundCloud.');
            }

            return $this->rewritePlaylist($response->body(), $stream['url']);
        });
    }

    public function isAllowedMediaUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return false;
        }

        foreach (['sndcdn.com', 'soundcloud.cloud', 'soundcloud.com'] as $domain) {
            if ($host === $domain || str_ends_with($host, '.'.$domain)) {
                return true;
            }
        }

        return false;
    }

    private function rewritePlaylist(string $playlist, string $playlistUrl): string
    {
        $base = rtrim(dirname($playlistUrl), '/');

        $rewrite = function (string $uri) use ($base): string {
            $absolute = preg_match('#^https?://#i', $uri)
                ? $uri
                : $base.'/'.ltrim($uri, '/');

            return route('api.segment', ['u' => self::base64UrlEncode($absolute)], false);
        };

        $playlist = preg_replace_callback(
            '/URI="([^"]+)"/i',
            fn (array $match): string => 'URI="'.$rewrite($match[1]).'"',
            $playlist
        );

        $lines = explode("\n", (string) $playlist);

        foreach ($lines as $index => $line) {
            $trimmed = trim($line);

            if ($trimmed !== '' && ! str_starts_with($trimmed, '#')) {
                $lines[$index] = $rewrite($trimmed);
            }
        }

        return implode("\n", $lines);
    }

    private function resolveTranscoding(string $url, ?string $authorization): string
    {
        $query = ['client_id' => $this->clientId()];

        if (is_string($authorization) && $authorization !== '') {
            $query['track_authorization'] = $authorization;
        }

        $response = $this->http()->get($url, $query);

        if ($response->failed()) {
            throw new SoundCloudException('Gagal mengambil URL streaming dari SoundCloud.');
        }

        $resolved = $response->json('url');

        if (! is_string($resolved) || $resolved === '') {
            throw new SoundCloudException('SoundCloud tidak mengembalikan URL streaming.');
        }

        return $resolved;
    }

    /* --------------------------------------------------------------------- */
    /* Helper                                                                 */
    /* --------------------------------------------------------------------- */

    /** @return array<string, mixed> */
    private function rawTrack(string|int $id): array
    {
        return $this->cached('raw-track:'.$id, 600, fn (): array => $this->api('/tracks/'.$id));
    }

    /**
     * @param  array<int, array<string, mixed>>  $tracks
     * @return array<int, array<string, mixed>>
     */
    private function normalizeTracks(array $tracks): array
    {
        return array_values(array_map(
            fn (array $track): array => $this->normalizeTrack($track),
            array_filter($tracks, 'is_array')
        ));
    }

    /** @return array<string, mixed> */
    public function normalizeTrack(array $track): array
    {
        $user = is_array($track['user'] ?? null) ? $track['user'] : [];
        $artwork = $track['artwork_url'] ?? $user['avatar_url'] ?? null;

        return [
            'id' => (string) ($track['id'] ?? ''),
            'title' => (string) ($track['title'] ?? 'Untitled'),
            'artist' => (string) ($user['username'] ?? 'Unknown artist'),
            'artist_id' => (string) ($user['id'] ?? ''),
            'artist_avatar' => $this->imageUrl($user['avatar_url'] ?? null),
            'artwork' => $this->imageUrl($artwork),
            'duration' => (int) round(((int) ($track['full_duration'] ?? $track['duration'] ?? 0)) / 1000),
            'genre' => $track['genre'] ?? null,
            'likes' => (int) ($track['likes_count'] ?? 0),
            'plays' => (int) ($track['playback_count'] ?? 0),
            'created_at' => $track['created_at'] ?? null,
            'permalink_url' => $track['permalink_url'] ?? null,
            'streamable' => (bool) ($track['streamable'] ?? false),
            'policy' => $track['policy'] ?? null,
            'description' => trim(strip_tags((string) ($track['description'] ?? ''))),
            'is_preview' => ($track['policy'] ?? null) === 'SNIP',
        ];
    }

    /** @return array<string, mixed> */
    public function normalizeUser(array $user): array
    {
        return [
            'id' => (string) ($user['id'] ?? ''),
            'username' => (string) ($user['username'] ?? 'Unknown'),
            'avatar' => $this->imageUrl($user['avatar_url'] ?? null),
            'followers' => (int) ($user['followers_count'] ?? 0),
            'track_count' => (int) ($user['track_count'] ?? 0),
            'permalink_url' => $user['permalink_url'] ?? null,
            'description' => trim(strip_tags((string) ($user['description'] ?? ''))),
            'city' => $user['city'] ?? null,
            'country_code' => $user['country_code'] ?? null,
        ];
    }

    /** @return array<string, mixed> */
    public function normalizePlaylist(array $playlist): array
    {
        $tracks = $this->normalizeTracks(
            array_values(array_filter(
                $playlist['tracks'] ?? [],
                fn ($track): bool => is_array($track) && ! empty($track['id'])
            ))
        );

        $artwork = $playlist['artwork_url'] ?? ($playlist['tracks'][0]['artwork_url'] ?? null);
        $user = is_array($playlist['user'] ?? null) ? $playlist['user'] : [];

        return [
            'id' => (string) ($playlist['id'] ?? ''),
            'title' => (string) ($playlist['title'] ?? 'Playlist'),
            'curator' => (string) ($user['username'] ?? 'SoundCloud'),
            'curator_id' => (string) ($user['id'] ?? ''),
            'artwork' => $this->imageUrl($artwork),
            'track_count' => (int) ($playlist['track_count'] ?? count($tracks)),
            'duration' => (int) round(((int) ($playlist['duration'] ?? 0)) / 1000),
            'genre' => $playlist['genre'] ?? null,
            'description' => trim(strip_tags((string) ($playlist['description'] ?? ''))),
            'permalink_url' => $playlist['permalink_url'] ?? null,
            'tracks' => $tracks,
        ];
    }

    private function imageUrl(?string $url): ?string
    {
        if (! is_string($url) || $url === '') {
            return null;
        }

        return str_replace('-large.', '-t500x500.', $url);
    }

    private function cleanId(string|int $id): ?string
    {
        $id = preg_replace('/\D/', '', (string) $id);

        return $id === '' ? null : $id;
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    private function api(string $path, array $query = [], bool $retryOnAuth = true): array
    {
        $query['client_id'] = $query['client_id'] ?? $this->clientId();

        $response = $this->http()->get(self::API.$path, $query);

        if (in_array($response->status(), [401, 403], true) && $retryOnAuth) {
            Cache::forget('soundcloud:client_id');
            $this->clientId = null;

            return $this->api($path, $query, false);
        }

        if ($response->failed()) {
            throw new SoundCloudException(
                "SoundCloud API mengembalikan status {$response->status()} untuk {$path}."
            );
        }

        $json = $response->json();

        if (! is_array($json)) {
            throw new SoundCloudException('Respons SoundCloud tidak valid.');
        }

        return $json;
    }

    private function http(): PendingRequest
    {
        return Http::withHeaders([
            'User-Agent' => self::USER_AGENT,
            'Accept' => 'application/json',
            'Accept-Language' => 'en-US,en;q=0.9',
        ])->timeout(15);
    }

    private function cached(string $key, int $seconds, callable $callback): mixed
    {
        return Cache::remember('soundcloud:'.$key, now()->addSeconds($seconds), $callback);
    }

    public static function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    public static function base64UrlDecode(string $value): ?string
    {
        $value = strtr($value, '-_', '+/');
        $padding = strlen($value) % 4;

        if ($padding > 0) {
            $value .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($value, true);

        return $decoded === false ? null : $decoded;
    }
}
