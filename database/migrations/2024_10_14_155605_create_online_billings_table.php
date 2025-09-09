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
        Schema::create('online_billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_install_id')->nullable()->constrained('work_order_installs')->nullOnDelete();
            $table->foreignId('pelanggan_id')->nullable()->constrained('pelanggans')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->foreignId('instansi_id')->nullable()->constrained('instansis')->nullOnDelete();
            $table->string('nama_site')->nullable();
            $table->string('alamat_pemasangan')->nullable();
            $table->string('nama_pic')->nullable();
            $table->string('no_pic')->nullable();
            $table->string('layanan')->nullable();
            $table->string('media')->nullable();
            $table->string('bandwidth')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('satuan')->nullable();
            $table->string('nni')->nullable();
            $table->string('vlan')->nullable();
            $table->string('no_jaringan')->nullable();
            $table->date('tanggal_instalasi')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_akhir')->nullable();
            $table->unsignedBigInteger('admin_id'); // Admin yang membuat work order
            $table->integer('durasi')->nullable(); // Durasi dalam satuan waktu, misalnya bulan
            $table->string('nama_durasi')->nullable(); // Deskripsi durasi, misalnya "3 Bulan"
            $table->unsignedInteger('harga_sewa')->nullable(); // Harga sewa dalam integer
            $table->string('sid_vendor')->nullable(); // Deskripsi durasi, misalnya "3 Bulan"
            $table->timestamps();
            $table->enum('status', ['active', 'dismantle'])->default('active');
            $table->string('cacti_link')->nullable();
            // Relasi ke tabel users (admin dan psb)
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_billings');
    }
};
