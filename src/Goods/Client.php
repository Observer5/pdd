<?php

namespace EasyPdd\Goods;

use EasyPdd\Core\AbstractAPI;
use EasyPdd\Support\Collection;

/**
 * Class Order
 *
 * @package EasyExpress\Order
 *
 */
class Client extends AbstractAPI
{
    /**
     * @return \EasyPdd\Support\Collection
     */
    public function list()
    {
        return $this->request('pdd.goods.list.get', 'token');
    }

    public function test()
    {
        return new Collection([1, 2, 3]);
    }
}