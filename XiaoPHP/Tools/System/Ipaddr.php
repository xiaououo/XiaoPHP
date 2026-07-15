<?php

/**
 * 系统IP地址获取
 * Date: 2026-07-14
 * Author: 小新
 * SystemName: XiaoPHP
 */

    namespace XiaoPHP\systools\System;
    Class Ipaddr
        {

            public static function get(): ?string
                {
                    return $_SERVER["REMOTE_ADDR"] ?? "unknown";
                }
            
            
        }