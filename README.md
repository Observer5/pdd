# EasyPDD [![Build Status](https://travis-ci.org/Observer5/pdd.svg?branch=master)](https://travis-ci.org/Observer5/pdd)

EasyPDD is a PHP library for use open PinDuoDuo APIs.

## Requirement

1. PHP >= 7.0.0
2. PHP cURL 扩展
3. PHP OpenSSL 扩展

> SDK 对所使用的框架并无特别要求

## Installation

```shell
composer require "observer/pdd:~1.0" -vvv

```

## Usage

基本使用:（以附加服务查询为例）

```php


$config = [
    'debug'  => true,

    'client_id'    => 'eaaedbeff0734feea6b0c901474456e2',
    'client_secret' => 'c2eda0c398xxxxxxbd63ff57bf22c05xxxxxx',
    'log'    => [
        'level' => 'debug',
        'file'  => '/tmp/easypdd.log',
    ],
    'oauth'  => [
        'callback'    => 'http:/foo.com/oauth_callback',
        'member_type' => 'MERCHANT',
    ],

];

$app = new \EasyPdd\Foundation\Application($config);

$goods = $app->goods;

$goods->list('access_token');

```


## Documentation

[wiki](https://open.pinduoduo.com/#/document)

> 强烈建议看懂拼多多文档后再来使用本 SDK。


## License

MIT

