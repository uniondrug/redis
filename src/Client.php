<?php
/**
 * Client.php
 *
 */

namespace Uniondrug\Redis;

use Exception;
use Redis;
use RedisException;
use RuntimeException;

/**
 * Class Client
 *
 * @package Uniondrug\Redis
 */
class Client
{
    /**
     * @var \Redis
     */
    protected $_redis = null;

    /**
     * @var array
     */
    protected $_options = [];

    /**
     * Client constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        if (!is_array($options)) {
            $options = [];
        }

        if (!isset($options['host'])) {
            $options['host'] = '127.0.0.1';
        }

        if (!isset($options['port'])) {
            $options['port'] = 6379;
        }

        if (!isset($options['persistent'])) {
            $options['persistent'] = false;
        }

        if (!isset($options['auth'])) {
            $options['auth'] = '';
        }

        $this->_options = $options;
    }

    /**
     * Connect to server
     */
    public function _connect()
    {
        if (!isset($this->_options['host']) || !isset($this->_options['port']) || !isset($this->_options['persistent'])) {
            throw new RuntimeException('Unexpected inconsistency in options');
        }

        $redis = new Redis();
        if ($this->_options['persistent']) {
            $success = $redis->pconnect($this->_options['host'], $this->_options['port']);
        } else {
            $success = $redis->connect($this->_options['host'], $this->_options['port']);
        }
        if (!$success) {
            throw new RuntimeException('Could not connect to the Redis server');
        }

        if (isset($this->_options['auth']) && !empty($this->_options['auth'])) {
            $success = $redis->auth($this->_options['auth']);
            if (!$success) {
                throw new RuntimeException('Failed to authenticate with the Redis server');
            }
        }

        if (isset($this->_options['index']) && $this->_options['index'] > 0) {
            $success = $redis->select($this->_options['index']);
            if (!$success) {
                throw new RuntimeException('Redis server selected database failed');
            }
        }

        if (isset($this->_options['prefix']) && !empty($this->_options['prefix'])) {
            $success = $redis->setOption(Redis::OPT_PREFIX, $this->_options['prefix']);
            if (!$success) {
                throw new RuntimeException('Redis server set prefix failed');
            }
        }

        $this->_redis = $redis;
    }

    /**
     * Close connection
     */
    public function close()
    {
        if (is_object($this->_redis)) {
            $this->_redis->close();
        }
        $this->_redis = null;
    }

    /**
     * Call real redis methods
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        try {
            if (!is_object($this->_redis)) {
                $this->_connect();
            }
            $this->_redis->ping();
        } catch (RedisException $e) {
            $this->_connect();
        } catch (Exception $e) {
            throw $e;
        }
        return call_user_func_array([$this->_redis, $name], $arguments);
    }
}