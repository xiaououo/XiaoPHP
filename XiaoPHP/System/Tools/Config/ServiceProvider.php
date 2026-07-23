<?php
/**
 * 服务提供者
 * Date: 2026-07-23
 * Author: 小新
 * SystemName: XiaoPHP
 */
namespace XiaoPHP\System\Config;

use XiaoPHP\System\Container;
use XiaoPHP\System\Tools\App\MysqlTools;
use XiaoPHP\System\Tools\App\RedisTools;
use XiaoPHP\System\Cache;
use XiaoPHP\System\Logs;
use XiaoPHP\System\Middleware;
use XiaoPHP\System\Validate;

class ServiceProvider
{
    /**
     * 注册核心服务到容器
     */
    public static function register(): void
    {
        $container = Container::getInstance();

        // 数据库工具 - 单例
        $container->singleton(MysqlTools::class, function () {
            return new MysqlTools();
        });

        // Redis工具 - 单例
        $container->singleton(RedisTools::class, function () {
            return new RedisTools();
        });

        // 缓存服务 - 单例
        $container->singleton(Cache::class, function () {
            return new Cache();
        });

        // 日志服务 - 单例
        $container->singleton(Logs::class, function () {
            return new Logs();
        });

        // 中间件 - 单例
        $container->singleton(Middleware::class, function () {
            return new Middleware();
        });

        // 验证工具 - 单例
        $container->singleton(Validate::class, function () {
            return new Validate();
        });

        // 添加别名（方便使用）
        $container->bind('db', function ($c) {
            return $c->make(MysqlTools::class);
        });
        $container->bind('redis', function ($c) {
            return $c->make(RedisTools::class);
        });
        $container->bind('cache', function ($c) {
            return $c->make(Cache::class);
        });
        $container->bind('logs', function ($c) {
            return $c->make(Logs::class);
        });
        $container->bind('middleware', function ($c) {
            return $c->make(Middleware::class);
        });
        $container->bind('validate', function ($c) {
            return $c->make(Validate::class);
        });
    }
}
