<?php
/**
 * 数据库配置
 * Date: 2026-07-18
 * Author: 小新
 * SystemName: XiaoPHP
 */

use XiaoPHP\System\Config\Env;

return [
    "host"     => Env::Load(null, "DB_HOST") ?? 'localhost',  // 数据库主机地址
    "port"     => Env::Load(null, "DB_PORT") ?? '3306',       // 数据库端口
    "user"     => Env::Load(null, "DB_USER") ?? 'root',       // 数据库用户名
    "password" => Env::Load(null, "DB_PASSWORD") ?? '123456', // 数据库密码
    "dbname"   => Env::Load(null, "DB_NAME") ?? 'xiaophp',    // 数据库名称
];