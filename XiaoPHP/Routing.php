<?php
/**
 * 系统路由
 * Date: 2026-07-18
 * Author: 小新
 * SystemName: XiaoPHP
 */

use XiaoPHP\System\Config\Env;
use XiaoPHP\System\Config\Route;
use XiaoPHP\System\Logs;
use XiaoPHP\System\Helper;
use XiaoPHP\System\Middleware;
use XiaoPHP\System\Container;

// 获取容器实例
$container = Container::getInstance();

// 中间件检查（白名单路径会自动跳过）
$middleware = $container->make(Middleware::class);
$middleware->check();

// 解析请求路径
$path = trim($_SERVER["REQUEST_URI"], "/");
$path = strtok($path, "?");
$pathLower = strtolower($path);
$segments = explode("/", $pathLower);
$app = preg_replace("/[^a-zA-Z0-9_-]/", "", $segments[0] ?? "");
$controller = preg_replace("/[^a-zA-Z0-9_-]/", "", $segments[1] ?? "");
$method = preg_replace("/[^a-zA-Z0-9_-]/", "", $segments[2] ?? "");

// 自定义路由（使用小写路径匹配，因为路由存储时已转换为小写）
$route = Route::find("/" . $pathLower, $_SERVER['REQUEST_METHOD']);

if ($route) {
    $routeParts = explode(":", $route);
    list($requestMethod, $classPath, $controllerName) = [
        $routeParts[0], 
        $routeParts[1], 
        $routeParts[2]
    ];
    
    // 验证请求方法
    if ($_SERVER['REQUEST_METHOD'] !== $requestMethod) {
        $container->make(Logs::class)->logs(1, 405);
        Error(405, "请求方法不允许");
    }

    // 分割控制器类和方法
    $controllerInfo = explode("/", $classPath);
    $controllerFile = $controllerInfo[0];
    $controllerMethod = $controllerInfo[1] ?? "Main";

    // 验证控制器文件（不区分大小写）
    $controllerFilePath = Helper::findFileCaseInsensitive(__DIR__ . "/../" . $controllerName, $controllerFile . ".php");
    if ($controllerFilePath === null) {
        $container->make(Logs::class)->logs(1, 404);
        Error(404, "控制器文件不存在");
    }

    // 加载同目录下的兄弟文件
    Helper::loadSiblingControllers($controllerFilePath);
    include_once $controllerFilePath;
    if (!class_exists($controllerFile)) {
        $container->make(Logs::class)->logs(1, 404);
        Error(404, "控制器类不存在");
    }

    // 通过容器实例化控制器
    $controllerInstance = $container->make($controllerFile);
    $realMethod = Helper::findMethodCaseInsensitive($controllerInstance, $controllerMethod);
    if ($realMethod !== null) {
        echo $controllerInstance->{$realMethod}();
        $container->make(Logs::class)->logs(0, 200);
    } else {
        $container->make(Logs::class)->logs(1, 404);
        Error(404, "控制器方法不存在");
    }
    
    exit(0);
}

// 自动路由
if (!empty($app)) {
    $appDir = Helper::findDirCaseInsensitive(__DIR__ . "/../App", $app);
    if ($appDir === null) {
        $container->make(Logs::class)->logs(1, 404);
        Error(404, "应用不存在");
    }

    if (empty($controller)) {
        $container->make(Logs::class)->logs(1, 400);
        Error(404, "控制器未指定");
    }

    $controllerDir = $appDir . "/Controller";
    $controllerFilePath = Helper::findFileCaseInsensitive($controllerDir, $controller . ".php");
    if ($controllerFilePath === null) {
        $container->make(Logs::class)->logs(1, 404);
        Error(404, "控制器文件不存在");
    }

    $realController = pathinfo($controllerFilePath, PATHINFO_FILENAME);

    // 加载同目录下的兄弟文件（基类如 AdminBase、DocsBase），再加载目标控制器
    Helper::loadSiblingControllers($controllerFilePath);
    include_once $controllerFilePath;
    if (!class_exists($realController)) {
        $container->make(Logs::class)->logs(1, 404);
        Error(404, "控制器类不存在");
    }

    // 通过容器实例化控制器（支持依赖注入）
    $controllerInstance = $container->make($realController);

    $methodName = !empty($method) ? $method : "Main";
    $realMethod = Helper::findMethodCaseInsensitive($controllerInstance, $methodName);

    if ($realMethod !== null) {
        echo $controllerInstance->{$realMethod}();
        $container->make(Logs::class)->logs(0, 200);
    } else {
        $container->make(Logs::class)->logs(1, 404);
        Error(404, "控制器方法不存在");
    }
} else {
    $container->make(Logs::class)->logs(1, 404);
    Error(404, "应用未指定");
}

exit(0);
?>