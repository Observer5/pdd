<?php


namespace EasyPdd\Foundation\ServiceProviders;


use EasyPdd\Foundation\Api;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ApiServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple)
    {
        $pimple['api'] = function ($pimple) {

            return new Api($pimple);
        };
    }
}