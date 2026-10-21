<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Manga extends Model
{
    protected $table = 'manga';

    protected $fillable = ['title', 'slug', 'author', 'synopsis', 'status', 'cover'];

    protected static function booted(): void
    {
        static::saving(function (Manga $manga) {
            $base = Str::slug($manga->title);
            $slug = $base;
            $i = 1;

            while (static::where('slug', $slug)->where('id', '!=', $manga->id)->exists()) {
                $slug = $base.'-'.$i++;
            }

            $manga->slug = $slug;
        });
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderByDesc('number');
    }

    public function latestChapter(): ?Chapter
    {
        return $this->chapters()->orderByDesc('number')->first();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'ongoing' => 'Ongoing',
            'completed' => 'Completed',
            'hiatus' => 'Hiatus',
            default => $this->status,
        };
    }
}
