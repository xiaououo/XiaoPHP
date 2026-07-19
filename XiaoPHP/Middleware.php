<?php

/**
 * 中间件类
 * Date: 2026-07-18
 * Author: 小新
 * SystemName: XiaoPHP
 */

namespace XiaoPHP\System;

use XiaoPHP\System\Config\Conf;
use XiaoPHP\System\Config\Whitelist;
use XiaoPHP\System\Cache;
use XiaoPHP\System\Tools\App\RedisTools;

class Middleware
{
    private $config;
    private $cache;
    private $redis;

    public function __construct()
    {
        $this->config = Conf::get("Middleware");
        $this->cache  = new Cache();
        $this->redis  = new RedisTools();
    }

    public function check(): void
    {
        $path = '/' . trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $path = strtolower($path);

        if (Whitelist::check($path)) {
            return;
        }

        $tokenKey = $this->config['token_key'] ?? 'token';
        $token    = $this->getToken($tokenKey);

        if (empty($token)) {
            \Error(401, '缺少认证令牌');
        }

        if ($this->config['storage'] === 'redis') {
            $valid = $this->verifyRedis($token);
        } else {
            $valid = $this->verifyCache($token);
        }

        if (!$valid) {
            \Error(401, '令牌无效或已过期');
        }
    }

    private function getToken(string $key): string
    {
        $modes = explode(',', $this->config['auth_mode'] ?? 'bearer');
        $modes = array_map('trim', $modes);

        foreach ($modes as $mode) {
            $token = $this->tryGetToken($mode, $key);
            if ($token !== '') {
                return $token;
            }
        }

        return '';
    }

    private function tryGetToken(string $mode, string $key): string
    {
        switch ($mode) {
            case 'bearer':
                $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
                if (preg_match('/^Bearer\s+(.+)$/i', $header, $m)) {
                    return trim($m[1]);
                }
                break;

            case 'get':
                if (!empty($_GET[$key])) {
                    return $_GET[$key];
                }
                break;

            case 'post':
                if (!empty($_POST[$key])) {
                    return $_POST[$key];
                }
                break;

            case 'cookie':
                $cookieName = $this->config['cookie_name'] ?? 'auth_token';
                if (!empty($_COOKIE[$cookieName])) {
                    return $_COOKIE[$cookieName];
                }
                break;
        }

        return '';
    }

    private function verifyCache(string $token): bool
    {
        $data = $this->cache->get('token_' . md5($token));
        return $data !== null;
    }

    private function verifyRedis(string $token): bool
    {
        if (!$this->redis->isAvailable()) {
            return $this->verifyCache($token);
        }
        return $this->redis->exists('token_' . md5($token));
    }

    public function setToken(string $token, $data = [], int $expire = null): void
    {
        $expire = $expire ?? (int)($this->config['cookie_expire'] ?? 7200);
        $key    = 'token_' . md5($token);

        if ($this->config['storage'] === 'redis' && $this->redis->isAvailable()) {
            $this->redis->setex($key, $expire, json_encode($data));
        } else {
            $this->cache->set($key, $data, $expire);
        }

        if ($this->hasMode('cookie')) {
            $cookieName = $this->config['cookie_name'] ?? 'auth_token';
            setcookie($cookieName, $token, [
                'expires'  => time() + $expire,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
                'secure'   => true,
            ]);
        }
    }

    public function delToken(string $token): void
    {
        $key = 'token_' . md5($token);

        if ($this->config['storage'] === 'redis' && $this->redis->isAvailable()) {
            $this->redis->del($key);
        } else {
            $this->cache->delete($key);
        }

        if ($this->hasMode('cookie')) {
            $cookieName = $this->config['cookie_name'] ?? 'auth_token';
            setcookie($cookieName, '', [
                'expires'  => time() - 3600,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
                'secure'   => true,
            ]);
        }
    }

    private function hasMode(string $mode): bool
    {
        $modes = explode(',', $this->config['auth_mode'] ?? '');
        $modes = array_map('trim', $modes);
        return in_array($mode, $modes, true);
    }
}