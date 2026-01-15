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
        // Mapping data lama agar sesuai ENUM final
        DB::statement("
            UPDATE work_order_upgrades
            SET status = CASE status
                WHEN 'pending' THEN 'Pending'
                WHEN 'approved' THEN 'On Progress'
                WHEN 'rejected' THEN 'Rejected'
                WHEN 'shipped' THEN 'Shipped'
                WHEN 'completed' THEN 'Completed'
                ELSE status
            END
        ");

        // Ubah ENUM ke versi final
        DB::statement("
            ALTER TABLE work_order_upgrades
            MODIFY status ENUM(
                'Pending',
                'On Progress',
                'Completed',
                'Shipped',
                'Rejected',
                'Canceled'
            ) DEFAULT 'Pending'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // rollback ke ENUM lama
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

        // rollback data lama
        DB::statement("
            UPDATE work_order_upgrades
            SET status = CASE status
                WHEN 'Pending' THEN 'pending'
                WHEN 'On Progress' THEN 'approved'
                WHEN 'Rejected' THEN 'rejected'
                WHEN 'Shipped' THEN 'shipped'
                WHEN 'Completed' THEN 'completed'
                ELSE status
            END
        ");
    }
};
