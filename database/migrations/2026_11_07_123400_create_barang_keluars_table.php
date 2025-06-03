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
        Schema::create('barang_keluars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_barang_id')->nullable()->constrained('request_barangs')->onDelete('cascade'); // Relasi ke tabel request_barang
            $table->foreignId('work_order_install_id')->nullable()->constrained('work_order_installs')->onDelete('cascade'); // Relasi ke tabel work order (instalasi)
            $table->foreignId('stock_barang_id')->constrained('stock_barangs')->onDelete('cascade'); // Relasi ke tabel stock_barangs
            $table->foreignId('work_order_relokasi_id')->nullable()->constrained('work_order_relokasis')->onDelete('cascade'); // Relasi ke tabel work order (instalasi)
            $table->foreignId('work_order_maintenance_id')->nullable()->constrained('work_order_maintenances')->onDelete('cascade'); // Relasi ke tabel work order (instalasi)

            $table->integer('jumlah'); // Jumlah barang yang dikirim
            $table->string('serial_number')->nullable(); // Serial Number barang
            $table->string('kualitas'); // Kualitas barang (baru/bekas)
            $table->unsignedBigInteger('user_id'); // Admin yang membuat work order
            $table->boolean('is_configured')->default(false);


            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_keluars');
    }
};
