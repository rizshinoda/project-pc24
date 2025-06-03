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
        Schema::create('berita_acaras', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('work_order_install_id');
            $table->unsignedBigInteger('user_id')->nullable(); // User yang mengirim
            $table->timestamp('tanggal_kirim')->nullable();    // Tanggal kirim berita acara
            $table->timestamp('tanggal_terima')->nullable();   // Tanggal terima berita acara
            $table->string('status')->default('pending');      // Status: pending, sent, received
            $table->timestamps();

            // Foreign key relations
            $table->foreign('work_order_install_id')
                ->references('id')
                ->on('work_order_installs')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berita_acaras');
    }
};
