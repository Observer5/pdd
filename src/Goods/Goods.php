<?php

namespace EasyPdd\Goods;

use EasyPdd\Core\AbstractAPI;

/**
 * Class Order
 *
 * @package EasyExpress\Order
 *
 */
class Goods extends AbstractAPI
{
    const API_LIST_GET = 'pdd.goods.list.get';

    public function __call($method, $arguments)
    {
        
    }

    /**
     * @return \EasyPdd\Support\Collection
     */
    public function list()
    {
        return $this->request(self::API_LIST_GET, 'token');
    }
}