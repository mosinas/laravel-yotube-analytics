<?php

namespace mosinas\YoutubeAnalyticsService\Facades;

use Illuminate\Support\Facades\Facade;
use mosinas\YoutubeAnalyticsService\YoutubeAnalytics;

class YoutubeAnalyticsFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return YoutubeAnalytics::class;
    }
}
