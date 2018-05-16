<?php

namespace Uniondrug\Redis;

use Phalcon\Config;
use Phalcon\Di\ServiceProviderInterface;

class RedisServiceProvider implements ServiceProviderInterface
{
    public function register(\Phalcon\DiInterface $di)
    {
        $di->set(
            'redis',
            function () {
                if (!extension_loaded('redis')) {
                    throw new \RuntimeException("Extension redis MUST be installed and loaded");
                }

                $options = config()->path('redis.options');
                if (!$options instanceof Config) {
                    throw new \RuntimeException("Redis options cannot be empty");
                }

                return new Client($options->toArray());
            }
        );

        $di->setShared(
            'redisLock',
            function () {
                $redis = app()->getShared('redis');
                if (!$redis) {
                    throw new \RuntimeException("Cannot get redis instance");
                }
                $retryDelay = config()->path('redis.lock.retryDelay', 200);
                $retryCount = config()->path('redis.lock.retryCount', 3);
                return new RedisLock([$redis], $retryDelay, $retryCount);
            }
        );
    }
}
