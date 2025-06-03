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
        Schema::create('request_barang_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_barang_id')->constrained('request_barangs')->onDelete('cascade');
            $table->foreignId('stock_barang_id')->constrained('stock_barangs')->onDelete('cascade');
            $table->string('serial_number')->nullable();  // Serial number diinput oleh divisi General Affair
            $table->integer('jumlah')->default(1);
            $table->string('merek');
            $table->string('tipe');
            $table->string('kualitas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_barang_details');
    }
};
