<?php


namespace EasyPdd\Foundation;


use EasyPdd\Core\AbstractAPI;

class Api extends AbstractAPI
{

    /**
     * @param bool $auth
     *
     * @return $this
     */
    public function auth(bool $auth = true): Api
    {
        $this->needToken = $auth;

        return $this;
    }
}