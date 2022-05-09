<?php

namespace EasyPdd\OAuth;

use EasyPdd\Core\Exceptions\AuthorizeFailedException;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class OAuth
 *
 * @package EasyPdd\OAuth
 *
 */
class OAuth
{

    const TOKEN_API = 'https://open-api.pinduoduo.com/oauth/token';

    /**
     * The client ID.
     *
     * @var string
     */
    protected $clientId;

    /**
     * The client secret.
     *
     * @var string
     */
    protected $clientSecret;

    /**
     * The member type
     *
     * @var string
     */
    protected $memberType;

    /**
     * The redirect URL.
     *
     * @var string
     */
    protected $redirectUrl;

    /**
     * The custom parameters to be sent with the request.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * The type of the encoding in the query.
     *
     * @var int Can be either PHP_QUERY_RFC3986 or PHP_QUERY_RFC1738
     */
    protected $encodingType = PHP_QUERY_RFC1738;

    /**
     * The options for guzzle\client.
     *
     * @var array
     */
    protected static $guzzleOptions = ['http_errors' => false];

    /**
     * OAuth constructor.
     *
     * @param $clientId
     * @param $clientSecret
     * @param null $redirectUrl
     * @param string $memberType
     */
    public function __construct($clientId, $clientSecret, $redirectUrl = null, $memberType = 'MERCHANT')
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUrl = $redirectUrl;
        $this->memberType = $memberType;
    }

    /**
     * Set the custom parameters of the request.
     *
     * @param array $parameters
     *
     * @return $this
     */
    public function with(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @param string $state
     * @param string $view
     *
     * @return string
     */
    public function authorizationUrl($state, $view = 'web')
    {
        $authorizeUrlArr = [
            'MERCHANT'  => 'https://fuwu.pinduoduo.com/service-market/auth?',   //拼多多店铺,WEB端网页授权
            'H5'        => 'https://mai.pinduoduo.com/h5-login.html?',          //拼多多店铺,H5移动端网页授权
            'JINBAO'    => 'https://jinbao.pinduoduo.com/open.html?',           //多多进宝推手,WEB端网页授权
            'KTT'       => 'https://oauth.pinduoduo.com/authorize/ktt?',        //快团团团长,WEB端网页授权
            'LOGISTICS' => 'https://wb.pinduoduo.com/logistics/auth?',          //拼多多电子面单用户,WEB端网页授权
        ];

        $query = array_merge([
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->redirectUrl,
            'response_type' => 'code',
            'state'         => $state ?: md5(time()),
            'view'          => $view,
        ], $this->parameters);
        return $authorizeUrlArr[$this->memberType] . http_build_query($query, '', '&', $this->encodingType);
    }


    /**
     * 重定向至授权 URL.
     *
     * @param string $redirectUrl
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function authorizationRedirect($redirectUrl = null)
    {
        $state = null;
        if (!is_null($redirectUrl)) {
            $this->redirectUrl = $redirectUrl;
        }

        return new RedirectResponse($this->authorizationUrl($state));
    }

    /**
     * Get a fresh instance of the Guzzle HTTP client.
     *
     * @return \GuzzleHttp\Client
     */
    protected function getHttpClient()
    {
        return new Client(self::$guzzleOptions);
    }

    /**
     * @param $code
     *
     * @return AccessTokenInterface
     */
    public function getAccessToken($code)
    {
        $response = $this->getHttpClient()->post(self::TOKEN_API, [
            'headers' => ['Accept' => 'application/json', 'content-type' => 'application/json'],
            'body'    => json_encode($this->getTokenFields($code)),
        ]);

        return $this->parseAccessToken($response->getBody());
    }

    /**
     * @param $code
     *
     * @return array
     */
    protected function getTokenFields($code)
    {
        return [
            'client_id'     => $this->clientId,
            'code'          => $code,
            'grant_type'    => 'authorization_code',
            'client_secret' => $this->clientSecret,
        ];
    }

    /**
     * Get the access token from the token response body.
     *
     * @param \Psr\Http\Message\StreamInterface|array $body
     *
     * @return AccessTokenInterface
     * @throws AuthorizeFailedException
     */
    protected function parseAccessToken($body)
    {
        if (!is_array($body)) {
            $body = json_decode($body, true);
        }
        if (empty($body['access_token'])) {
            throw new AuthorizeFailedException('Authorize Failed: ' . json_encode($body, JSON_UNESCAPED_UNICODE),
                $body);
        }

        return new AccessToken($body);
    }
}