<?php
/**
 * 数据验证类
 * Date: 2026-07-18
 * Author: 小新
 * SystemName: XiaoPHP
 */

namespace XiaoPHP\System;

class Validate
{
    // 验证手机号
    public static function phone($data)
    {
        if (preg_match("/^1[3-9]\d{9}$/", $data)) {
            return $data;
        }
        return false;
    }
    // 验证邮箱
    public static function email($data)
    {
        if (filter_var($data, FILTER_VALIDATE_EMAIL)) {
            return $data;
        }
        return false;
    }


    public static function username($data)
    {
        if (preg_match("/^[\w\x{4e00}-\x{9fa5}]{2,20}$/u", $data)) {
            return $data;
        }
        return false;
    }

    /**
     * 验证密码：6~20 位，至少包含字母和数字（可自定义强度）
     */
    public static function password($data)
    {
        // 至少一个字母、一个数字，长度 6~20
        if (preg_match("/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,20}$/", $data)) {
            return $data;
        }
        return false;
    }

    /**
     * 验证中国大陆身份证号（18 位，最后一位可能是 X）
     */
    public static function idCard($data)
    {
        $data = strtoupper($data);
        if (preg_match("/^\d{17}[\dX]$/", $data)) {
            return $data;
        }
        return false;
    }

    /**
     * 验证 URL（支持 http/https）
     */
    public static function url($data)
    {
        if (filter_var($data, FILTER_VALIDATE_URL)) {
            return $data;
        }
        return false;
    }

    /**
     * 验证 IPv4 或 IPv6 地址
     */
    public static function ip($data)
    {
        if (filter_var($data, FILTER_VALIDATE_IP)) {
            return $data;
        }
        return false;
    }

    /**
     * 验证日期（格式 YYYY-MM-DD）
     */
    public static function date($data)
    {
        $d = \DateTime::createFromFormat('Y-m-d', $data);
        if ($d && $d->format('Y-m-d') === $data) {
            return $data;
        }
        return false;
    }

    /**
     * 验证是否为纯数字（整数或浮点数）
     */
    public static function numeric($data)
    {
        if (is_numeric($data)) {
            return $data;
        }
        return false;
    }

    /**
     * 验证数字是否在指定范围内（闭区间）
     * @param int|float $min
     * @param int|float $max
     */
    public static function between($data, $min, $max)
    {
        if (is_numeric($data) && $data >= $min && $data <= $max) {
            return $data;
        }
        return false;
    }

    /**
     * 验证字符串长度（UTF-8 中文字符算 1 个长度）
     * @param int $min
     * @param int $max
     */
    public static function length($data, $min, $max)
    {
        $len = mb_strlen($data, 'UTF-8');
        if ($len >= $min && $len <= $max) {
            return $data;
        }
        return false;
    }

    /**
     * 验证是否为合法的时间（HH:MM:SS 或 HH:MM）
     */
    public static function time($data)
    {
        $pattern = '/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/';
        if (preg_match($pattern, $data)) {
            return $data;
        }
        return false;
    }

    /**
     * 验证是否为合法的邮政编码（中国 6 位数字）
     */
    public static function postcode($data)
    {
        if (preg_match("/^\d{6}$/", $data)) {
            return $data;
        }
        return false;
    }

    /**
     * 验证是否为合法的 QQ 号（5~12 位纯数字，不能以 0 开头）
     */
    public static function qq($data)
    {
        if (preg_match("/^[1-9]\d{4,11}$/", $data)) {
            return $data;
        }
        return false;
    }
}