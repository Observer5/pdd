<?php


namespace EasyPdd\Foundation\ServiceProviders;

use EasyPdd\Goods\Client;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class GoodsServiceProvider
 *
 * @package EasyPdd\Foundation\ServiceProviders
 */
class GoodsServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple)
    {
        $pimple['goods'] = function ($pimple) {

            $clientID = $pimple['config']->get('client_id');
            $clientSecret = $pimple['config']->get('client_secret');

            return new Client($clientID, $clientSecret);
        };
    }

}