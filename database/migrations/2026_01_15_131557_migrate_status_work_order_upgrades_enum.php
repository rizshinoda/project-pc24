<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1️⃣ Update data lama ke format baru
        DB::statement("
            UPDATE work_order_upgrades
            SET status = CASE status
                WHEN 'Pending' THEN 'pending'
                WHEN 'On Progress' THEN 'approved'
                WHEN 'Completed' THEN 'completed'
                WHEN 'Canceled' THEN 'rejected'
                ELSE status
            END
        ");

        // 2️⃣ Ubah ENUM
        DB::statement("
            ALTER TABLE work_order_upgrades
            MODIFY status ENUM(
                'pending',
                'approved',
                'rejected',
                'shipped',
                'completed'
            ) DEFAULT 'pending'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // rollback enum ke versi lama
        DB::statement("
            ALTER TABLE work_order_upgrades
            MODIFY status ENUM(
                'Pending',
                'On Progress',
                'Completed',
                'Canceled'
            ) DEFAULT 'Pending'
        ");
        // rollback data
        DB::statement("
            UPDATE work_order_upgrades
            SET status = CASE status
                WHEN 'pending' THEN 'Pending'
                WHEN 'approved' THEN 'On Progress'
                WHEN 'completed' THEN 'Completed'
                WHEN 'rejected' THEN 'Canceled'
                ELSE status
            END
        ");
    }
};
