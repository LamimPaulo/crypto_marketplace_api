<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

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
        $this->registerPolicies();

        Passport::ignoreMigrations();

        Passport::routes(function ($router) {
            $router->forAccessTokens();
        });

        if(env('APP_ENV')==='local') {
            Passport::tokensExpireIn(now()->addYear());
        }else{
            Passport::tokensExpireIn(now()->addHours(12));
        }
    }
}
