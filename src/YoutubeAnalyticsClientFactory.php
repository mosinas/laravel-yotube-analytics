<?php

namespace Mosinas\YoutubeAnalytics;

use Google_Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\Repository;
use Symfony\Component\Cache\Adapter\Psr16Adapter;


class YoutubeAnalyticsClientFactory
{

    public static function createForConfig(array $analyticsConfig): YoutubeAnalyticsClient
    {
        $authenticatedClient = self::createAuthenticatedGoogleClient($analyticsConfig);

        return self::createAnalyticsClient($analyticsConfig, $authenticatedClient);

        return $authenticatedClient;
    }

    public static function createAuthenticatedGoogleClient(array $config): Google_Client
    {
        $client = new Google_Client();

        $client->setAuthConfig(Arr::get($config, 'client_secret'));
        $client->setRedirectUri(Arr::get($config, 'redirect_uri'));

        $client->addScope(Arr::get($config, 'scope'));

        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');

//        $client->setAccessToken(Arr::get($config, 'access_token'));

        // throw exception is access token expired
//        if ($client->isAccessTokenExpired()) {
//            throw new YoutubeAnalyticsException('access token is expired');
//        }

//        self::configureCache($client, $config['cache']);

        return $client;
    }

    protected static function configureCache(Google_Client $client, $config)
    {
        $config = collect($config);

        $store = Cache::store($config->get('store'));

        $cache = new Psr16Adapter($store);

        $client->setCache($cache);

        $client->setCacheConfig(
            $config->except('store')->toArray()
        );
    }

    protected static function createAnalyticsClient(array $analyticsConfig, Google_Client $client) : YoutubeAnalyticsClient
    {
        $client = new YoutubeAnalyticsClient($client, app(Repository::class));

        $client->setCacheLifeTimeInMinutes($analyticsConfig['cache_lifetime']);

        return $client;
    }
}
