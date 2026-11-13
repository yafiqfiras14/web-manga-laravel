<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('play_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('soundcloud_id', 64);
            $table->string('title');
            $table->string('artist')->nullable();
            $table->string('artist_id', 64)->nullable();
            $table->text('artwork_url')->nullable();
            $table->text('permalink_url')->nullable();
            $table->string('genre')->nullable();
            $table->unsignedInteger('duration')->default(0);
            $table->boolean('is_preview')->default(false);
            $table->timestamp('played_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'soundcloud_id']);
            $table->index(['user_id', 'played_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('play_histories');
    }
};
