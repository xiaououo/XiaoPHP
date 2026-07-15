<?php

/**
 * 系统日志记录
 * Date: 2026-07-14
 * Author: 小新
 * SystemName: XiaoPHP
 */

namespace XiaoPHP\systools\System;

use XiaoPHP\systools\Config\Conf;

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
        $logDir = __DIR__ . "/../../../logs/" . $type . "/";
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $line =
            $type . " " .
            date("Y-m-d H:i:s") .
            "--[" . $this->clientIp() . "]:" .
            urldecode(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH)) .
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