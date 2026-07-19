<?php
/**
 * 中间件配置
 * Date: 2026-07-18
 * Author: 小新
 * SystemName: XiaoPHP
 */

return [
    "storage"       => 'cache',                      // 存储方式：cache（文件缓存）| redis
    "token_key"     => 'token',                      // 令牌参数名（GET/POST 方式时使用）
    "auth_mode"     => 'bearer,get,post,cookie',     // 认证方式：bearer(请求头) | get | post | cookie
    "cookie_name"   => 'auth_token',                 // Cookie 名称（仅 auth_mode 含 cookie 时有效）
    "cookie_expire" => '7200',                       // 默认过期时间（秒）
];