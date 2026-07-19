<?php
/**
 * 应用加载器
 * Date: 2026-07-18
 * Author: 小新
 * SystemName: XiaoPHP
 */

class Command
{
    public static function add()
    {
        global $argv;
        if ($argv[1] == "add") {
            while (true) {
                echo PHP_EOL . "XiaoPHP V 2.1.0" . PHP_EOL . "命令模式：创建应用-输入Exit退出" . PHP_EOL;
                echo "请输入应用名称:";
                $com = trim(fgets(STDIN));
                if ($com === "Exit") {
                    echo "退出程序。" . PHP_EOL;
                    break;
                }

                self::diradd($com);
            }
        }
    }

    public static function diradd($name)
    {
        $path = __DIR__;
        $dirs = ["Config", "Controller", "Function", "Model", "View"];
        foreach ($dirs as $dir) {
            $fullPath = $path . "/" . $name . "/" . $dir;
            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0755, true);
            }
        }

        $appRoot = $path . "/" . $name;
        $jsonFile = $appRoot . "/app.json";
        if (!file_exists($jsonFile)) {
            $defaultConfig = [
                'name'        => $name,
                'status'      => 'on',
                'description' => 'status值off为关闭应用文件加载,on启用应用文件加载',
                'version'     => '1.0.0'
            ];
            file_put_contents($jsonFile, json_encode($defaultConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            echo "已生成默认配置文件: " . $jsonFile . PHP_EOL;
        } else {
            echo "配置文件已存在，保留原有配置。" . PHP_EOL;
        }
    }
}

class Loading
{
    public static function run()
    {
        $scanned = array_map('basename', glob(__DIR__ . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR));
        $predefined = ["Config", "Function", "Model"];
        $allPhpFiles = [];

        foreach ($scanned as $appDir) {
            $appPath = __DIR__ . DIRECTORY_SEPARATOR . $appDir;
            $configFile = $appPath . DIRECTORY_SEPARATOR . 'app.json';
            $shouldLoad = true;

            if (file_exists($configFile)) {
                $config = json_decode(file_get_contents($configFile), true);
                if (isset($config['status']) && $config['status'] === 'off') {
                    $shouldLoad = false;
                }
            }

            if ($shouldLoad) {
                foreach ($predefined as $subDir) {
                    $subPath = $appPath . DIRECTORY_SEPARATOR . $subDir;
                    if (!is_dir($subPath)) continue;

                    $iterator = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($subPath, RecursiveDirectoryIterator::SKIP_DOTS)
                    );
                    foreach ($iterator as $fileInfo) {
                        if ($fileInfo->isFile() && $fileInfo->getExtension() === 'php') {
                            $allPhpFiles[] = $fileInfo->getRealPath();
                        }
                    }
                }
            }
        }

        foreach ($allPhpFiles as $file) {
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }
}

if (isset($argv[1])) {
    Command::add();
} else {
    Loading::run();
}