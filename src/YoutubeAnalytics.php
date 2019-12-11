<?php


namespace mosinas\GoogleService;


class YoutubeAnalytics
{
    protected $client;

    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = new \Google_Client(Arr::get($config, 'config', []));
        $this->client->setApplicationName('YouTubeAnalytics');
    }
}
