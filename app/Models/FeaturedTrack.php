<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FeaturedTrack extends Model
{
    protected $fillable = [
        'soundcloud_id',
        'title',
        'artist',
        'artist_id',
        'artwork_url',
        'permalink_url',
        'genre',
        'duration',
        'position',
        'is_active',
    ];

    protected $casts = [
        'duration' => 'integer',
        'position' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('id');
    }

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
            'is_preview' => false,
        ];
    }
}
