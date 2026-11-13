<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Playlist extends Model
{
    protected $fillable = [
        'name',
        'description',
        'cover_url',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tracks(): HasMany
    {
        return $this->hasMany(PlaylistTrack::class)->orderBy('position')->orderBy('id');
    }

    /**
     * @return array<string, mixed>
     */
    public function toSummary(): array
    {
        $count = $this->tracks_count ?? $this->tracks()->count();
        $cover = $this->cover_url ?: $this->tracks()->whereNotNull('artwork_url')->value('artwork_url');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'cover_url' => $cover,
            'track_count' => (int) $count,
        ];
    }
}
