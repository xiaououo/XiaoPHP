<?php

/**
 * 系统配置
 * Date: 2026-07-14
 * Author: 小新
 * SystemName: XiaoPHP
 */

    namespace XiaoPHP\systools\Config;
    Class Conf
        {

            public static function get($conf): ?array
                {
                    if(file_exists(__DIR__."/../../../Config/".$conf.".php"))
                        {
                            return require __DIR__."/../../../Config/".$conf.".php";
                        }
                    else
                        {
                            return null;
                        }
                }
            
        }