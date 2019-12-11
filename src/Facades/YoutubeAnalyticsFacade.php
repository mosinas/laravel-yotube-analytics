<?php

namespace mosinas\GoogleService\Facades;

use Illuminate\Support\Facades\Facade;
use mosinas\GoogleService\YoutubeAnalytics;

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
