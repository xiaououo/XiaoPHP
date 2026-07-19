<?php
/**
 * 视图模板引擎
 * Date: 2026-07-18
 * Author: 小新
 * SystemName: XiaoPHP
 */

namespace XiaoPHP\System;

class View
{
    private $data = [];
    private $path = '';
    private $autoEscape = true; // 是否自动转义

    public function set($data, $path = '')
    {
        $this->data = array_merge($this->data, $data);
        $path = preg_replace('/[^a-zA-Z0-9_\-\\/]/', '', $path);
        $path = preg_replace('#/+#', '/', $path);
        $parts = array_filter(explode('/', $path), function ($p) {
            return $p !== '..' && $p !== '.';
        });
        $this->path = implode('/', $parts);
        return $this;
    }

    /**
     * 设置是否自动转义
     */
    public function setAutoEscape($enabled = true)
    {
        $this->autoEscape = $enabled;
        return $this;
    }


    public function get_module_path() {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $callerFile = $trace[1]['file'] ?? __FILE__; 
        return dirname($callerFile, 2) . DIRECTORY_SEPARATOR;
    }

    public function show($filename)
    {
        $filename = basename($filename);
        $filename = preg_replace('/[^a-zA-Z0-9_\-.]/', '', $filename);
        $viewBase = $this->get_module_path() . 'View';
        if ($viewBase === false) {
            throw new \Exception('视图根目录不存在');
        }
        $relative = ($this->path ? $this->path . '/' : '') . $filename . '.html';
        $relative = ltrim($relative, '/');
        $file = $viewBase . '/' . $relative;

        $realFile = realpath($file);
        if ($realFile === false || strpos($realFile, $viewBase) !== 0) {
            throw new \Exception("模板不存在或非法路径: $relative");
        }

        if (!file_exists($realFile)) {
            throw new \Exception("模板不存在: $relative");
        }

        $content = file_get_contents($realFile);
    
        $content = $this->compileTemplateWithEscape($content); 

        $tmp = tempnam(sys_get_temp_dir(), 'tpl_') . '.php';
        file_put_contents($tmp, $content);
        extract($this->data);
        ob_start();
        try {
            include $tmp;
            ob_end_flush();
        } catch (\Throwable $e) {
            ob_end_clean();
            unlink($tmp);
            throw $e;
        }
        unlink($tmp);

        return $this;
    }

    /**
     * 支持原始输出的语法：{{! $var }}
     * 以及自动转义的语法：{{ $var }}
     */
    private function compileTemplateWithEscape($content)
    {
        // 处理不转义的变量 {{! $var }}
        $content = preg_replace_callback(
            '/\{\{\!\s*\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(?:\.[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*)\s*\}\}/',
            function ($m) {
                $var = preg_replace('/\.([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/', "['$1']", $m[1]);
                return "<?php echo \$$var; ?>";
            },
            $content
        );

        // 处理自动转义的变量 {{ $var }}
        $content = preg_replace_callback(
            '/\{\{\s*\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(?:\.[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*)\s*\}\}/',
            function ($m) {
                $var = preg_replace('/\.([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/', "['$1']", $m[1]);
                return "<?php echo htmlspecialchars(\$$var, ENT_QUOTES, 'UTF-8'); ?>";
            },
            $content
        );

        return $content;
    }

    /**
     * 安全输出函数，可用于模板中手动调用
     */
    public static function e($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * 原始输出函数，不转义
     */
    public static function raw($string)
    {
        return $string;
    }
}