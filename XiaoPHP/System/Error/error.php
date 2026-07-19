<?php
/**
 * 全局错误处理函数
 * Date: 2026-07-18
 * Author: 小新
 * SystemName: XiaoPHP
 */

use XiaoPHP\System\Config\Conf;

function Error($code, $info)
{
    $config = Conf::get("App");
    $format = $config["error"] ?? 'json';

    if ($format == "html") {
        $htmlFile = __DIR__ . "/" . $code . ".html";
        if (file_exists($htmlFile)) {
            include_once $htmlFile;
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                "code" => $code,
                "date" => date('Y-m-d H:i:s'),
                "data" => ["msg" => $info,"info"=>"错误文件找不到，默认json格式"]
            ]);
        }
        exit();
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            "code" => $code,
            "date" => date('Y-m-d H:i:s'),
            "data" => ["msg" => $info]
        ]);
        exit();
    }
}