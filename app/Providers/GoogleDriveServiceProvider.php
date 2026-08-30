<?php

namespace App\Providers;

use Google\Client;
use Google\Service\Drive;
use Illuminate\Support\ServiceProvider;

class GoogleDriveServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(Drive::class, function ($app) {
            $client = new Client;
            $client->setClientId(config('google.client_id'));
            $client->setClientSecret(config('google.client_secret'));
            $client->setRedirectUri(config('google.redirect_uri'));
            $client->refreshToken(config('google.refresh_token'));
            $client->setAccessType('offline');
            $client->setApprovalPrompt('force');

            return new Drive($client);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
