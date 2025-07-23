<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Client\LongLivedAccessToken;
use AmoCRM\Exceptions\AmoCRMApiException;

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

            $token = new LongLivedAccessToken($accessToken);

            $apiClient = new AmoCRMApiClient();
            $apiClient->setAccessToken($token)
                      ->setAccountBaseDomain($accountUrl);

            $apiClient->getHttpClientOptions()->setVerify(false);
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
