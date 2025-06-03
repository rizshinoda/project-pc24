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
        Schema::create('ganti_vendor_progress_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ganti_vendor_progress_id')->constrained('ganti_vendor_progress')->onDelete('cascade');
            $table->string('file_path'); // Path foto
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ganti_vendor_progress_photos');
    }
};
