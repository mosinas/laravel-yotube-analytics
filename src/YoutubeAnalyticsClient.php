<?php


namespace Mosinas\YoutubeAnalytics;

use Illuminate\Contracts\Cache\Repository;
use Mosinas\YoutubeAnalytics\Exceptions\YoutubeAnalyticsException;
use Mosinas\YoutubeAnalytics\Exceptions\AccessTokenExpiredException;
use Mosinas\YoutubeAnalytics\Exceptions\InvalidConfigurationException;
use Mosinas\YoutubeAnalytics\Exceptions\NotValidAccessToken;

class YoutubeAnalyticsClient
{
    const PARAM_IDS = 'ids';
    const PARAM_START_DATE = 'startDate';
    const PARAM_END_DATE = 'endDate';
    const PARAM_METRICS = 'metrics';
    const PARAM_DIMENSIONS = 'dimensions';
    const PARAM_MAX_RESULT = 'maxResults';
    const PARAM_FILTER = 'filter';
    const PARAM_SORT = 'sort';


    /** @var \Google_Client $client */
    protected $client;

    /** @var \Google_Service_YouTubeAnalytics $service */
    protected $service;

    /** @var Repository */
    protected $cache;

    /** @var int */
    protected $cacheLifeTime= 0;

    protected $accessToken;

    protected $channelId;

    protected $videoIds;

    protected $params;

    protected $isTokenRefreshed;


    /** @var \Google_Service_YouTubeAnalytics_QueryResponse response */
    protected $response = [];

    /**
     * YoutubeAnalyticsClient constructor.
     * @param  \Google_Service_YouTubeAnalytics  $service
     * @param  Repository  $cache
     */
    public function __construct(\Google_Client $client, Repository $cache)
    {
        $this->client = $client;

        $this->cache = $cache;

        $this->isTokenRefreshed = false;
    }

    /**
     * Set the cache time.
     *
     * @param int $cacheLifeTimeInMinutes
     *
     * @return self
     */
    public function setCacheLifeTimeInMinutes(int $cacheLifeTimeInMinutes)
    {
        $this->cacheLifeTime = $cacheLifeTimeInMinutes * 60;

        return $this;
    }

    public function initService($accessToken)
    {
        $accessToken  = json_decode($accessToken, true);

        try {
            $this->client->setAccessToken($accessToken);
        } catch (\InvalidArgumentException $e) {
            throw new InvalidConfigurationException($e->getMessage(), $e->getCode());
        }

        if($this->client->isAccessTokenExpired()) {
//            throw new AccessTokenExpiredException('access token is expired');
            $this->accessToken = $this->refreshToken();
            $this->isTokenRefreshed = true;
        }

        $this->service = new \Google_Service_YouTubeAnalytics($this->client);

        return $this;
    }

    public function isInitializedService() : bool
    {
        return $this->service->serviceName ? true : false;
    }

    public function refreshToken()
    {
        $this->client->refreshToken($this->client->getRefreshToken());

        $token = $this->client->getAccessToken();

//        $this->client->setAccessToken($token);

        return $token;
    }

    public function getService()
    {
        return $this->service;
    }

    /**
     * @param  string  $channelId
     * @return $this
     */
    public function setChannelId(string $channelId) : self
    {
        $this->channelId = $channelId;

        return $this;
    }

    public function setVideoIds(array $videoIds)
    {
        $this->videoIds = $videoIds;

        return $this;
    }

    protected function setParam($key, $value)
    {
        $this->params[$key] = $value;
        return $this;
    }

    public function setParamIds($value, $field='channel')
    {
        return $this->setParam('ids', ($field . '==' . $value));
    }

    public function setParamStartDate(\DateTime $dateTime)
    {
        return $this->setParam('startDate', $dateTime->format('Y-m-d'));
    }

    public function setParamEndDate(\DateTime $dateTime)
    {
        return $this->setParam('endDate', $dateTime->format('Y-m-d'));
    }

    public function setParamMetrics(array $metrics)
    {
        return $this->setParam('metrics', implode(',', $metrics));
    }

    /**
     * @param  array  $arrayFilters ['filterKey' => ['filterValue1', 'filterValue2']]
     * @return $this
     */
    public function setParamFilters(array $arrayFilters)
    {
        $filters = [];
        foreach ($arrayFilters as $key => $filter) {
            $filters []= $key . '==' . implode(',', $filter);
        }
        $filters = implode(';', $filters);


        return $this->setParam('filters', $filters);
    }

    public function setParamDimensions(array $dimensions)
    {
        return $this->setParam('dimensions', implode(',', $dimensions));
    }

    public function setParamSort($sort)
    {
        return $this->setParam('sort', $sort);
    }

    public function setParamMaxResult($maxResult)
    {
        return $this->setParam('maxResults', $maxResult);
    }

    public function clearParams()
    {
        $this->params = [];
        return $this;
    }

    public function query()
    {
        if(!$this->params) {
            throw new YoutubeAnalyticsException('Empty options params empty');
        }

        if(!$this->service) {
            throw new YoutubeAnalyticsException('It is not possible to initialize the client Youtube Analytics');
        }

        try {
            $this->response = $this->service->reports->query($this->params);
        } catch (\Google_Service_Exception $e) {
            $errorMsg = json_decode($e->getMessage(), true);

            if($errorMsg['error'] === 'invalid_grant') {
                throw new NotValidAccessToken('Not valid access token');
            } else {
                throw new YoutubeAnalyticsException($e->getMessage());
            }

        }

        return $this;
    }

    public function getResponse()
    {
        if(empty($this->response)) {
            return [];
        }

        return $this->transformResponse($this->response);
    }


    /**
     * @param \Google_Service_YouTubeAnalytics_QueryResponse $response
     * @return array
     */
    protected function transformResponse($response)
    {
        $headers = [];
        $transformResponse = [];

        $columnHeaders = $response->getColumnHeaders();
        if($columnHeaders) {
            /** @var \Google_Service_YouTubeAnalytics_ResultTableColumnHeader $columnHeader */
            foreach ($columnHeaders as $columnHeader) {
                $headers []= $columnHeader->getName();
            }
        }

        $rows = $response->getRows();

        foreach ($rows as $row) {
            $responseItem = [];
            foreach ($row as $key => $value) {
                $responseItem[$headers[$key]] = $value;
            }

            if($responseItem) {
                $transformResponse []= $responseItem;
            }
        }


        return $transformResponse;
    }

    public function isTokenRefreshed()
    {
        return $this->isTokenRefreshed;
    }

    public function getToken()
    {
        return $this->accessToken;
    }
}
