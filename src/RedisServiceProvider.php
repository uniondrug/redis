<?php
namespace Uniondrug\Redis;

use Phalcon\Config;
use Phalcon\Di\ServiceProviderInterface;

/**
 * @package Uniondrug\Redis
 */
class RedisServiceProvider implements ServiceProviderInterface
{
    /**
     * @param \Phalcon\DiInterface $di
     */
    public function register(\Phalcon\DiInterface $di)
    {
        /**
         * 调整配置兼容
         * @author wsfuyibing <webserach@163.com>
         * @date   2019-02-21
         * @var Config $config
         */
        $config = config()->path('redis');
        // 1. Redis对象
        $optConfig = isset($config->options) && $config->options instanceof Config ? $config->options->toArray() : $config->toArray();
        $di->set('redis', function() use ($optConfig){
            if (!extension_loaded('redis')) {
                throw new \RuntimeException("Extension redis MUST be installed and loaded");
            }
            return new Client($optConfig);
        });
        // 2. Redis锁
        $lockConfig = isset($config->lock) && $config->lock instanceof Config ? $config->lock->toArray() : [];
        $di->setShared('redisLock', function() use ($lockConfig){
            $redis = app()->getShared('redis');
            if (!$redis) {
                throw new \RuntimeException("Cannot get redis instance");
            }
            $retryDelay = isset($lockConfig['retryDelay']) ? (int) $lockConfig['retryDelay'] : 200;
            $retryCount = isset($lockConfig['retryCount']) ? (int) $lockConfig['retryCount'] : 3;
            return new RedisLock([$redis], $retryDelay, $retryCount);
        });
    }
}
