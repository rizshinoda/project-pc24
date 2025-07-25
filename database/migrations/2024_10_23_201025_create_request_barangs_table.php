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
        Schema::create('request_barangs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('online_billing_id')->nullable();
            $table->string('subject_manual')->nullable();
            $table->string('nama_penerima');
            $table->string('alamat_penerima');
            $table->string('no_penerima');
            $table->text('keterangan')->nullable();
            $table->string('non_stock')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'shipped', 'completed'])->default('pending');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // User yang request
            $table->foreign('online_billing_id')->references('id')->on('online_billings')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_barangs');
    }
};
