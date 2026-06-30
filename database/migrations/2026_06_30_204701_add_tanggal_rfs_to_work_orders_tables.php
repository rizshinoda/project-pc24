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
        Schema::table('work_order_upgrades', function (Blueprint $table) {
            $table->date('tanggal_rfs')->nullable();
        });

        Schema::table('work_order_downgrades', function (Blueprint $table) {
            $table->date('tanggal_rfs')->nullable();
        });

        Schema::table('work_order_relokasis', function (Blueprint $table) {
            $table->date('tanggal_rfs')->nullable();
        });

        Schema::table('work_order_dismantles', function (Blueprint $table) {
            $table->date('tanggal_rfs')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_order_upgrades', function (Blueprint $table) {
            $table->dropColumn('tanggal_rfs');
        });

        Schema::table('work_order_downgrades', function (Blueprint $table) {
            $table->dropColumn('tanggal_rfs');
        });

        Schema::table('work_order_relokasis', function (Blueprint $table) {
            $table->dropColumn('tanggal_rfs');
        });

        Schema::table('work_order_dismantles', function (Blueprint $table) {
            $table->dropColumn('tanggal_rfs');
        });
    }
};
