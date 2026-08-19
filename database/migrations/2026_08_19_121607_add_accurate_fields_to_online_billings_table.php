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
        Schema::table('online_billings', function (Blueprint $table) {
            $table->unsignedBigInteger('accurate_customer_id')
                ->nullable()
                ->after('cacti_link');

            $table->string('accurate_customer_no')
                ->nullable()
                ->after('accurate_customer_id');

            $table->unsignedBigInteger('accurate_invoice_id')
                ->nullable()
                ->after('accurate_customer_no');

            $table->string('accurate_invoice_no')
                ->nullable()
                ->after('accurate_invoice_id');

            $table->timestamp('accurate_synced_at')
                ->nullable()
                ->after('accurate_invoice_no');

            $table->text('accurate_sync_error')
                ->nullable()
                ->after('accurate_synced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('online_billings', function (Blueprint $table) {
            $table->dropColumn([
                'accurate_customer_id',
                'accurate_customer_no',
                'accurate_invoice_id',
                'accurate_invoice_no',
                'accurate_synced_at',
                'accurate_sync_error',
            ]);
        });
    }
};
