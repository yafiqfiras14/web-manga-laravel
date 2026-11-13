<?php

namespace App\Models;

use App\Models\Concerns\HasTrackMetadata;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayHistory extends Model
{
    use HasTrackMetadata;

    protected $fillable = [
        'user_id',
        'soundcloud_id',
        'title',
        'artist',
        'artist_id',
        'artwork_url',
        'permalink_url',
        'genre',
        'duration',
        'is_preview',
        'played_at',
    ];

    protected $casts = [
        'duration' => 'integer',
        'is_preview' => 'boolean',
        'played_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
