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
        Schema::create('dismantle_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('dismantle_id')
                ->constrained('work_order_dismantles')
                ->cascadeOnDelete();

            $table->foreignId('jenis_id')->constrained('jenis');
            $table->foreignId('merek_id')->constrained('mereks');
            $table->foreignId('tipe_id')->constrained('tipes');

            $table->enum('kualitas', ['baru', 'bekas']);
            $table->string('serial_number')->nullable();

            $table->integer('jumlah')->default(1); // Kolom jumlah default 1

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dismantle_details');
    }
};
