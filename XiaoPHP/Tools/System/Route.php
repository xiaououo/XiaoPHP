<?php

/**
 * 系统路由
 * Date: 2026-07-14
 * Author: 小新
 * SystemName: XiaoPHP
 */

    namespace XiaoPHP\systools\Config;
    Class Route
    {
        private static $routes = [];

        public static function add($method,$url,$controller,$bootstrap ): void
            {
                self::$routes[$url] = $method.":".$controller.":".$bootstrap;
            }

        public static function get():array
            {
                return self::$routes;
            }

        public static function find(string $url): ?string
            {
                return self::$routes[$url] ?? null;
            }
    }