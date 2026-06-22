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
            $table->string('received_file_path')
                ->nullable()
                ->after('file_path');
        });
    }

    public function down()
    {
        Schema::table('berita_acaras', function (Blueprint $table) {
            $table->dropColumn('received_file_path');
        });
    }
};
