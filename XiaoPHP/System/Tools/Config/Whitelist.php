<?php

/**
 * URL白名单管理
 * Date: 2026-07-18
 * Author: 小新
 * SystemName: XiaoPHP
 */

namespace XiaoPHP\System\Config;

class Whitelist
{
    private static $whitelist = [];

    public static function add(string $url): void
    {
        self::$whitelist[] = strtolower($url);
    }

    public static function get(): array
    {
        return self::$whitelist;
    }

    public static function remove(string $url): void
    {
        $url = strtolower($url);
        $key = array_search($url, self::$whitelist);
        if ($key !== false) {
            unset(self::$whitelist[$key]);
            self::$whitelist = array_values(self::$whitelist);
        }
    }

    public static function clear(): void
    {
        self::$whitelist = [];
    }

    public static function exists(string $url): bool
    {
        return in_array(strtolower($url), self::$whitelist);
    }

    public static function check(string $url): bool
    {
        $url = strtolower($url);
        foreach (self::$whitelist as $pattern) {
            if ($pattern === $url) {
                return true;
            }
            if (strpos($pattern, '*') !== false) {
                $regexPattern = preg_quote($pattern, '#');
                $regexPattern = str_replace('\*', '.*', $regexPattern);
                $regex = '#^' . $regexPattern . '$#i';
                if (preg_match($regex, $url)) {
                    return true;
                }
            }
        }
        return false;
    }
}