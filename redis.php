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