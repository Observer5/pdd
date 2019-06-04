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

    public function getAuthUrl($state)
    {
        $authorizeUrlArr = [
            'MERCHANT' => 'https://mms.pinduoduo.com/open.html', //商家授权正式环境
            'H5'       => 'https://mai.pinduoduo.com/h5-login.html', //移动端授权正式环境
            'JINBAO'   => 'https://jinbao.pinduoduo.com/open.html', //多多客授权正式环境
        ];

        return $this->buildAuthUrlFromBase($authorizeUrlArr[$this->memberType], $state);
    }

    /**
     * {@inheritdoc}.
     */
    protected function buildAuthUrlFromBase($url, $state)
    {
        $query = http_build_query($this->getCodeFields($state), '', '&', $this->encodingType);

        return $url . '?' . $query;
    }

    /**
     * {@inheritdoc}.
     */
    protected function getCodeFields($state = null)
    {
        return array_merge([
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->redirectUrl,
            'response_type' => 'code',
            'state'         => $state ?: md5(time()),
        ], $this->parameters);
    }

    /**
     * Redirect the user of the application to the provider's authentication screen.
     *
     * @param string $redirectUrl
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect($redirectUrl = null)
    {
        $state = null;
        if (!is_null($redirectUrl)) {
            $this->redirectUrl = $redirectUrl;
        }

        return new RedirectResponse($this->getAuthUrl($state));
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
     * {@inheritdoc}.
     */
    public function getAccessToken($code)
    {
        $response = $this->getHttpClient()->get($this->getTokenUrl(), [
            'headers' => ['Accept' => 'application/json'],
            'query'   => $this->getTokenFields($code),
        ]);

        return $this->parseAccessToken($response->getBody());
    }

    protected function getTokenUrl()
    {
        return 'http://open-api.pinduoduo.com/oauth/token';
    }

    /**
     * {@inheritdoc}.
     */
    protected function getTokenFields($code)
    {
        return array_filter([
            'client_id'     => $this->clientId,
            'code'          => $code,
            'grant_type'    => 'authorization_code',
            'client_secret' => $this->clientSecret,
        ]);
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