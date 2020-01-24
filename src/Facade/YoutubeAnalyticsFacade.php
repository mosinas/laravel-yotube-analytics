<?php

namespace Mosinas\YoutubeAnalytics\Facade;

use Illuminate\Support\Facades\Facade;

class YoutubeAnalyticsFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Mosinas\YoutubeAnalytics\YoutubeAnalytics::class';
    }
}
