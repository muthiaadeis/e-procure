<?php

namespace App\Providers;

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
        // Pagination pill bawaan Laravel kelihatan gelap/kaku dan gak nyambung
        // sama tema indigo di app ini, jadi dipakein view custom secara global.
        Paginator::defaultView('vendor.pagination.custom');
    }
}
