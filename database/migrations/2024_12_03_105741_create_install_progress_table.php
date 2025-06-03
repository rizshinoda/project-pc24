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
        Schema::create('install_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('work_order_install_id');
            $table->text('keterangan');
            $table->timestamps();
            $table->string('status')->default('On Progress'); // Status default
            $table->unsignedBigInteger('user_id'); //PSB yang membuat work order


            $table->foreign('work_order_install_id')->references('id')->on('work_order_installs')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('install_progress');
    }
};
