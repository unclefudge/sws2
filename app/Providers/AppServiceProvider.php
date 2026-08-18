<?php

namespace App\Providers;

use App\Models\Site\Site;
use App\Models\Site\SiteFoc;
use App\Observers\SiteFocObserver;
use App\Observers\SiteObserver;
use Illuminate\Database\Eloquent\Relations\Relation;
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

        Relation::morphMap([
            'site_foc' => \App\Models\Site\SiteFoc::class,
            'site_maintenance' => \App\Models\Site\SiteMaintenance::class,
            'site_qa' => \App\Models\Site\SiteQa::class,
            'site_asbestos' => \App\Models\Site\SiteAsbestos::class,
            'site_hazards' => \App\Models\Site\SiteHazard::class,
            'site_accidents' => \App\Models\Site\SiteAccident::class,
            'site_notes' => \App\Models\Site\SiteNote::class,
            'site_prac_completion' => \App\Models\Site\SitePracCompletion::class,
            'site_inspection_plumbing' => \App\Models\Site\SiteInspectionPlumbing::class,
            'site_inspection_electrical' => \App\Models\Site\SiteInspectionElectrical::class,
            'company_docs_review' => \App\Models\Company\CompanyDocReview::class,
            'companys' => \App\Models\Company\Company::class,
            'supervisor_checklist' => \App\Models\Misc\Supervisor\SuperChecklist::class,
        ]);
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
