<?php

/**
 * 系统日志记录
 * Date: 2026-07-18
 * Author: 小新
 * SystemName: XiaoPHP
 */

namespace XiaoPHP\System;

use XiaoPHP\System\Config\Conf;

class Logs
{
    private function clientIp(): string
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    private function writeLog(string $type, int $code): void
    {
        $logDir = __DIR__ . "/../../../../logs/" . $type . "/";
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        // 获取并清理请求路径：移除换行符和控制字符，防止日志注入
        $rawPath = $_SERVER["REQUEST_URI"] ?? "/";
        $cleanPath = urldecode(parse_url($rawPath, PHP_URL_PATH));
        $cleanPath = preg_replace('/[\x00-\x1f\x7f]/', '', $cleanPath);
        $cleanPath = str_replace(["
", "\n"], '', $cleanPath);

        $line =
            $type . " " .
            date("Y-m-d H:i:s") .
            "--[" . $this->clientIp() . "]:" .
            $cleanPath .
            "-code:" . $code;

        file_put_contents(
            $logDir . date("Y-m-d") . ".logs",
            $line . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }

    function logs($int, $code = 0)
    {
        $config = Conf::get("Logs");
        $success = filter_var($config['success'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
        $error   = filter_var($config['error'] ?? 'true', FILTER_VALIDATE_BOOLEAN);

        if ($int == 0 && $success) {
            $this->writeLog('success', $code);
        }

        if ($int == 1 && $error) {
            $this->writeLog('error', $code);
        }
    }
}