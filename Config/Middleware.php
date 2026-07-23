<?php
/**
 * 中间件配置
 * Date: 2026-07-18
 * Author: 小新
 * SystemName: XiaoPHP
 */

return [
    "storage"       => 'cache',                      // 存储方式：cache（文件缓存）| redis
    "token_key"     => 'token',                      // 令牌参数名（POST/COOKIE 方式时使用）
    "auth_mode"     => 'bearer,post,cookie',         // 认证方式：bearer(请求头) | post | cookie
                                                     // 注意：不建议使用 get 方式，Token 会出现在 URL 中
                                                     // 导致 Referer 泄漏、浏览器历史记录泄漏等风险
    "cookie_name"   => 'auth_token',                 // Cookie 名称（仅 auth_mode 含 cookie 时有效）
    "cookie_expire" => '7200',                       // 默认过期时间（秒）
];
