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
        Schema::create('work_order_installs', function (Blueprint $table) {
            $table->id();
            $table->string('no_spk')->unique();
            $table->unsignedBigInteger('survey_id')->nullable(); // opsional
            $table->foreignId('pelanggan_id')->constrained('pelanggans')->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->foreignId('instansi_id')->constrained('instansis')->onDelete('cascade');
            $table->string('nama_site');
            $table->string('alamat_pemasangan');
            $table->string('nama_pic')->nullable();
            $table->string('no_pic')->nullable();
            $table->string('layanan');
            $table->string('media');
            $table->string('bandwidth');
            $table->string('provinsi');
            $table->string('satuan');
            $table->string('nni')->nullable();
            $table->string('vlan')->nullable();
            $table->string('no_jaringan')->nullable();
            $table->date('tanggal_instalasi')->nullable();
            $table->date('tanggal_rfs');
            $table->unsignedBigInteger('admin_id'); // Admin yang membuat work order
            $table->enum('status', ['Pending', 'On Progress', 'Completed', 'Shipped', 'Rejected', 'Canceled'])->default('Pending');
            // Tambahan kolom
            $table->integer('durasi')->nullable(); // Durasi dalam satuan waktu, misalnya bulan
            $table->string('nama_durasi')->nullable(); // Deskripsi durasi, misalnya "3 Bulan"
            $table->unsignedInteger('harga_sewa')->nullable(); // Harga sewa dalam integer
            $table->unsignedInteger('harga_instalasi')->nullable(); // Harga instalasi dalam integer
            $table->string('keterangan')->nullable();
            $table->timestamps();

            // Relasi ke tabel users (admin dan psb)
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('survey_id')->references('id')->on('work_order_surveys')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_order_installs');
    }
};
