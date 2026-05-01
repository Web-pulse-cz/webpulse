<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Routing\Route;
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

        $this->configureApiDocs();
    }

    /**
     * Split Scramble docs into two separate APIs: admin and client.
     */
    private function configureApiDocs(): void
    {
        Scramble::configure()->expose(false);

        Scramble::registerApi('admin', [
            'info' => [
                'version' => env('API_VERSION', '0.0.1'),
                'description' => 'WebPulse admin endpoints under `/api/admin/*`. Require Sanctum auth and an `X-Site-Hash` header.',
            ],
            'ui' => [
                'title' => 'WebPulse Admin API',
                'theme' => 'light',
                'layout' => 'responsive',
                'hide_try_it' => false,
                'hide_schemas' => false,
                'try_it_credentials_policy' => 'include',
            ],
            'middleware' => ['web'],
        ])
            ->routes(fn (Route $route) => str_starts_with($route->uri, 'api/admin'))
            ->expose(
                ui: 'docs/api/admin',
                document: 'docs/api/admin.json',
            );

        Scramble::registerApi('client', [
            'info' => [
                'version' => env('API_VERSION', '0.0.1'),
                'description' => 'WebPulse public client endpoints under `/api/*` consumed by managed websites. Authenticated via the `X-Site-Hash` header.',
            ],
            'ui' => [
                'title' => 'WebPulse Client API',
                'theme' => 'light',
                'layout' => 'responsive',
                'hide_try_it' => false,
                'hide_schemas' => false,
                'try_it_credentials_policy' => 'include',
            ],
            'middleware' => ['web'],
        ])
            ->routes(fn (Route $route) => str_starts_with($route->uri, 'api/')
                && ! str_starts_with($route->uri, 'api/admin'))
            ->expose(
                ui: 'docs/api/client',
                document: 'docs/api/client.json',
            );
    }
}
