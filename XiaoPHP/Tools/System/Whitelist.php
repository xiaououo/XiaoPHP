<?php

/**
 * URL白名单管理
 * Date: 2026-07-14
 * Author: 小新
 * SystemName: XiaoPHP
 */

namespace XiaoPHP\systools\Config;

class Whitelist
{
    private static $whitelist = [];

    public static function add(string $url): void
    {
        self::$whitelist[] = $url;
    }

    public static function get(): array
    {
        return self::$whitelist;
    }

    public static function check(string $url): bool
    {
        foreach (self::$whitelist as $pattern) {
            if ($pattern === $url) {
                return true;
            }
            if (strpos($pattern, '*') !== false) {
                $regex = '#^' . str_replace('\*', '.*', preg_quote($pattern, '#')) . '$#';
                if (preg_match($regex, $url)) {
                    return true;
                }
            }
        }
        return false;
    }
}