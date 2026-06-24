<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // 1. Add new polymorphic columns
        Schema::table('berita_acaras', function (Blueprint $table) {
            $table->unsignedBigInteger('work_order_id')
                ->nullable()
                ->after('work_order_install_id');

            $table->string('work_order_type')
                ->nullable()
                ->after('work_order_id');

            $table->string('file_path')
                ->nullable()
                ->after('work_order_type');
        });

        // 2. Copy existing install data
        DB::table('berita_acaras')
            ->whereNotNull('work_order_install_id')
            ->update([
                'work_order_id' => DB::raw('work_order_install_id'),
                'work_order_type' => 'install'
            ]);
    }

    public function down()
    {
        Schema::table('berita_acaras', function (Blueprint $table) {
            $table->dropColumn([
                'work_order_id',
                'work_order_type',
                'file_path'
            ]);
        });
    }
};
