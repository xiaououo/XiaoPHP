<?php
/**
 * 系统配置
 * Date: 2026-07-18
 * Author: 小新
 * SystemName: XiaoPHP
 */

namespace XiaoPHP\System\Config;

class Conf
{
    private static $cache = [];

    public static function get($conf): ?array
    {
        if (isset(self::$cache[$conf])) {
            return self::$cache[$conf];
        }
        $filePath = SYS_PATH . "/../Config/" . $conf . ".php";
        if (file_exists($filePath)) {
            self::$cache[$conf] = require $filePath;
            return self::$cache[$conf];
        } else {
            return null;
        }
    }

    public static function clearCache(string $conf = null): void
    {
        if ($conf !== null) {
            unset(self::$cache[$conf]);
        } else {
            self::$cache = [];
        }
    }
}