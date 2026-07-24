<?php
/**
 * 系统依赖加载
 * Date: 2026-07-18
 * Author: 小新
 * SystemName: XiaoPHP
 */
declare(strict_types=1);

/**
 * 自动加载器：根据类名自动加载对应的文件
 */
spl_autoload_register(function (string $class): void {
    $baseDir = __DIR__;
    
    // 类名到文件路径的映射规则
    $classPath = str_replace('\\', '/', $class);
    
    // 系统类（XiaoPHP\System\*）
    $systemFile = $baseDir . '/' . $classPath . '.php';
    if (file_exists($systemFile)) {
        require_once $systemFile;
        return;
    }
    
    // 配置类（XiaoPHP\System\Config\*）
    $configFile = $baseDir . '/System/Tools/Config/' . basename($classPath) . '.php';
    if (file_exists($configFile)) {
        require_once $configFile;
        return;
    }
    
    // 工具类（XiaoPHP\System\Tools\*）
    $toolsFile = $baseDir . '/System/Tools/' . basename($classPath) . '.php';
    if (file_exists($toolsFile)) {
        require_once $toolsFile;
        return;
    }
    
    // 应用控制器（App\*）
    $appFile = $baseDir . '/../' . $classPath . '.php';
    if (file_exists($appFile)) {
        require_once $appFile;
        return;
    }
});

function loadSystemFiles(string $baseDir): void
{
    // 递归扫描 System 目录下所有 PHP 文件
    $allPhpFiles = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseDir."/System/", RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $realPath = $file->getRealPath();
            $allPhpFiles[] = $realPath;
        }
    }

    // 合并其他必要文件
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
        [$baseDir . '/start.php']
    );

    // 去重并加载
    $files = array_unique($files);

    foreach ($files as $file) {
        if (file_exists($file)) {
            require_once $file;
        }
    }
}

// 加载系统文件
loadSystemFiles(__DIR__);

// 注册核心服务到容器
\XiaoPHP\System\Config\ServiceProvider::register();

// 加载路由入口
require_once __DIR__ . '/Routing.php';
