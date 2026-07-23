<?php

/**
     * 系统入口
     * Author: 小新
     * Version: XiaoPHP 2.1.1
 */
    ob_start();
    $sessPath = session_save_path() ?: sys_get_temp_dir();
    $testFile = rtrim($sessPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.sess_test_' . uniqid();
    $canWrite = @file_put_contents($testFile, 'test') !== false;
    if ($canWrite) {
        @unlink($testFile);
    }
    if (!$canWrite) {
        $fallback = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'sessions';
        if (!is_dir($fallback)) {
            @mkdir($fallback, 0755, true);
        }
        session_save_path($fallback);
        // 设置目录权限
        @chmod($fallback, 0755);
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    define("SYS_PATH",__DIR__."/../XiaoPHP");
    include_once SYS_PATH."/console.php";