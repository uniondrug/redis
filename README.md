# Redis component for uniondrug/framework

## 安装

```shell
$ cd project-home
$ composer require uniondrug/redis
$ cp vendor/uniondrug/redis/redis.php config/
```

修改 `app.php` 配置文件，加上Redis服务，服务名称`redis`

Update：2018-05-16：
    增加锁功能，服务名称 `redisLock`

```php
return [
    'default' => [
        ......
        'providers'           => [
            ......
            \Uniondrug\Redis\RedisServiceProvider::class,
        ],
    ],
];
```

## 配置

配置文件在 `redis.php` 中，配置相关参数

```php
<?php
/**
 * Redis配置文件。
 *
 * lock: 基于Redis的锁功能配置参数，参考如下：
 * <code>
 *      'lock'    => [
 *          'retryCount' => 30, // 申请锁的尝试次数
 *          'retryDelay' => 500, // 每次申请的最大时间间隔，毫秒
 *      ],
 * </code>
 *
 * options: Redis服务的配置参数，参考如下：
 * <code>
 *        'options'  => [
 *            'prefix' => '', // use custom prefix on all keys
 *            'host' => 'localhost',
 *            'port' => 6379,
 *            'auth' => 'foobared',
 *            'persistent' => false, // 持久化连接，默认不持久化
 *            'index' => 0,
 *        ],
 * </code>
 */
return [
    'default' => [
        'lock'    => [
            'retryCount' => 30, // 申请锁的尝试次数
            'retryDelay' => 500, // 每次申请的最大时间间隔，毫秒
        ],
        'options' => [
            'prefix'     => '_REDIS',
            'host'       => '127.0.0.1',
            'port'       => 6379,
            'auth'       => '',
            'persistent' => false,
            'index'      => 0,
        ],
    ],
];
```

## 使用

```php
    $data = $this->getDI()->getShared('redis')->get($key);
```

## RedisLock的使用

RedisLock是一个基于Redis实现的分布式锁，在应用内的服务名称是`redisLock`：

### 获取锁

```php
$lock = $this->redisLock->lock('my_resource_name', 1000);
```

其中第一个参数是锁定的资源，第二个参数是锁定的时间，单位是毫秒。

redisLock会尝试 `retryCount` 次数去获取锁，每次间隔在 `retryDeyay`/2 ~ `retryDeyay` 之间的毫秒数。

获取到一个锁之后，返回一个锁信息，数组格式，包括如下信息：

```php
Array
(
    [validity] => 9897.3020019531
    [resource] => my_resource_name
    [token] => 53771bfa1e775
)
```

* validity, 有效时间。毫秒数，是这个锁的有效时间。超过这个时间如果不释放锁，这个锁自动失效。
* resource, 资源名称。获取锁时的第一个参数，标识锁定的资源。
* token, 锁标识，一个随机字符串，用于安全的删除锁。

获取失败返回`false`.

### 释放锁

释放锁时，需要传入的参数就是获取的锁信息（数组）：

```php
$this->redisLock->unlock($lock);
```
