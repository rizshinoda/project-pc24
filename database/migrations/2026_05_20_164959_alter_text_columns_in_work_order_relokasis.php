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
    public function up(): void
    {
        DB::statement("
            ALTER TABLE work_order_relokasis
            MODIFY keterangan TEXT NULL,
            MODIFY non_stock TEXT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE work_order_relokasis 
            MODIFY keterangan VARCHAR(255) NULL,
            MODIFY non_stock VARCHAR(255) NULL
        ");
    }
};
