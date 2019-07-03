<?php

namespace EasyPdd\Core;


use EasyPdd\Core\Exceptions\HttpException;
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
    
    /**
     * @var Http
     */
    protected $http;


    public function __construct($client_id, $client_secret)
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
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
            Log::debug("Client Request: {$request->getMethod()} {$request->getUri()} ", [$options, $request->getHeaders()]);

        }, function (RequestInterface $request, $options, PromiseInterface $response) {

            $response->then(function (ResponseInterface $response) {
                Log::debug('API response:', [
                    'Status' => $response->getStatusCode(),
                    'Reason' => $response->getReasonPhrase(),
                    'Headers' => $response->getHeaders(),
                    'Body' => strval($response->getBody()),
                ]);
            });
        });
    }

    /**
     * @param $apiType
     * @param string $accessToken
     * @param array $params
     *
     * @return Collection
     */
    public function request($apiType, $accessToken = '', array $params = [])
    {
        $http = $this->getHttp();

        $data = $this->_commonParams($apiType, $accessToken, $params);

        $contents = $http->parseJSON(call_user_func_array([$http, 'json'], [$data]));

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
        $params = array(
            'type' => $apiType,
            'client_id' => $this->client_id,
            'timestamp' => time(),
            'data_type' => 'json',
            'version' => 'v1',
        );

        if ($accessToken) {
            $params['access_token'] = $accessToken;
        }
        
        $params = array_merge($params, $fields);

        $params = $this->_paramsHandle($params);

        $params['sign'] = $this->_sign($params);

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
    private function _sign(array $params)
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