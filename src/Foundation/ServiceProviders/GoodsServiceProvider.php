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
            return new Client($pimple, true);
        };
    }

}