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
        Schema::table('stock_barangs', function (Blueprint $table) {
            // 1. Drop foreign key
            if (Schema::hasColumn('stock_barangs', 'dismantle_id')) {
                $table->dropForeign(['dismantle_id']);
                $table->dropColumn('dismantle_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_barangs', function (Blueprint $table) {
            $table->unsignedBigInteger('dismantle_id')->nullable();

            $table->foreign('dismantle_id')
                ->references('id')
                ->on('work_order_dismantles')
                ->onDelete('cascade');
        });
    }
};
