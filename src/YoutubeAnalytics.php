<?php


namespace Mosinas\YoutubeAnalytics;


class YoutubeAnalytics
{
    /** @var YoutubeAnalyticsClient $client */
    protected $client;


    /** @var array */
    protected $config;


    public function __construct(YoutubeAnalyticsClient $client, array $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * @return YoutubeAnalyticsClient
     */
    public function getClient()
    {
        return $this->client;
    }

    public function initService($accessToken)
    {
        $this->client->initService($accessToken);

        return $this;
    }

    public function refreshToken($accessToken)
    {
        return $this->client->refreshToken($accessToken);
    }


    protected function getService()
    {
        $this->client->getService();
    }

    public function query()
    {
        $service = $this->client->getService();

        $service->reports->query()
        return $this;
    }

}

