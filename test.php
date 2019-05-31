<?php

use EasyPdd\Foundation\Application;

require __DIR__ . '/vendor/autoload.php';

$options = [
    'debug'  => true,

    'client_id'    => 'ea0edbeff0764fe0a6b0d901474456e2',
    'client_secret' => 'c2eda0c398xxxxxxbd63ff57bf22c05xxxxxx',
    'log'    => [
        'level' => 'debug',
        'file'  => '/tmp/easywechat.log',
    ],
    'oauth'  => [
        'callback'    => 'http:/test.open.5icxf.com/oauth_callback',
        'member_type' => 'MERCHANT',//用户角色 ：MERCHANT(商家授权),H5(移动端),多多客(JINBAO),
    ],

];

$app = new Application($options);

//var_dump($app->oauth->redirect());
var_dump($app->goods->list());

