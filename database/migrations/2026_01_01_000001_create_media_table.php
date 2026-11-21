<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();          // judul tidak boleh duplikat
            $table->string('type', 10)->index();        // 'anime' | 'manga'
            $table->string('genre', 100)->index();
            $table->unsignedSmallInteger('release_year');
            $table->unsignedInteger('episodes')->nullable(); // khusus anime
            $table->unsignedInteger('chapters')->nullable(); // khusus manga
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
