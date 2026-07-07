<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\DB;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        //$this->registerPolicies();

        if (app()->environment('local')) {
            DB::listen(function ($query) {
                if ($query->time > 100) {
                    logger('Slow query', ['sql' => $query->sql, 'bindings' => $query->bindings, 'time_ms' => $query->time,]);
                }
            });
        }
    }
}
