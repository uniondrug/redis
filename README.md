# Redis component for uniondrug/framework

## 安装

```shell
$ cd project-home
$ composer require uniondrug/redis
$ cp vendor/uniondrug/redis/redis.php config/
```

修改 `app.php` 配置文件，加上Redis服务，服务名称`redis`

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
