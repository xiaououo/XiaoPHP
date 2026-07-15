<?php
use XiaoPHP\systools\Config\Conf;

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
                "data" => ["msg" => $info]
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