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
            $table->string('non_stock')
                ->nullable();
            $table->string('keterangan')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_order_upgrades', function (Blueprint $table) {
            $table->dropColumn('non_stock');
            $table->dropColumn('keterangan');
        });
    }
};
