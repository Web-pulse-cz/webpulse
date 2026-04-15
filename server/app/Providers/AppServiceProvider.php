<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
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
        ResetPassword::createUrlUsing(function (object $user, string $token) {
            $adminUrl = rtrim(config('app.admin_url', 'https://admin.web-pulse.cz'), '/');

            return $adminUrl.'/obnoveni-hesla?token='.$token.'&email='.urlencode($user->email);
        });
    }
}
