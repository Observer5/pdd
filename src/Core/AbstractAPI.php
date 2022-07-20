<?php

namespace EasyPdd\Core;


use EasyPdd\Core\Exceptions\HttpException;
use EasyPdd\Foundation\Application;
use EasyPdd\Support\Collection;
use EasyPdd\Support\Log;
use GuzzleHttp\Middleware;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractAPI
{
    /**
     * @var
     */
    private $client_id;

    /**
     * @var
     */
    private $client_secret;

    protected $needToken;

    protected $application;

    protected $model_type;

    protected $base_uri;

    protected $api_key;

    protected $user_agent;

    protected $referer;


    /**
     * @var Http
     */
    protected $http;


    /**
     * AbstractAPI constructor.
     * @param Application $application
     * @param bool $needToken
     */
    public function __construct(Application $application, $needToken = false)
    {
        $this->client_id = $application['config']->get('client_id');
        $this->client_secret = $application['config']->get('client_secret');

        if ($application['config']->get('model_type')) {
            $this->base_uri = $application['config']->get('base_uri');
            $this->api_key = $application['config']->get('api_key');
            $this->model_type = $application['config']->get('model_type');

            $userAgents = $application['config']->get('user_agent');
            $referers = $application['config']->get('referer');

            if (!is_array($userAgents)) {
                $userAgents = json_decode($userAgents, true);
            }
            $this->user_agent = !empty($userAgents) ? $userAgents[array_rand($userAgents, 1)] : '';

            if (!is_array($referers)) {
                $referers = json_decode($referers, true);
            }
            $this->referer = !empty($referers) ? $referers[array_rand($referers, 1)] : '';
        }

        $this->application = $application;
        $this->needToken = $needToken;
    }

    /**
     * @return Http
     */
    public function getHttp()
    {
        if (is_null($this->http)) {
            $this->http = new Http();
        }

        if (count($this->http->getMiddlewares()) === 0) {
            $this->registerHttpMiddlewares();
        }

        return $this->http;
    }

    /**
     * @param $http
     */
    public function setHttp($http)
    {
        $this->http = $http;
    }

    protected function registerHttpMiddlewares()
    {
        // log
        $this->http->addMiddleware($this->logMiddleware());
    }

    /**
     * @return callable
     */
    protected function logMiddleware()
    {
        return Middleware::tap(function (RequestInterface $request, $options) {
            Log::debug("Client Request: {$request->getMethod()} {$request->getUri()} ",
                [$options, $request->getHeaders()]);

        }, function (RequestInterface $request, $options, PromiseInterface $response) {

            $response->then(function (ResponseInterface $response) {
                Log::debug('API response:', [
                    'Status'  => $response->getStatusCode(),
                    'Reason'  => $response->getReasonPhrase(),
                    'Headers' => $response->getHeaders(),
                    'Body'    => strval($response->getBody()),
                ]);
            });
        });
    }

    /**
     * @param $apiType
     * @param  string  $accessToken
     * @param  array  $params
     * @return Collection
     * @throws HttpException
     */
    public function request($apiType, $accessToken = '', array $params = [])
    {
        $http = $this->getHttp();

        $data = $this->_commonParams($apiType, $accessToken, $params);

        if (!empty($this->model_type)) {
            $http->baseUri = $this->base_uri;

            $headers = [
                'content-type'     => 'application/json',
                'user-agent'       =>  $this->user_agent,
                'referer'          =>  $this->referer,
                'X-Requested-With' => 'XMLHttpRequest',
                'Cookie'           => 'admin=' . mt_rand(10, 100),
            ];

            $requestParams = [$data, JSON_UNESCAPED_UNICODE, 'apiKey='.$this->api_key, $headers];
        } else {
            $requestParams = [$data];
        }

        $contents = $http->parseJSON(call_user_func_array([$http, 'json'], $requestParams));

        $this->checkAndThrow($contents);

        return new Collection($contents);
    }

    /**
     * @param array $contents
     *
     * @throws HttpException
     */
    protected function checkAndThrow(array $contents)
    {
        if (isset($contents['error_response'])) {
            $error_code = $contents['error_response']['error_code'];
            $error_msg = $contents['error_response']['error_msg'];
            throw new HttpException($error_msg, $error_code);
        }
    }

    /**
     * @param $apiType
     * @param $accessToken
     * @param array $fields
     * @return array
     */
    private function _commonParams($apiType, $accessToken = '', $fields = [])
    {
        $params = [
            'type'      => $apiType,
            'client_id' => $this->client_id,
            'timestamp' => time(),
            'data_type' => 'json',
            'version'   => 'v1',
        ];

        if ($accessToken) {
            $params['access_token'] = $accessToken;
        }

        $params = array_merge($params, $fields);

        $params = $this->_paramsHandle($params);

        if (empty($this->model_type)) {
            $params['sign'] = $this->_signature($params);
        }

        return $params;
    }

    /**
     * @param array $params
     *
     * @return array
     */
    private function _paramsHandle(array $params)
    {
        array_walk($params, function (&$item) {
            if (is_array($item)) {
                $item = json_encode($item);
            }
            if (is_bool($item)) {
                $item = ['false', 'true'][intval($item)];
            }
        });
        return $params;
    }

    /**
     * @param array $params
     *
     * @return string
     */
    private function _signature(array $params)
    {
        ksort($params);
        $to_sign = $this->client_secret;
        foreach ($params as $key => $value) {
            $to_sign .= "$key$value";
        }
        unset($key, $value);
        $to_sign .= $this->client_secret;

        return strtoupper(md5($to_sign));
    }

}