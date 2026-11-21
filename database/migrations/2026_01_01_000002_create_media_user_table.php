<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot table many-to-many: koleksi media milik pengguna.
     */
    public function up(): void
    {
        Schema::create('media_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->timestamps(); // created_at = kapan media ditambahkan ke koleksi

            // Satu media hanya boleh masuk satu kali ke koleksi pengguna yang sama.
            $table->unique(['user_id', 'media_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_user');
    }
};
