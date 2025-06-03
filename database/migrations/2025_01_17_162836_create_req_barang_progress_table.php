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
        Schema::create('req_barang_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('req_barang_id');
            $table->text('keterangan');
            $table->timestamps();
            $table->string('status')->default('On Progress'); // Status default
            $table->unsignedBigInteger('user_id'); //PSB yang membuat work order


            $table->foreign('req_barang_id')->references('id')->on('request_barangs')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('req_barang_progress');
    }
};
