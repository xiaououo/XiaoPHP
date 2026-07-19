<?php
/**
 * 辅助工具类
 * Date: 2026-07-18
 * Author: 小新
 * SystemName: XiaoPHP
 */

namespace XiaoPHP\System;

class Helper
{
    public static function findFileCaseInsensitive(string $directory, string $filename): ?string
    {
        $lowerFilename = strtolower($filename);
        $files = glob($directory . "/*.php");
        if ($files === false) {
            return null;
        }
        foreach ($files as $file) {
            if (strtolower(basename($file)) === $lowerFilename) {
                return $file;
            }
        }
        return null;
    }

    public static function findDirCaseInsensitive(string $parentDir, string $dirName): ?string
    {
        if (!is_dir($parentDir)) {
            return null;
        }
        $lowerDirName = strtolower($dirName);
        $dirs = glob($parentDir . "/*", GLOB_ONLYDIR);
        if ($dirs === false) {
            return null;
        }
        foreach ($dirs as $dir) {
            if (strtolower(basename($dir)) === $lowerDirName) {
                return $dir;
            }
        }
        return null;
    }

    public static function findMethodCaseInsensitive(object $instance, string $methodName): ?string
    {
        $lowerMethod = strtolower($methodName);
        $methods = get_class_methods($instance);
        foreach ($methods as $m) {
            if (strtolower($m) === $lowerMethod) {
                return $m;
            }
        }
        return null;
    }

    /**
     * 加载目标控制器所在目录下的所有兄弟 PHP 文件（排除目标文件本身）
     * 用于在加载控制器前先加载其基类（如 AdminBase、DocsBase）
     *
     * @param string $targetControllerFile 目标控制器文件绝对路径
     * @return void
     */
    public static function loadSiblingControllers(string $targetControllerFile): void
    {
        $dir = dirname($targetControllerFile);
        if (!is_dir($dir)) {
            return;
        }
        $targetReal = realpath($targetControllerFile);
        $files = glob($dir . "/*.php");
        if ($files === false) {
            return;
        }
        // 排序：让 *Base.php 优先加载（保证基类先于子类）
        usort($files, function ($a, $b) {
            $aIsBase = (stripos(basename($a), 'Base') !== false) ? 0 : 1;
            $bIsBase = (stripos(basename($b), 'Base') !== false) ? 0 : 1;
            return $aIsBase <=> $bIsBase;
        });
        foreach ($files as $file) {
            if (realpath($file) === $targetReal) {
                continue; // 跳过目标控制器本身
            }
            require_once $file;
        }
    }
}