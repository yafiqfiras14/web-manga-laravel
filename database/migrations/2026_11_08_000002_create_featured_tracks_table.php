<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('featured_tracks', function (Blueprint $table) {
            $table->id();
            $table->string('soundcloud_id')->unique();
            $table->string('title');
            $table->string('artist')->nullable();
            $table->string('artist_id')->nullable();
            $table->string('artwork_url', 2048)->nullable();
            $table->string('permalink_url', 2048)->nullable();
            $table->string('genre')->nullable();
            $table->unsignedInteger('duration')->default(0);
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('featured_tracks');
    }
};
