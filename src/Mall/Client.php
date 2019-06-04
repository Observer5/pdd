<?php
namespace EasyPdd\Mall;

use EasyPdd\Core\AbstractAPI;

class Client extends AbstractAPI
{
    /**
     * @return \EasyPdd\Support\Collection
     */
    public function info()
    {
        return $this->request('pdd.mall.info.get', 'token');
    }

}