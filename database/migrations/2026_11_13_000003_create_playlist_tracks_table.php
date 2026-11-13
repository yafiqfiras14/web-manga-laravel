<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playlist_tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('playlist_id')->constrained()->cascadeOnDelete();
            $table->string('soundcloud_id', 64);
            $table->string('title');
            $table->string('artist')->nullable();
            $table->string('artist_id', 64)->nullable();
            $table->text('artwork_url')->nullable();
            $table->text('permalink_url')->nullable();
            $table->string('genre')->nullable();
            $table->unsignedInteger('duration')->default(0);
            $table->boolean('is_preview')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['playlist_id', 'soundcloud_id']);
            $table->index(['playlist_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('playlist_tracks');
    }
};
