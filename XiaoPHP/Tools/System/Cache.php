<?php

namespace XiaoPHP\systools\System;

use XiaoPHP\systools\Config\Conf;

class Cache
{
    private $dir;
    private $expire;

    public function __construct()
    {
        $config = Conf::get("Cache");
        $this->dir = rtrim($config['dir'] ?? __DIR__ . '/../../../Temp/Cache/', '/') . '/';
        $this->expire = (int)($config['expire'] ?? 3600);
        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0755, true);
        }
    }

    public function set($key, $data, $expire = null)
    {
        $expire = $expire ?? $this->expire;
        $file = $this->dir . md5($key) . '.txt';
        $content = serialize([
            'expire' => time() + $expire,
            'data'   => $data,
        ]);
        return file_put_contents($file, $content, LOCK_EX);
    }

    public function get($key)
    {
        $file = $this->dir . md5($key) . '.txt';
        if (!file_exists($file)) {
            return null;
        }

        $cache = unserialize(file_get_contents($file));
        if ($cache['expire'] < time()) {
            unlink($file);
            return null;
        }
        return $cache['data'];
    }

    public function delete($key)
    {
        $file = $this->dir . md5($key) . '.txt';
        if (file_exists($file)) {
            unlink($file);
        }
    }

    public function clear()
    {
        $files = glob($this->dir . '*.txt');
        foreach ($files as $file) {
            unlink($file);
        }
    }
}