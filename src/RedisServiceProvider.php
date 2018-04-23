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

                $options = $this->getConfig()->path('redis.options');
                if (!$options instanceof Config) {
                    throw new \RuntimeException("Cache option cannot be empty");
                }

                return new Client($options->toArray());
            }
        );
    }
}
