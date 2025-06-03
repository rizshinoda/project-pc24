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
        Schema::create('dismantle_progress_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dismantle_progress_id')->constrained('dismantle_progress')->onDelete('cascade');
            $table->string('file_path'); // Path foto
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dismantle_progress_photos');
    }
};
