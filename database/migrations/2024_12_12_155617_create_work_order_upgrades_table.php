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
        Schema::create('work_order_upgrades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('online_billing_id');
            $table->string('no_spk')->unique();
            $table->string('bandwidth_baru');
            $table->string('satuan');
            $table->unsignedBigInteger('admin_id'); // Admin yang membuat work order
            $table->enum('status', ['Pending', 'On Progress', 'Completed', 'Canceled'])->default('Pending');
            $table->timestamps();
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('online_billing_id')->references('id')->on('online_billings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_order_upgrades');
    }
};
