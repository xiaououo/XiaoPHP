<?php
/**
 * 系统路由
 * Date: 2026-07-14
 * Author: 小新
 * SystemName: XiaoPHP
 */

use XiaoPHP\systools\Config\Env;
use XiaoPHP\systools\Config\Route;
use XiaoPHP\systools\System\Logs;

// 解析请求路径
$path = trim($_SERVER["REQUEST_URI"], "/");
$segments = explode("/", $path);
$controller = preg_replace("/[^a-zA-Z0-9_-]/", "", $segments[0] ?? "");
$method = preg_replace("/[^a-zA-Z0-9_-]/", "", $segments[1] ?? "");

// 自定义路由
$route = Route::find("/" . $path);

if ($route) {
    $routeParts = explode(":", $route);
    list($requestMethod, $classPath, $controllerName) = [
        $routeParts[0], 
        $routeParts[1], 
        $routeParts[2]
    ];
    
    // 验证请求方法
    if ($_SERVER['REQUEST_METHOD'] !== $requestMethod) {
        (new Logs())->logs(1, 405);
        Error(405, "请求方法不允许");
    }

    // 分割控制器类和方法
    $controllerInfo = explode("/", $classPath);
    $controllerFile = $controllerInfo[0];
    $controllerMethod = $controllerInfo[1] ?? "Main";

    // 验证控制器文件
    $controllerFilePath = __DIR__ . "/../" . $controllerName . "/" . $controllerFile . ".php";
    if (!file_exists($controllerFilePath)) {
        (new Logs())->logs(1, 404);
        Error(404, "控制器文件不存在");
    }

    // 加载控制器
    include_once $controllerFilePath;
    if (!class_exists($controllerFile)) {
        (new Logs())->logs(1, 404);
        Error(404, "控制器类不存在");
    }

    // 实例化控制器并执行方法
    $controllerInstance = new $controllerFile();
    if (method_exists($controllerInstance, $controllerMethod)) {
        echo $controllerInstance->{$controllerMethod}();
        (new Logs())->logs(0, 200);
    } else {
        (new Logs())->logs(1, 404);
        Error(404, "控制器方法不存在");
    }
    
    exit(0);
}

// 自动路由
if (!empty($controller)) {
    // 验证控制器文件
    $controllerFilePath = __DIR__ . "/../App/Run/" . $controller . ".php";
    if (!file_exists($controllerFilePath)) {
        (new Logs())->logs(1, 404);
        Error(404, "控制器文件不存在");
    }

    // 加载控制器
    include_once $controllerFilePath;
    if (!class_exists($controller)) {
        (new Logs())->logs(1, 404);
        Error(404, "控制器类不存在");
    }

    // 实例化控制器
    $controllerInstance = new $controller();

    // 确定要执行的方法
    $methodName = !empty($method) ? $method : "Main";

    // 验证并执行方法
    if (method_exists($controllerInstance, $methodName)) {
        echo $controllerInstance->{$methodName}();
        (new Logs())->logs(0, 200);
    } else {
        (new Logs())->logs(1, 404);
        Error(404, "控制器方法不存在");
    }
} else {
    (new Logs())->logs(1, 400);
    Error(400, "控制器未指定");
}

exit(0);
?>