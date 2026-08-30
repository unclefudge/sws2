<?php

namespace App\Providers;

use App\Models\Site\Site;
use App\Models\Site\SiteFoc;
use App\Observers\SiteFocObserver;
use App\Observers\SiteObserver;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Queue;
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

        // Child jobs (queued mailables, PDF batches, etc.) inherit both the
        // scheduled run id and the automatic recipients selected by the handler.
        // Queue listeners restore both values in the worker so a queued email is
        // logged against the correct run and keeps its intended recipients.
        Queue::createPayloadUsing(function (): array {
            $runId = app(\App\Scheduled\ScheduledRunContext::class)->runId();
            if (!$runId) {
                return [];
            }

            $payload = ['sws_scheduled_run_id' => $runId];
            $dynamicRecipients = app(\App\Scheduled\ScheduledDynamicRecipientContext::class)->all();

            if ($dynamicRecipients) {
                $payload['sws_scheduled_dynamic_recipients'] = $dynamicRecipients;
            }

            return $payload;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // A singleton is essential here: mail events fired inside a scheduled
        // queue job must see the same run context that the runner opened.
        $this->app->singleton(\App\Scheduled\ScheduledRunContext::class);
        $this->app->singleton(\App\Scheduled\ScheduledDynamicRecipientContext::class);
    }
}
