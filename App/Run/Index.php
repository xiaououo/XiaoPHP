<?php
    use XiaoPHP\systools\Middleware;
    use XiaoPHP\systools\toolsbox\View;
    Class Index
        {
            public function Main()
                {
                    (new Middleware())->check();  
                    $view =new view();
                    $json=["title"=>"你好世界！","h1"=>"Hello World"];
                    $view->set($json,"index")->show("index");
                }

        }