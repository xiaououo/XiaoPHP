# XiaoPHP V1.5.0 使用文档

> 基于 PHP 的轻量级 MVC 微框架，适合小型项目快速开发  
> Author: 小新 | License: MIT

---

## 目录

- [一、环境要求](#一环境要求)
- [二、快速开始](#二快速开始)
- [三、目录结构](#三目录结构)
- [四、配置说明](#四配置说明)
- [五、路由系统](#五路由系统)
- [六、控制器](#六控制器)
- [七、中间件（令牌认证）](#七中间件令牌认证)
- [八、白名单](#八白名单)
- [九、视图模板](#九视图模板)
- [十、数据库操作](#十数据库操作)
- [十一、Redis 操作](#十一redis-操作)
- [十二、缓存系统](#十二缓存系统)
- [十三、日志系统](#十三日志系统)
- [十四、HTTP 请求工具](#十四http-请求工具)
- [十五、JSON 工具](#十五json-工具)
- [十六、加密工具](#十六加密工具)
- [十七、阿里云 DNS 操作](#十七阿里云-dns-操作)
- [十八、IP 地址获取](#十八ip-地址获取)
- [十九、错误处理与调试](#十九错误处理与调试)
- [二十、Web 服务器配置](#二十web-服务器配置)

---

## 一、环境要求

- PHP >= 7.4
- 推荐扩展：`pdo_mysql`、`redis`、`openssl`、`curl`
- Composer（用于自动加载）

---

## 二、快速开始

### 2.1 项目部署

将项目放置到 Web 服务器目录下，确保 `Public/` 目录为网站根目录。

### 2.2 配置环境变量

编辑项目根目录下的 `.env` 文件：

```env
# 数据库
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=xiaophp
DB_USER=root
DB_PASSWORD=123456

# Redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=

# 调试模式
DEBUG=true
```

### 2.3 安装依赖

```bash
composer install
```

### 2.4 启动开发服务器

```bash
php -S localhost:8080 -t Public/ Public/router.php
```

浏览器访问 `http://localhost:8080` 即可看到默认首页。

---

## 三、目录结构

```
XiaoPHPV1.5/
├── Public/                # Web 入口目录（网站根目录）
│   ├── index.php          # 应用入口文件
│   ├── router.php         # PHP 内置服务器路由脚本
│   ├── .htaccess          # Apache URL 重写规则
│   └── nginx.htaccess     # Nginx URL 重写参考
├── XiaoPHP/               # 框架核心
│   ├── console.php        # 系统加载器
│   ├── Routing.php        # 路由分发器
│   ├── Middleware.php     # 中间件（令牌认证）
│   ├── debug.php          # 调试错误页面
│   ├── xiao.php           # 品牌标识
│   ├── Error/             # 错误页面（401/403/404 HTML模板）
│   │   └── error.php      # 错误处理函数
│   └── Tools/             # 工具类库
│       ├── System/        # 系统工具类
│       └── Code/          # 业务工具类
├── Config/                # 配置文件目录
├── Route/                 # 自定义路由定义
├── Whitelist/             # 白名单定义
├── App/                   # 应用目录
│   └── Run/               # 控制器目录
├── view/                  # 视图模板目录
├── logs/                  # 日志文件目录
├── vendor/                # Composer 依赖
└── .env                   # 环境变量配置
```

---

## 四、配置说明

所有配置文件位于 `Config/` 目录，返回 PHP 数组。

### 4.1 应用配置 `Config/App.php`

```php
return [
    "debug" => Env::Load(null, "DEBUG") ?? 'false',  // 调试模式：true | false
    "error" => 'html',                                 // 错误响应格式：html | json
];
```

| 配置项 | 说明 |
|--------|------|
| `debug` | 调试模式，`true` 时显示详细错误页面，`false` 时显示简洁提示 |
| `error` | 错误响应格式，`html` 返回 HTML 错误页面（需有对应 Error 模板），`json` 返回 JSON |

### 4.2 缓存配置 `Config/Cache.php`

```php
return [
    "dir"    => __DIR__ . '/../../Temp/Cache/',  // 缓存文件存放目录
    "expire" => '3600',                           // 默认过期时间（秒）
];
```

### 4.3 数据库配置 `Config/Mysql.php`

```php
return [
    "host"     => Env::Load(null, "DB_HOST") ?? 'localhost',
    "port"     => Env::Load(null, "DB_PORT") ?? '3306',
    "user"     => Env::Load(null, "DB_USER") ?? 'root',
    "password" => Env::Load(null, "DB_PASSWORD") ?? '123456',
    "dbname"   => Env::Load(null, "DB_NAME") ?? 'xiaophp',
];
```

### 4.4 Redis 配置 `Config/Redis.php`

```php
return [
    "host"     => Env::Load(null, "REDIS_HOST") ?? '127.0.0.1',
    "port"     => Env::Load(null, "REDIS_PORT") ?? '6379',
    "password" => Env::Load(null, "REDIS_PASSWORD") ?? '',
];
```

### 4.5 中间件配置 `Config/Middleware.php`

```php
return [
    "storage"       => 'cache',                      // 存储方式：cache | redis
    "token_key"     => 'token',                      // 令牌参数名
    "auth_mode"     => 'bearer,get,post,cookie',     // 认证方式
    "cookie_name"   => 'auth_token',                 // Cookie 名称
    "cookie_expire" => '7200',                       // 默认过期时间（秒）
];
```

### 4.6 日志配置 `Config/Logs.php`

```php
return [
    "success" => 'true',  // 是否记录成功日志
    "error"   => 'true',  // 是否记录错误日志
];
```

### 4.7 阿里云 DNS 配置 `Config/AliyunDns.php`

```php
return [
    "accessKeyId"     => '',  // 阿里云 AccessKey ID
    "accessKeySecret" => '',  // 阿里云 AccessKey Secret
];
```

### 4.8 读取配置

使用 `Conf` 类读取任意配置：

```php
use XiaoPHP\systools\Config\Conf;

$config = Conf::get("App");       // 读取 Config/App.php
$debug  = $config['debug'];       // 获取配置项
```

### 4.9 环境变量读取

```php
use XiaoPHP\systools\Config\Env;

// 读取 .env 中指定 key
$value = Env::Load(null, "DB_HOST");  // 返回 '127.0.0.1'

// 获取已加载的环境变量
$debug = Env::get("DEBUG", "false");  // 第二个参数为默认值
```

---

## 五、路由系统

### 5.1 自动路由（默认）

URL 格式：`http://域名/控制器名/方法名`

规则：
- URL 第一段对应 `App/Run/` 目录下的控制器文件名（类名）
- URL 第二段对应控制器中的方法名
- 不指定方法时默认调用 `Main()` 方法

示例：

| URL | 对应文件 | 调用方法 |
|-----|---------|---------|
| `/` | 走自定义路由 → `App/Run/Index.php` | `Index::Main()` |
| `/Index/Main` | `App/Run/Index.php` | `Index::Main()` |
| `/User/Login` | `App/Run/User.php` | `User::Login()` |
| `/User` | `App/Run/User.php` | `User::Main()` |

### 5.2 自定义路由

在 `Route/Route.php` 中定义：

```php
use XiaoPHP\systools\Config\Route;

// 格式：Route::add("请求方法", "URL路径", "控制器名/方法名", "控制器目录");
Route::add("GET",  "/",           "Index",          "App/Run");
Route::add("POST", "/api/login",  "Auth/Login",     "App/Run");
Route::add("GET",  "/user/info",  "User/GetInfo",   "App/Run");
```

自定义路由优先级高于自动路由。路由匹配时会验证 HTTP 请求方法。

---

## 六、控制器

### 6.1 创建控制器

在 `App/Run/` 目录下创建控制器文件，文件名与类名一致：

```php
<?php
// App/Run/User.php

use XiaoPHP\systools\Middleware;
use XiaoPHP\systools\toolsbox\View;

class User
{
    public function Main()
    {
        // 默认入口方法
    }

    public function Login()
    {
        // 登录方法
    }
}
```

### 6.2 完整示例

```php
<?php
// App/Run/Index.php

use XiaoPHP\systools\Middleware;
use XiaoPHP\systools\toolsbox\View;

class Index
{
    public function Main()
    {
        // 中间件认证检查
        (new Middleware())->check();

        // 渲染视图
        $view = new View();
        $data = [
            "title" => "你好世界！",
            "h1"    => "Hello World"
        ];
        $view->set($data, "index")->show("index");
    }
}
```

---

## 七、中间件（令牌认证）

### 7.1 认证流程

中间件在控制器方法中手动调用 `(new Middleware())->check()` 进行令牌验证。如果 URL 在白名单中，则跳过验证。

### 7.2 支持的认证方式

配置文件 `auth_mode` 支持多种认证方式，可组合使用（逗号分隔）：

| 方式 | 说明 |
|------|------|
| `bearer` | 从 HTTP 请求头 `Authorization: Bearer <token>` 获取 |
| `get` | 从 URL 参数 `?token=xxx` 获取 |
| `post` | 从 POST 数据 `token=xxx` 获取 |
| `cookie` | 从 Cookie `auth_token` 获取 |

### 7.3 设置令牌

```php
use XiaoPHP\systools\Middleware;

$middleware = new Middleware();

// 设置令牌（默认过期时间 7200 秒）
$middleware->setToken('my_token_value', ['user_id' => 1]);

// 自定义过期时间
$middleware->setToken('my_token_value', ['user_id' => 1], 3600);
```

### 7.4 删除令牌

```php
$middleware = new Middleware();
$middleware->delToken('my_token_value');
```

### 7.5 存储方式

- **cache**（默认）：令牌存储在文件缓存中（`Temp/Cache/` 目录）
- **redis**：令牌存储在 Redis 中，Redis 不可用时自动降级为文件缓存

---

## 八、白名单

在 `Whitelist/Whitelist.php` 中定义不需要认证的 URL：

```php
use XiaoPHP\systools\Config\Whitelist;

// 精确匹配
Whitelist::add("/Index/Main");
Whitelist::add("/api/health");

// 通配符匹配
Whitelist::add("/api/public/*");
Whitelist::add("/static/*");
```

---

## 九、视图模板

### 9.1 模板语法

视图文件放在 `view/` 目录下，使用 `.html` 扩展名。支持 `{{$变量名}}` 语法输出变量，支持点号访问数组：

```html
<!-- view/index/index.html -->
<!DOCTYPE html>
<html>
<head>
    <title>{{$title}}</title>
</head>
<body>
    <h1>{{$h1}}</h1>
    <p>{{$user.name}}</p>   <!-- 对应 $user['name'] -->
</body>
</html>
```

### 9.2 渲染视图

```php
use XiaoPHP\systools\toolsbox\View;

$view = new View();

// 设置数据并指定子目录
$view->set([
    "title" => "我的页面",
    "h1"    => "欢迎光临",
    "user"  => ["name" => "小明"]
], "index")->show("index");

// 链式调用
$view->set(["title" => "首页"], "index")
     ->set(["h1" => "Hello"], "index")
     ->show("index");
```

### 9.3 安全特性

- 视图路径自动过滤 `..` 和 `.`，防止目录穿越
- 文件名过滤特殊字符，仅允许 `[a-zA-Z0-9_\-.]`
- 使用 `realpath()` 校验，确保模板文件在 `view/` 目录内

---

## 十、数据库操作

### 10.1 快速使用

使用全局辅助函数 `db()` 获取数据库实例：

```php
$db = db();
```

或手动实例化：

```php
use app\tools\MysqlTools;

$db = new MysqlTools();
```

### 10.2 查询数据

```php
// 查询全部
$users = db()->table('users')->get();

// 条件查询
$user = db()->table('users')->where('id', 1)->first();

// 多条件查询
$list = db()->table('users')
    ->where('status', '1')
    ->where('age', '>= 18')
    ->get();

// IN 查询
$list = db()->table('users')
    ->where('id', [1, 2, 3])
    ->get();

// 分页查询
$list = db()->table('users')
    ->limit(10)
    ->offset(0)
    ->get();
```

### 10.3 模糊查询

```php
// 单字段模糊匹配
db()->table('users')->whereLike('name', '小')->get();

// 左匹配（%value）
db()->table('users')->whereLike('name', '小', 'left')->get();

// 右匹配（value%）
db()->table('users')->whereLike('name', '小', 'right')->get();

// 多字段模糊匹配
db()->table('users')->whereMultiLike(['name', 'email'], '小')->get();

// 全字段模糊匹配（自动获取表的所有列）
db()->table('users')->whereFullLike('小')->get();
```

### 10.4 插入数据

```php
$id = db()->table('users')->insert([
    'name'  => '小明',
    'email' => 'xiaoming@example.com',
    'age'   => 18,
]);
// 返回插入后的自增 ID
```

### 10.5 更新数据

```php
$affected = db()->table('users')
    ->where('id', 1)
    ->update([
        'name' => '新名字',
        'age'  => 20,
    ]);
// 返回受影响的行数
```

> **注意**：`update()` 和 `delete()` 必须带 `WHERE` 条件，否则会报错。

### 10.6 删除数据

```php
$affected = db()->table('users')->where('id', 1)->delete();
```

### 10.7 获取最小/最大 ID

```php
$range = db()->table('users')->getMinMaxId();
// 返回 ['min_id' => 1, 'max_id' => 100]
```

### 10.8 关闭连接

```php
db()->close();
```

---

## 十一、Redis 操作

### 11.1 基本使用

```php
use app\tools\RedisTools;

$redis = new RedisTools();

// 字符串操作
$redis->set('key', 'value');
$value = $redis->get('key');
$redis->del('key');

// 哈希操作
$redis->hSet('hash', 'field', 'value');
$value = $redis->hGet('hash', 'field');

// 过期时间
$redis->expire('key', 3600);
$ttl = $redis->ttl('key');

// 检查是否存在
$exists = $redis->exists('key');
```

### 11.2 Pipeline 管道模式

```php
$redis = new RedisTools();

// 开启管道
$redis->pipeline();

// 批量操作入队
$redis->set('key1', 'value1');
$redis->set('key2', 'value2');
$redis->set('key3', 'value3');

// 执行管道
$results = $redis->exec();
```

### 11.3 可用性检查

```php
$redis = new RedisTools();

if ($redis->isAvailable()) {
    // Redis 可用
    $redis->set('key', 'value');
} else {
    // Redis 不可用，走降级逻辑
}
```

> **注意**：Redis 扩展未安装或连接失败时，`RedisTools` 会自动降级，所有操作返回 null 或空，不会抛出异常。

---

## 十二、缓存系统

### 12.1 基本使用

```php
use XiaoPHP\systools\System\Cache;

$cache = new Cache();

// 设置缓存（默认过期时间从配置读取）
$cache->set('my_key', ['name' => '小明', 'age' => 18]);

// 设置缓存并指定过期时间（秒）
$cache->set('my_key', $data, 1800);

// 获取缓存
$data = $cache->get('my_key');

// 删除缓存
$cache->delete('my_key');

// 清空所有缓存
$cache->clear();
```

### 12.2 缓存原理

- 缓存文件存储在 `Temp/Cache/` 目录（可在配置中修改）
- 文件名 = `md5(key).txt`
- 内容为序列化数组：`['expire' => 过期时间戳, 'data' => 数据]`
- 读取时自动检查过期，过期则删除文件并返回 `null`

---

## 十三、日志系统

### 13.1 自动日志

框架在路由处理过程中自动记录日志，无需手动调用。日志分为两类：

- **成功日志**：请求成功时记录，存储于 `logs/success/`
- **错误日志**：请求失败时记录，存储于 `logs/error/`

### 13.2 手动记录日志

```php
use XiaoPHP\systools\System\Logs;

$logs = new Logs();

// 记录成功日志
$logs->logs(0, 200);  // 参数1：0=成功，参数2：状态码

// 记录错误日志
$logs->logs(1, 404);  // 参数1：1=错误，参数2：状态码
```

### 13.3 日志格式

```
success 2026-07-14 12:30:00--[192.168.1.1]:/Index/Main-code:200
error 2026-07-14 12:30:05--[192.168.1.2]:/User/Login-code:404
```

日志按天分割，文件名格式：`2026-07-14.logs`

### 13.4 日志配置

在 `Config/Logs.php` 中控制是否记录：

```php
return [
    "success" => 'true',   // 设为 'false' 关闭成功日志
    "error"   => 'true',   // 设为 'false' 关闭错误日志
];
```

---

## 十四、HTTP 请求工具

### 14.1 GET 请求

```php
use XiaoPHP\systools\toolsbox\Wget;

// 简单 GET 请求
$response = Wget::get('https://api.example.com/data');

// 启用 SSL 验证
$response = Wget::get('https://api.example.com/data', true);

// 自定义 cURL 选项
$response = Wget::get('https://api.example.com/data', false, [
    CURLOPT_TIMEOUT        => 60,
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer token123'],
]);
```

### 14.2 POST 请求

```php
// 表单数据（自动设置 Content-Type: application/x-www-form-urlencoded）
$response = Wget::post('https://api.example.com/login', [
    'username' => 'admin',
    'password' => '123456',
]);

// JSON 数据（传入字符串自动设置 Content-Type: application/json）
$response = Wget::post('https://api.example.com/user', json_encode([
    'name' => '小明',
    'age'  => 18,
]));
```

### 14.3 PUT 请求

```php
$response = Wget::put('https://api.example.com/user/1', [
    'name' => '新名字',
]);
```

### 14.4 DELETE 请求

```php
$response = Wget::delete('https://api.example.com/user/1');
```

### 14.5 文件下载

```php
$result = Wget::download(
    'https://example.com/file.zip',
    '/path/to/save/file.zip'
);

if ($result === true) {
    echo "下载成功";
} else {
    echo "下载失败：" . $result['error'];
}
```

### 14.6 错误处理

所有 HTTP 方法在出错时返回包含 `error` 和 `http_code` 的数组：

```php
$response = Wget::get('https://api.example.com/data');

if (is_array($response) && isset($response['error'])) {
    echo "请求失败：" . $response['error'];
    echo "HTTP 状态码：" . $response['http_code'];
} else {
    // 请求成功，$response 为响应内容
}
```

---

## 十五、JSON 工具

### 15.1 输出 JSON 响应

```php
use XiaoPHP\systools\toolsbox\Json;

// 直接输出 JSON 并终止请求
Json::encode([
    'code' => 200,
    'msg'  => '成功',
    'data' => ['id' => 1, 'name' => '小明']
]);
// 自动设置 Content-Type: application/json
```

### 15.2 解码 JSON

```php
$array = Json::decode('{"name":"小明","age":18}');
// 返回关联数组
```

### 15.3 远程 JSON 请求

```php
// 请求远程 JSON 接口并自动解码
$data = Json::wdecode('https://api.example.com/data');

// 启用 SSL 验证
$data = Json::wdecode('https://api.example.com/data', true);

// 自定义 cURL 选项
$data = Json::wdecode('https://api.example.com/data', false, [
    CURLOPT_TIMEOUT => 10,
]);
```

---

## 十六、加密工具

### 16.1 AES 加密

```php
use XiaoPHP\systools\toolsbox\AesTool;

// 加密（默认 AES-128-ECB）
$encrypted = AesTool::encode('Hello World', 'my_secret_key');

// 解密
$decrypted = AesTool::decode($encrypted, 'my_secret_key');

// 指定加密算法
$encrypted = AesTool::encode('Hello World', 'my_key', 'AES-256-ECB');

// 使用 CBC 模式（需要 IV）
$iv = '1234567890123456';  // 16 字节
$encrypted = AesTool::encode('Hello World', 'my_key', 'AES-128-CBC', $iv);
$decrypted = AesTool::decode($encrypted, 'my_key', 'AES-128-CBC', $iv);
```

> 密钥通过 SHA1-PRNG 算法派生到对应长度，无需手动补全密钥长度。

### 16.2 RSA 加密

```php
use XiaoPHP\systools\toolsbox\RSATool;

$rsa = new RSATool();

// 公钥加密
$encrypted = $rsa->encode('Hello World', $publicKey);

// 私钥解密
$decrypted = $rsa->decode($encrypted, $privateKey);
```

> 公钥会自动添加 `-----BEGIN PUBLIC KEY-----` 头部和换行，私钥需是完整的 PEM 格式字符串。

---

## 十七、阿里云 DNS 操作

### 17.1 配置

在 `Config/AliyunDns.php` 中配置阿里云密钥：

```php
return [
    "accessKeyId"     => 'your-access-key-id',
    "accessKeySecret" => 'your-access-key-secret',
];
```

### 17.2 实例化

```php
use app\tools\AliyunDns;

$dns = new AliyunDns();
```

### 17.3 获取域名列表

```php
$result = $dns->domains();
// 返回 ['code' => 200, 'data' => [...], 'total' => N]
```

### 17.4 获取解析记录列表

```php
// 获取所有记录
$result = $dns->list();

// 获取指定域名记录
$result = $dns->list('example.com');
```

### 17.5 查询解析记录

```php
$result = $dns->get([
    'domain' => 'example.com',
    'rr'     => 'www',      // 主机记录
    'record' => 'A',        // 记录类型
]);
```

### 17.6 添加解析记录

```php
$result = $dns->add([
    'domain'   => 'example.com',
    'rr'       => 'www',           // 主机记录
    'record'   => 'A',             // 记录类型：A/AAAA/CNAME/MX/TXT...
    'value'    => '192.168.1.1',   // 记录值
    'ttl'      => 600,             // TTL（秒）
    'priority' => 10,              // MX 优先级（可选）
    'line'     => 'default',       // 解析线路（可选）
]);
// 返回 ['code' => 200, 'message' => 'Record added successfully', 'data' => ['recordId' => '...']]
```

### 17.7 更新解析记录

```php
$result = $dns->update([
    'recordId' => '123456789',     // 必填：记录 ID
    'rr'       => 'www',           // 可选
    'record'   => 'A',             // 可选
    'value'    => '10.0.0.1',      // 可选
    'ttl'      => 300,             // 可选
]);
```

### 17.8 删除解析记录

```php
// 方式一：通过 recordId 删除
$result = $dns->del(['recordId' => '123456789']);

// 方式二：通过域名+主机记录自动查找并删除
$result = $dns->del([
    'domain' => 'example.com',
    'rr'     => 'www',
]);
```

### 17.9 设置记录状态

```php
// 启用记录
$result = $dns->setStatus([
    'recordId' => '123456789',
    'status'   => 'enable',
]);

// 禁用记录
$result = $dns->setStatus([
    'recordId' => '123456789',
    'status'   => 'disable',
]);
```

### 17.10 修改备注

```php
$result = $dns->remark([
    'recordId' => '123456789',
    'remark'   => '这是新的备注信息',
]);
```

---

## 十八、IP 地址获取

```php
use XiaoPHP\systools\System\Ipaddr;

$ip = Ipaddr::get();
// 返回 $_SERVER['REMOTE_ADDR'] 或 'unknown'
```

---

## 十九、错误处理与调试

### 19.1 错误响应函数

```php
// 发送错误响应
Error(404, '页面未找到');
Error(401, '未授权访问');
Error(403, '禁止访问');
Error(500, '服务器内部错误');
```

根据 `App.php` 中 `error` 配置决定响应格式：
- `html`：返回 `Error/` 目录下对应的 HTML 页面（如 `Error/404.html`）
- `json`：返回 JSON 格式错误信息

### 19.2 调试模式

在 `.env` 中设置 `DEBUG=true` 开启调试模式。当发生未捕获异常时：

- **开发模式**（`DEBUG=true`）：显示详细的错误页面，包含：
  - 异常类型和消息
  - 出错文件和行号
  - 代码片段（错误行高亮，前后各 7 行）
  - 完整调用堆栈
  - 请求上下文（GET/POST/SESSION/COOKIE/SERVER）
  - 客户端 IP、PHP 版本、当前时间

- **生产模式**（`DEBUG=false`）：显示简洁的"系统繁忙"提示，错误信息写入 PHP 错误日志。

### 19.3 自定义错误页面

在 `XiaoPHP/Error/` 目录下创建 HTML 文件：
- `401.html` - 未授权
- `403.html` - 禁止访问
- `404.html` - 页面未找到

---

## 二十、Web 服务器配置

### 20.1 Apache

项目已自带 `.htaccess`（位于 `Public/.htaccess`），确保已启用 `mod_rewrite`：

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php/$1 [QSA,PT,L]
</IfModule>
```

### 20.2 Nginx

参考 `Public/nginx.htaccess`，在 Nginx 配置中添加：

```nginx
location / {
    try_files $uri $uri/ /index.php?$args;
}
```

完整示例：

```nginx
server {
    listen 80;
    server_name example.com;
    root /path/to/XiaoPHPV1.5/Public;
    index index.php;

    # 隐藏 .env 文件（重要）
    location ~ /\.env {
        deny all;
    }

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 20.3 PHP 内置服务器

开发时可直接使用：

```bash
php -S localhost:8080 -t Public/ Public/router.php
```

`router.php` 会自动处理静态文件请求，其他请求转发到 `index.php`。

---

## 附录：完整示例

### 创建一个完整的 API 接口

**1. 创建控制器 `App/Run/Api.php`：**

```php
<?php
// App/Run/Api.php

use XiaoPHP\systools\Middleware;
use XiaoPHP\systools\toolsbox\Json;

class Api
{
    public function Main()
    {
        // 令牌验证
        (new Middleware())->check();

        // 获取用户列表
        $users = db()->table('users')->get();

        Json::encode([
            'code' => 200,
            'msg'  => '成功',
            'data' => $users,
        ]);
    }

    public function Create()
    {
        (new Middleware())->check();

        $name  = $_POST['name']  ?? '';
        $email = $_POST['email'] ?? '';

        if (empty($name)) {
            Json::encode(['code' => 400, 'msg' => '名称不能为空']);
        }

        $id = db()->table('users')->insert([
            'name'  => $name,
            'email' => $email,
        ]);

        Json::encode([
            'code' => 200,
            'msg'  => '创建成功',
            'data' => ['id' => $id],
        ]);
    }
}
```

**2. 配置自定义路由 `Route/Route.php`：**

```php
use XiaoPHP\systools\Config\Route;

Route::add("GET",  "/api/users",     "Api",       "App/Run");
Route::add("POST", "/api/user/create", "Api/Create", "App/Run");
```

**3. 配置白名单 `Whitelist/Whitelist.php`（可选）：**

```php
use XiaoPHP\systools\Config\Whitelist;

// 注册接口不需要认证
Whitelist::add("/api/user/register");
```

**4. 访问接口：**

```bash
# 获取用户列表
curl -H "Authorization: Bearer your_token" http://localhost:8080/api/users

# 创建用户
curl -X POST -H "Authorization: Bearer your_token" \
  -d "name=小明&email=xm@test.com" \
  http://localhost:8080/api/user/create
```

---

> 文档版本：V1.5.0 | 最后更新：2026-07-14
