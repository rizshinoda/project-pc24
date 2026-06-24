<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('berita_acaras', function (Blueprint $table) {
            $table->unsignedBigInteger('work_order_install_id')
                ->nullable()
                ->change();
        });
    }

    public function down()
    {
        Schema::table('berita_acaras', function (Blueprint $table) {
            $table->unsignedBigInteger('work_order_install_id')
                ->nullable(false)
                ->change();
        });
    }
};
