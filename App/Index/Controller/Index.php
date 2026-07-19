<?php
/**
 * Index应用首页控制器
 * Date: 2026-07-18
 * Author: 小新
 * SystemName: XiaoPHP
 */

use XiaoPHP\System\View;
Class Index
    {
        public function Main()
            {
                $view = new View();
                $view->set(["h1"=>"XiaoPHP V 2.0.0","title"=>"Hello World!"])->show("index");
            }
    }
