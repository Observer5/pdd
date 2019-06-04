<?php


namespace EasyPdd\Foundation\ServiceProviders;

use EasyPdd\Mall\Client;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class MallServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['mall'] = function ($pimple) {

            $clientID = $pimple['config']->get('client_id');
            $clientSecret = $pimple['config']->get('client_secret');

            return new Client($clientID, $clientSecret);
        };
    }

}