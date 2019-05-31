<?php
namespace EasyPdd\Foundation\ServiceProviders;

use EasyPdd\OAuth\OAuth;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class OAuthServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     *
     *  https://www.easywechat.com/docs/3.x/oauth
     */
    public function register(Container $pimple)
    {
        $pimple['oauth'] = function ($pimple) {

            $clientID = $pimple['config']->get('client_id');
            $clientSecret = $pimple['config']->get('client_secret');
            $memberType = $pimple['config']->get('oauth.member_type');
            $redirectUrl = $pimple['config']->get('oauth.callback');

            return new OAuth($clientID, $clientSecret, $redirectUrl, $memberType);
        };
    }


}