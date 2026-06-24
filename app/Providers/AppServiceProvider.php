<?php

namespace App\Providers;

use App\Models\WorkOrderDismantle;
use App\Models\WorkOrderDowngrade;
use App\Models\WorkOrderInstall;
use App\Models\WorkOrderRelokasi;
use App\Models\WorkOrderSurvey;
use App\Models\WorkOrderUpgrade;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set locale Carbon ke Indonesia
        Carbon::setLocale('id');
        Paginator::useBootstrap();
        Relation::morphMap([
            'install' => WorkOrderInstall::class,
            'survey' => WorkOrderSurvey::class,
            'upgrade' => WorkOrderUpgrade::class,
            'downgrade' => WorkOrderDowngrade::class,
            'dismantle' => WorkOrderDismantle::class,
            'relokasi' => WorkOrderRelokasi::class,
        ]);
    }
}
