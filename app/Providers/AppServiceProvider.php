<?php

namespace App\Providers;

use App\Policies\NotificationPolicy;
use App\Policies\ReportPolicy;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Gate;
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
        Gate::policy(DatabaseNotification::class, NotificationPolicy::class);

        Gate::define('reports.view', [ReportPolicy::class, 'view']);
        Gate::define('reports.export', [ReportPolicy::class, 'export']);
    }
}
