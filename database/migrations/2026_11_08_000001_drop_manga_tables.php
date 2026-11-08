<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('manga_pages');
        Schema::dropIfExists('chapters');
        Schema::dropIfExists('manga');
    }

    public function down(): void
    {
        // Tabel lama tidak dibuat ulang: aplikasi ini sudah bukan situs manga.
    }
};
