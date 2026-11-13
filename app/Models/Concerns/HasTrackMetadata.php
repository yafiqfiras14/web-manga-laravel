<?php

namespace App\Models\Concerns;

trait HasTrackMetadata
{
    /**
     * Bentuk data yang sama dengan SoundCloudService::normalizeTrack().
     *
     * @return array<string, mixed>
     */
    public function toTrack(): array
    {
        return [
            'id' => (string) $this->soundcloud_id,
            'title' => $this->title,
            'artist' => $this->artist ?: 'Unknown artist',
            'artist_id' => (string) ($this->artist_id ?: ''),
            'artist_avatar' => null,
            'artwork' => $this->artwork_url,
            'duration' => (int) $this->duration,
            'genre' => $this->genre,
            'likes' => 0,
            'plays' => 0,
            'created_at' => $this->created_at?->toIso8601String(),
            'permalink_url' => $this->permalink_url,
            'streamable' => true,
            'policy' => null,
            'description' => '',
            'is_preview' => (bool) $this->is_preview,
        ];
    }

    /**
     * Ambil kolom penyimpanan dari payload track yang dikirim browser.
     *
     * @param  array<string, mixed>  $track
     * @return array<string, mixed>
     */
    public static function metadataFromTrack(array $track): array
    {
        return [
            'soundcloud_id' => (string) ($track['id'] ?? ''),
            'title' => (string) ($track['title'] ?? 'Tanpa judul'),
            'artist' => $track['artist'] ?? null,
            'artist_id' => isset($track['artist_id']) && $track['artist_id'] !== '' ? (string) $track['artist_id'] : null,
            'artwork_url' => $track['artwork'] ?? $track['artwork_url'] ?? null,
            'permalink_url' => $track['permalink_url'] ?? null,
            'genre' => $track['genre'] ?? null,
            'duration' => (int) ($track['duration'] ?? 0),
            'is_preview' => (bool) ($track['is_preview'] ?? false),
        ];
    }
}
