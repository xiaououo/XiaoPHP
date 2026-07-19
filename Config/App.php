<?php
/**
 * 应用配置
 * Date: 2026-07-18
 * Author: 小新
 * SystemName: XiaoPHP
 */

use XiaoPHP\System\Config\Env;

return [
    "debug" => Env::Load(null, "DEBUG") ?? 'false',  // 调试模式：true开启 | false关闭
    "error" => 'html',                                 // 错误响应格式：html | json
];