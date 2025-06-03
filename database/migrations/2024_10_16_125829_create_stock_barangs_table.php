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
        // Tabel stock_barangs
        Schema::create('stock_barangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_id')->constrained('jenis')->onDelete('cascade');
            $table->foreignId('merek_id')->constrained('mereks')->onDelete('cascade');
            $table->foreignId('tipe_id')->constrained('tipes')->onDelete('cascade');
            $table->string('serial_number')->nullable();
            $table->integer('jumlah')->default(1); // Kolom jumlah default 1
            $table->unsignedBigInteger('dismantle_id')->nullable(); // Relasi ke work order dismantle

            $table->enum('kualitas', ['baru', 'bekas']);
            $table->timestamps();
            $table->foreign('dismantle_id')->references('id')->on('work_order_dismantles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_barangs');
    }
};
