<?php
/**
 * 错误处理注册
 * Date: 2026-07-18
 * Author: 小新
 * SystemName: XiaoPHP
 */

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


