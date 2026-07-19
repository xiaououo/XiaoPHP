<?php
/**
 * 环境变量加载类
 * Date: 2026-07-18
 * Author: 小新
 * SystemName: XiaoPHP
 */

namespace XiaoPHP\System\Config;

class Env
{
    private static $loadedEnv = [];
    private static $loadedFiles = [];

    public static function load($file = null, $key = null): ?string
    {
        $fileName = $file ? ".env" . $file : ".env";
        $filePath = SYS_PATH . "/../" . $fileName;

        if (!file_exists($filePath)) {
            return null;
        }

        $cacheKey = md5($filePath);
        if (isset(self::$loadedFiles[$cacheKey])) {
            $env = self::$loadedFiles[$cacheKey];
        } else {
            $env = [];
            $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                $line = trim($line);
                if (strpos($line, '#') === 0) continue;
                if (strpos($line, '=') === false) continue;

                list($envKey, $envValue) = explode('=', $line, 2);
                $envKey = trim($envKey);
                $envValue = trim($envValue, " \t\n\r\0\x0B\"'");

                if (strtolower($envValue) === 'true') $envValue = true;
                elseif (strtolower($envValue) === 'false') $envValue = false;
                elseif (strtolower($envValue) === 'null') $envValue = null;
                elseif (is_numeric($envValue)) $envValue = strpos($envValue, '.') ? (float)$envValue : (int)$envValue;

                $_ENV[$envKey] = $envValue;
                if (function_exists('putenv')) {
                    putenv("$envKey=$envValue");
                }
                $env[$envKey] = $envValue;
            }

            self::$loadedFiles[$cacheKey] = $env;
            self::$loadedEnv = array_merge(self::$loadedEnv, $env);
        }


        if ($key !== null) {
            if (isset($env[$key])) {
                $value = $env[$key];
                if ($value === '' || $value === null || $value === false) {
                    return null;
                }
                return is_scalar($value) ? (string)$value : null;
            }
            return null;
        }

        return true;
    }


    public static function get($key, $default = '')
    {
        if (isset(self::$loadedEnv[$key])) {
            $value = self::$loadedEnv[$key];
            if ($value === '' || $value === null || $value === false) {
                return $default;
            }
            return is_scalar($value) ? (string)$value : $default;
        }
        return $default;
    }
}