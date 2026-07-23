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

        public static function add($method,$url,$controller,$bootstrap, $action = 'Main'): void
            {
                $url = strtolower($url);
                $method = strtoupper($method);
                if (!isset(self::$routes[$url])) {
                    self::$routes[$url] = [];
                }
                self::$routes[$url][$method] = $controller."/".$action.":App/".$bootstrap."/Controller";
            }

        public static function get():array
            {
                return self::$routes;
            }

        public static function find(string $url, string $method = 'GET'): ?string
            {
                $url = strtolower($url);
                $method = strtoupper($method);
                if (isset(self::$routes[$url][$method])) {
                    return $method.":".self::$routes[$url][$method];
                }
                return null;
            }
    }