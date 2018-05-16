<?php
/**
 * RedisLock - 基于Redis的锁。
 *
 * @see https://github.com/ronnylt/redlock-php
 */

namespace Uniondrug\Redis;

class RedisLock
{
    private $retryDelay;
    private $retryCount;
    private $clockDriftFactor = 0.01;
    private $quorum;
    private $instances = [];

    /**
     * RedisLock constructor.
     *
     * @param array $instances Redis实例列表
     * @param int   $retryDelay
     * @param int   $retryCount
     */
    function __construct(array $instances, $retryDelay = 200, $retryCount = 3)
    {
        $this->instances = $instances;
        $this->retryDelay = $retryDelay;
        $this->retryCount = $retryCount;
        $this->quorum = min(count($instances), (count($instances) / 2 + 1));
    }

    /**
     * @param $resource
     * @param $ttl
     *
     * @return array|bool
     */
    public function lock($resource, $ttl)
    {
        $token = uniqid();
        $retry = $this->retryCount;
        do {
            $n = 0;
            $startTime = microtime(true) * 1000;
            foreach ($this->instances as $instance) {
                if ($this->lockInstance($instance, $resource, $token, $ttl)) {
                    $n++;
                }
            }
            # Add 2 milliseconds to the drift to account for Redis expires
            # precision, which is 1 millisecond, plus 1 millisecond min drift
            # for small TTLs.
            $drift = ($ttl * $this->clockDriftFactor) + 2;
            $validityTime = $ttl - (microtime(true) * 1000 - $startTime) - $drift;
            if ($n >= $this->quorum && $validityTime > 0) {
                return [
                    'validity' => $validityTime,
                    'resource' => $resource,
                    'token'    => $token,
                ];
            } else {
                foreach ($this->instances as $instance) {
                    $this->unlockInstance($instance, $resource, $token);
                }
            }
            // Wait a random delay before to retry
            $delay = mt_rand(floor($this->retryDelay / 2), $this->retryDelay);
            usleep($delay * 1000);
            $retry--;
        } while ($retry > 0);

        return false;
    }

    /**
     * @param array $lock
     */
    public function unlock(array $lock)
    {
        $resource = $lock['resource'];
        $token = $lock['token'];
        foreach ($this->instances as $instance) {
            $this->unlockInstance($instance, $resource, $token);
        }
    }

    /**
     * @param \Redis $instance
     * @param        $resource
     * @param        $token
     * @param        $ttl
     *
     * @return mixed
     */
    private function lockInstance($instance, $resource, $token, $ttl)
    {
        return $instance->set($resource, $token, ['NX', 'PX' => $ttl]);
    }

    /**
     * @param \Redis $instance
     * @param        $resource
     * @param        $token
     *
     * @return mixed
     */
    private function unlockInstance($instance, $resource, $token)
    {
        $script = '
            if redis.call("GET", KEYS[1]) == ARGV[1] then
                return redis.call("DEL", KEYS[1])
            else
                return 0
            end
        ';

        return $instance->eval($script, [$resource, $token], 1);
    }
}
