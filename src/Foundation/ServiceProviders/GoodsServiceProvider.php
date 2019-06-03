<?php


namespace EasyPdd\Foundation\ServiceProviders;

use EasyPdd\Goods\Client;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class GoodsServiceProvider implements ServiceProviderInterface
{

    public function register(Container $pimple)
    {
        $pimple['goods'] = function ($pimple) {

            $clientID = $pimple['config']->get('client_id');
            $clientSecret = $pimple['config']->get('client_secret');
            return new Client($clientID, $clientSecret);
        };
    }

}