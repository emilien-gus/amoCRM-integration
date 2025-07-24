<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Client\LongLivedAccessToken;
use Illuminate\Support\Facades\Log;

class AmoCRMServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->singleton(AmoCRMApiClient::class, function ($app) {
            $accessToken = config('amocrm.access_token');
            $accountUrl = config('amocrm.account_url');

            $apiClient = new AmoCRMApiClient();
            $token = new LongLivedAccessToken($accessToken);

            $apiClient->setAccessToken($token)
                      ->setAccountBaseDomain($accountUrl);

            return $apiClient;
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
