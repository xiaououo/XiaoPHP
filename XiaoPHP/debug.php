<?php
use XiaoPHP\systools\Config\Conf;

function displayDebugInfo(Throwable $exception)
{
    $config = Conf::get("App");
    $debug = filter_var($config['debug'] ?? 'false', FILTER_VALIDATE_BOOLEAN);

    if (!$debug) {
        error_log($exception->getMessage());
        if (!headers_sent()) {
            http_response_code(500);
        }
        echo "<!DOCTYPE html><html><head><title>系统错误</title></head><body><h1>系统繁忙，请稍后再试</h1></body></html>";
        exit(1);
    }

    $type = get_class($exception);
    $message = $exception->getMessage();
    $file = $exception->getFile();
    $line = $exception->getLine();
    $trace = $exception->getTraceAsString();
    $code = $exception->getCode() ?: "500";

    $clientIp = $_SERVER['REMOTE_ADDR'] ?? '未知';
    $phpVersion = phpversion();
    $currentTime = date('Y-m-d H:i:s');

    $globals = [
        "GET"     => $_GET,
        "POST"    => $_POST,
        "SESSION" => $_SESSION ?? [],
        "COOKIE"  => $_COOKIE,
        "FILES"   => $_FILES,
        "SERVER"  => $_SERVER,
    ];

    $codeSnippet = [];
    if ($file && $line && file_exists($file)) {
        $allLines = file($file);
        $totalLines = count($allLines);
        $start = max(0, $line - 8);
        $end = min($totalLines, $line + 7);
        for ($i = $start; $i < $end; $i++) {
            $num = $i + 1;
            $codeSnippet[] = [
                "num"   => $num,
                "text"  => rtrim($allLines[$i], "\r\n"),
                "error" => $num == $line,
            ];
        }
    }

    if (!headers_sent()) {
        header("Content-Type: text/html; charset=utf-8");
        http_response_code(500);
    }
    ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($type); ?> - XiaoPHP</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{background:#f0f0f0;font-family:-apple-system,"PingFang SC","Microsoft YaHei",sans-serif;color:#333;padding:24px;min-height:100vh}
        .container{max-width:960px;margin:0 auto}

        .header{background:#fff;border:1px solid #ddd;border-radius:6px;padding:20px 24px;margin-bottom:16px}
        .header .type{font-size:20px;font-weight:600;color:#c0392b;margin-bottom:6px}
        .header .msg{font-size:15px;color:#555;line-height:1.6;word-break:break-all}
        .header .meta{margin-top:12px;font-size:13px;color:#888}
        .header .meta span{margin-right:20px}

        .section{margin-bottom:16px}
        .section-title{font-size:13px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;padding:0 4px}

        .code-box{background:#1e1e1e;border-radius:6px;overflow:hidden;border:1px solid #333}
        .code-header{background:#2d2d2d;padding:8px 16px;font-size:12px;color:#999;font-family:"SF Mono","Menlo",monospace}
        .code-header span{color:#e0e0e0}
        .code-lines{overflow-x:auto}
        .code-lines table{width:100%;border-collapse:collapse}
        .code-lines td{font-family:"SF Mono","Menlo","Consolas",monospace;font-size:13px;line-height:1.6;padding:0 12px;vertical-align:top}
        .code-lines .ln{width:1%;text-align:right;color:#666;padding-right:12px;user-select:none;white-space:nowrap}
        .code-lines .ct{color:#d4d4d4;white-space:pre;padding-left:12px;border-left:1px solid #333}
        .code-lines tr.error-line{background:rgba(192,57,43,0.15)}
        .code-lines tr.error-line .ln{color:#e74c3c}
        .code-lines tr.error-line .ct{color:#fff}
        .code-lines tr.error-line .ct::before{content:'▶ ';color:#e74c3c}

        .trace-box{background:#fff;border:1px solid #ddd;border-radius:6px;padding:16px 20px;font-family:"SF Mono","Menlo","Consolas",monospace;font-size:12px;line-height:1.7;color:#555;white-space:pre-wrap;word-break:break-all;max-height:360px;overflow:auto}

        .toggle-wrap{margin-bottom:16px}
        .toggle-btn{display:inline-block;cursor:pointer;font-size:13px;color:#555;border:1px solid #ccc;border-radius:4px;padding:6px 14px;background:#fff;user-select:none}
        .toggle-btn:hover{background:#f5f5f5}
        .ctx-box{display:none;background:#fff;border:1px solid #ddd;border-radius:6px;padding:14px 18px;margin-top:8px;font-family:"SF Mono","Menlo",monospace;font-size:11px;line-height:1.6;color:#666;max-height:300px;overflow:auto;white-space:pre-wrap;word-break:break-all}
        .ctx-box.active{display:block}

        .info-row{display:flex;flex-wrap:wrap;gap:16px;font-size:13px;color:#888;margin-bottom:16px}
        .info-row span{padding:4px 12px;background:#fff;border:1px solid #e0e0e0;border-radius:4px}

        .actions{text-align:center;margin-top:24px}
        .actions a{display:inline-block;padding:8px 24px;background:#fff;color:#555;font-size:14px;border:1px solid #ccc;border-radius:4px;text-decoration:none;margin:0 6px}
        .actions a:hover{background:#f5f5f5}
        .actions a.primary{background:#333;color:#fff;border-color:#333}
        .actions a.primary:hover{background:#555}

        @media(max-width:640px){
            body{padding:12px}
            .header{padding:14px 16px}
            .header .type{font-size:17px}
            .code-lines td{font-size:11px;padding:0 8px}
        }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <div class="type"><?php echo htmlspecialchars($type); ?></div>
        <div class="msg"><?php echo htmlspecialchars($message ?: '(无错误消息)'); ?></div>
        <div class="meta">
            <span>文件: <?php echo htmlspecialchars($file); ?></span>
            <span>行: <?php echo (int)$line; ?></span>
            <span>代码: <?php echo htmlspecialchars($code); ?></span>
        </div>
    </div>

    <?php if (!empty($codeSnippet)): ?>
    <div class="section">
        <div class="section-title">代码片段</div>
        <div class="code-box">
            <div class="code-header"><?php echo htmlspecialchars($file); ?></div>
            <div class="code-lines">
                <table>
                    <?php foreach ($codeSnippet as $cs): ?>
                    <tr class="<?php echo $cs['error'] ? 'error-line' : ''; ?>">
                        <td class="ln"><?php echo $cs['num']; ?></td>
                        <td class="ct"><?php echo htmlspecialchars($cs['text']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="section">
        <div class="section-title">调用堆栈</div>
        <div class="trace-box"><?php echo htmlspecialchars($trace); ?></div>
    </div>

    <div class="toggle-wrap">
        <span class="toggle-btn" onclick="toggleGlobals()">▶ 请求上下文</span>
        <div id="ctxContainer" class="ctx-box">
            <?php foreach ($globals as $key => $value): ?>
            <strong><?php echo htmlspecialchars($key); ?></strong>
            <?php echo htmlspecialchars(print_r($value, true)); ?>

            <?php endforeach; ?>
        </div>
    </div>

    <div class="info-row">
        <span>IP: <?php echo htmlspecialchars($clientIp); ?></span>
        <span>PHP: <?php echo htmlspecialchars($phpVersion); ?></span>
        <span>时间: <?php echo htmlspecialchars($currentTime); ?></span>
    </div>

    <div class="actions">
        <a href="javascript:history.back();">← 返回</a>
        <a href="/" class="primary">首页</a>
    </div>

</div>

<script>
function toggleGlobals() {
    var c = document.getElementById('ctxContainer');
    var t = document.querySelector('.toggle-btn');
    c.classList.toggle('active');
    t.textContent = c.classList.contains('active') ? '▼ 收起上下文' : '▶ 请求上下文';
}
</script>
</body>
</html>
<?php
    exit(1);
}