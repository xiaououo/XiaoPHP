<?php
/**
 * 白名单路由
 * Date: 2026-07-14
 * Author: 小新
 * SystemName: XiaoPHP
 */

use XiaoPHP\System\Config\Whitelist;

// 首页与默认入口
// 白名单路由：允许所有路由访问
Whitelist::add("/");