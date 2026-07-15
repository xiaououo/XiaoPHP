<?php
/**
 * 系统依赖加载
 * Date: 2026-07-14
 * Author: 小新
 * SystemName: XiaoPHP
 */
declare(strict_types=1);

function loadSystemFiles(string $baseDir): void
{
    $files = array_merge(
        [$baseDir . "/../vendor/autoload.php"],
        glob($baseDir . "/Tools/System/*.php") ?: [],
        glob($baseDir . "/Tools/Code/*.php") ?: [],
        glob($baseDir . "/Error/*.php") ?: [],
        glob($baseDir . "/../Route/Route.php") ?: [],
        [$baseDir . "/../Whitelist/Whitelist.php"],
        glob($baseDir . "/../App/Func/*.php") ?: [],
        [$baseDir . "/debug.php"],
        [$baseDir . "/Middleware.php"],
        [$baseDir . "/Routing.php"]
    );
    register_shutdown_function(function () {
        $error = error_get_last();
        if (
            $error &&
            in_array($error["type"], [
                E_ERROR,
                E_PARSE,
                E_CORE_ERROR,
                E_COMPILE_ERROR,
            ])
        ) {
            $exception = new ErrorException(
                $error["message"],
                0,
                $error["type"],
                $error["file"],
                $error["line"]
            );
            displayDebugInfo($exception);
        }
    });

    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            return;
        }
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    });
    set_exception_handler(function ($exception) {
        displayDebugInfo($exception);
    });
    array_walk($files, function ($file) {
        if (file_exists($file)) {
            require_once $file;
        }
    });
}

loadSystemFiles(__DIR__);