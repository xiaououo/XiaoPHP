<?php
/**
 * Redis配置
 * Date: 2026-07-18
 * Author: 小新
 * SystemName: XiaoPHP
 */

use XiaoPHP\System\Config\Env;

return [
    "host"     => Env::Load(null, "REDIS_HOST") ?? '127.0.0.1',  // Redis 主机地址
    "port"     => Env::Load(null, "REDIS_PORT") ?? '6379',       // Redis 端口
    "password" => Env::Load(null, "REDIS_PASSWORD") ?? '',       // Redis 密码（空为无密码）
];