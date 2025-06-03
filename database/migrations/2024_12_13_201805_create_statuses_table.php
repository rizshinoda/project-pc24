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
        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('work_orderable_id'); // ID work order (polymorphic)
            $table->string('work_orderable_type'); // Tipe work order: Upgrade atau Downgrade

            $table->unsignedBigInteger('online_billing_id'); // ID pelanggan terkait
            $table->unsignedBigInteger('admin_id'); // Admin yang melakukan perubahan status

            $table->string('process'); // Jenis proses, misal: Upgrade, Downgrade, etc.
            $table->enum('status', ['Pending', 'On Progress', 'Shipped', 'Rejected', 'Completed', 'Canceled'])->default('Pending');

            $table->timestamps();

            // Relasi ke tabel online_billings
            $table->foreign('online_billing_id')->references('id')->on('online_billings')->onDelete('cascade');
            // Relasi ke tabel users
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statuses');
    }
};
