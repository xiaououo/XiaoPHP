<?php
/**
 * Redis操作封装类
 * Date: 2026-07-18
 * Author: 小新
 * SystemName: XiaoPHP
 */

namespace XiaoPHP\System\Tools\App;

use XiaoPHP\System\Config\Conf;

class RedisTools
{
    private $redis;
    private $pipeline = false;

    function __construct()
    {
        try {
            if (!class_exists('\Redis')) {
                $this->redis = null;
                return;
            }
            $conf = Conf::get("Redis");
            $this->redis = new \Redis();
            $host = $conf["host"] ?? '127.0.0.1';
            $port = (int)($conf["port"] ?? 6379);
            $this->redis->connect($host, $port, 2.5);
            if (!empty($conf["password"])) {
                $this->redis->auth($conf["password"]);
            }
        } catch (\Throwable $e) {
            $this->redis = null;
        }
    }

    function pipeline($mode = null)
    {
        if ($this->redis) {
            $mode = $mode ?? 1;
            $this->redis->multi($mode);
            $this->pipeline = true;
        }
        return $this;
    }

    function __call($method, $args)
    {
        if (!$this->redis) {
            return $this;
        }
        return $this->redis->$method(...$args);
    }

    function exec()
    {
        if (!$this->redis) {
            return [];
        }
        $this->pipeline = false;
        $result = $this->redis->exec();
        return $result ?: [];
    }

    function isAvailable()
    {
        return $this->redis !== null;
    }

    function __destruct()
    {
        if ($this->redis) {
            $this->redis->close();
        }
    }
}