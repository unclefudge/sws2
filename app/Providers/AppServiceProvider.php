<?php

namespace App\Providers;

use App\Models\Site\Site;
use App\Models\Site\SiteFoc;
use App\Observers\SiteFocObserver;
use App\Observers\SiteObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Site::observe(SiteObserver::class);
        SiteFoc::observe(SiteFocObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
