<?php

namespace App\Models;

use App\Models\Concerns\HasTrackMetadata;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaylistTrack extends Model
{
    use HasTrackMetadata;

    protected $fillable = [
        'playlist_id',
        'soundcloud_id',
        'title',
        'artist',
        'artist_id',
        'artwork_url',
        'permalink_url',
        'genre',
        'duration',
        'is_preview',
        'position',
    ];

    protected $casts = [
        'duration' => 'integer',
        'is_preview' => 'boolean',
        'position' => 'integer',
    ];

    public function playlist(): BelongsTo
    {
        return $this->belongsTo(Playlist::class);
    }
}
