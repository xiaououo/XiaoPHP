<?php
/**
 * 系统依赖加载
 * Date: 2026-07-18
 * Author: 小新
 * SystemName: XiaoPHP
 */
declare(strict_types=1);

function loadSystemFiles(string $baseDir): void
{
    $allPhpFiles = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseDir."/System/", RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $allPhpFiles[] = $file->getRealPath();
        }
    }

    $files = array_merge(
        [$baseDir . '/../vendor/autoload.php'],
        $allPhpFiles,
         glob($baseDir . '/../Config/*.php') ?: [],
          [$baseDir . '/../Whitelist/Whitelist.php'],
        [$baseDir . '/System/Error/error.php'],
        [$baseDir . '/Middleware.php'],
        [$baseDir . '/debug.php'],
        [$baseDir . '/install/Installer.php'],
        [$baseDir . '/Import/MarkdownImporter.php'],
        [$baseDir . '/Import/DocxImporter.php'],
        [$baseDir . '/../App/Loading.php'],
        [$baseDir . '/../Route/Route.php'],
        [$baseDir . '/Routing.php']
    );

    $files = array_unique($files);

    foreach ($files as $file) {
        if (file_exists($file)) {
            require_once $file;
        }
    }
}

loadSystemFiles(__DIR__);