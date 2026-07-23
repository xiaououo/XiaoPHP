<?php
/**
 * 白名单路由
 * Date: 2026-07-14
 * Author: 小新
 * SystemName: XiaoPHP
 */

use XiaoPHP\System\Config\Whitelist;

// 公开路由白名单：以下路径不需要 Token 认证
// 注意：不要使用 "/*" 通配符，否则中间件认证将完全失效
Whitelist::add("/");
Whitelist::add("/login");
Whitelist::add("/Login/DoLogin");
Whitelist::add("/Logout");
Whitelist::add("/register");
Whitelist::add("/sitemap.xml");
Whitelist::add("/robots.txt");
