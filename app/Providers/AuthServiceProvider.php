<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use LdapRecord\Laravel\Middleware\WindowsAuthenticate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        WindowsAuthenticate::rememberAuthenticatedUsers();
        //WindowsAuthenticate::logoutUnauthenticatedUsers();   //CAN ENABLE THIS ONCE SETUP
    }
}
