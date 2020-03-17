<?php

namespace Mosinas\YoutubeAnalytics;

use Illuminate\Support\ServiceProvider;

class YoutubeAnalyticsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([ __DIR__ . '/config/youtube_analytics.php' => config_path('youtube_analytics.php')], 'youtube_analytics');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/youtube_analytics.php', 'youtube-analytics');

        $this->app->bind(YoutubeAnalyticsClient::class, function () {
            $analyticsConfig = config('youtube-analytics');
            return YoutubeAnalyticsClientFactory::createForConfig($analyticsConfig);
        });

        $this->app->alias(YoutubeAnalyticsClient::class, 'youtube-analytics');
    }
}
