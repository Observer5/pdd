<?php
namespace EasyPdd\Foundation;

use Doctrine\Common\Cache\Cache as CacheInterface;
use Doctrine\Common\Cache\FilesystemCache;

use EasyPdd\Goods\Client as GoodsClient;
use EasyPdd\OAuth\OAuth;
use EasyPdd\Support\Log;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Pimple\Container;

/**
 * Class Application
 *
 * @package EasyPdd\Foundation
 *
 *
 * @property Api $api
 * @property OAuth $oauth
 * @property GoodsClient $goods
 */
class Application extends Container
{
    /**
     * @var array
     */
    protected $providers = [
        ServiceProviders\ApiServiceProvider::class,
        ServiceProviders\GoodsServiceProvider::class,
        ServiceProviders\OAuthServiceProvider::class,
    ];

    /**
     * Application constructor.
     *
     * @param array $config
     *
     */
    public function __construct($config)
    {
        parent::__construct();

        $this['config'] = function() use ($config) {
            return new Config($config);
        };

        $this->registerBase();

        $this->registerProviders();

        $this->initializeLogger();

    }

    /**
     * Register basic providers.
     */
    private function registerBase()
    {
        if (!empty($this['config']['cache']) && $this['config']['cache'] instanceof CacheInterface) {
            $this['cache'] = $this['config']['cache'];
        } else {
            $this['cache'] = function () {
                return new FilesystemCache(sys_get_temp_dir());
            };
        }
    }

    /**
     * Add a provider.
     *
     * @param string $provider
     *
     * @return Application
     */
    public function addProvider($provider)
    {
        array_push($this->providers, $provider);
        return $this;
    }

    /**
     * Set providers.
     *
     * @param array $providers
     */
    public function setProviders(array $providers)
    {
        $this->providers = [];
        foreach ($providers as $provider) {
            $this->addProvider($provider);
        }
    }

    /**
     * Return all providers.
     *
     * @return array
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * Magic get access.
     *
     * @param string $id
     * @return mixed  $value
     */
    public function __get($id)
    {
        return $this->offsetGet($id);
    }

    /**
     * Magic set access.
     *
     * @param string $id
     * @param mixed  $value
     */
    public function __set($id, $value)
    {
        $this->offsetSet($id, $value);
    }


    /**
     *
     */
    private function registerProviders()
    {
        foreach ($this->providers as $provider) {
            $this->register(new $provider());
        }
    }

    /**
     *
     */
    private function initializeLogger()
    {
        if (Log::hasLogger()) {
            return;
        }

        $logger = new Logger('pdd');

        if (!$this['config']['debug'] || defined('PHPUNIT_RUNNING')) {
            $logger->pushHandler(new NullHandler());
        } elseif ($this['config']['log.handler'] instanceof HandlerInterface) {
            $logger->pushHandler($this['config']['log.handler']);
        } elseif ($logFile = $this['config']['log.file']) {
            $logger->pushHandler(new StreamHandler(
                    $logFile,
                    $this['config']->get('log.level', Logger::WARNING),
                    true,
                    $this['config']->get('log.permission', null))
            );
        }

        Log::setLogger($logger);
    }

}
