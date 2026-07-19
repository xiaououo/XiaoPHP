<?php
/**
 * 路由注册类
 * Date: 2026-07-18
 * Author: 小新
 * SystemName: XiaoPHP
 */

namespace XiaoPHP\System\Config;
    Class Route
    {
        private static $routes = [];

        public static function add($method,$url,$controller,$bootstrap ): void
            {
                self::$routes[strtolower($url)] = $method.":".$controller.":"."App/".$bootstrap."/Controller";
            }

        public static function get():array
            {
                return self::$routes;
            }

        public static function find(string $url): ?string
            {
                return self::$routes[strtolower($url)] ?? null;
            }
    }