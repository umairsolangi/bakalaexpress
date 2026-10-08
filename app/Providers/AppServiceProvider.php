<?php

namespace App\Providers;

use App\Models\Seller;
use App\Observers\SellerObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        Seller::observe(SellerObserver::class);

        // Make the logo component available to all views
        View::composer('*', function ($view) {
            $view->with('appName', config('app.name', 'Bakala Express'));
        });
    }
}
