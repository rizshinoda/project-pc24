<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('install_progress_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('install_progress_id')->constrained('install_progress')->onDelete('cascade');
            $table->string('file_path'); // Path foto
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('install_progress_photos');
    }
};
