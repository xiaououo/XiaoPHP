# XiaoPHP 1\.6 开发文档
 

# 一、框架简介

XiaoPHP（小新PHP）是一款由国人开发的轻量级 PHP MVC 框架，专为小型项目、API 接口、管理后台的快速开发设计。框架采用原生 PHP 编写，零第三方依赖，部署简单，上手容易。

**当前版本：**V1\.6\.0

**开源协议：**Apache\-2\.0

**作者：**小新

**核心特点：**零依赖、轻量级、MVC 架构、开箱即用

# 二、环境要求

|组件|要求|
|---|---|
|PHP 版本|\>= 7\.4|
|必选扩展|openssl、curl、pdo\_mysql、json|
|可选扩展|redis（用于 Redis 缓存功能）|
|Composer|可选，推荐使用|

# 三、安装部署

## 3\.1 Composer 安装（推荐）

```bash
composer create-project xiaououo/xiaophp 项目名
```

## 3\.2 手动安装

下载压缩包解压到网站目录即可，无需额外安装步骤。

## 3\.3 部署配置

**网站根目录必须指向 Public/ 目录**，这是 MVC 框架的统一规范，保护其他目录不被直接访问。

### Apache 部署

确保开启 mod\_rewrite 模块，Public 目录下已自带 \.htaccess 文件：

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [QSA,PT,L]
```

### Nginx 部署

参考 Public/nginx\.htaccess 文件配置伪静态：

```nginx
location / {
    try_files $uri $uri/ /index.php?$args;
}
```

### 开发调试（PHP 内置服务器）

```bash
cd Public
php -S localhost:8000 router.php
```

# 四、目录结构

```text
XiaoPHP/
├── App/
│   ├── Func/          # 自定义函数目录（自动加载）
│   └── Run/           # 控制器目录
├── Config/            # 配置文件目录
│   ├── App.php        # 应用配置
│   ├── Cache.php      # 缓存配置
│   ├── Logs.php       # 日志配置
│   ├── Middleware.php # 中间件配置
│   ├── Mysql.php      # 数据库配置
│   ├── Redis.php      # Redis配置
│   └── AliyunDns.php  # 阿里云DNS配置
├── Public/            # 网站入口目录
│   ├── index.php      # 入口文件
│   ├── router.php     # 内置服务器路由
│   └── .htaccess      # Apache伪静态
├── Route/             # 自定义路由
│   └── Route.php
├── Temp/              # 临时文件目录
│   └── Cache/         # 文件缓存目录
├── logs/              # 日志目录
│   ├── success/       # 成功请求日志
│   └── error/         # 失败请求日志
├── view/              # 视图模板目录
├── Whitelist/         # 路由白名单
│   └── Whitelist.php
├── XiaoPHP/           # 框架核心目录
│   ├── Tools/
│   │   ├── System/    # 系统工具类
│   │   └── Code/      # 业务工具类
│   ├── Error/         # 错误页面
│   ├── console.php    # 框架引导文件
│   ├── debug.php      # 调试信息展示
│   ├── Middleware.php # 中间件核心
│   ├── Routing.php    # 路由分发
│   └── xiao.php       # 控制台彩蛋
├── vendor/            # Composer自动加载
├── .env               # 环境变量配置
├── composer.json
└── composer.lock
```

# 五、配置说明

## 5\.1 环境变量 \.env

框架采用环境变量优先策略，\.env 文件配置会覆盖配置文件默认值。

```ini
# MySQL配置
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=xiaophp
DB_USER=root
DB_PASSWORD=123456

# Redis配置
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=

# 框架配置
DEBUG=true
ERROR_FORMAT=html
```

## 5\.2 应用配置 Config/App\.php

```php
return [
    "debug" => 'true',   // 调试模式：true | false
    "error" => 'html',   // 错误格式：html | json
];
```

## 5\.3 数据库配置 Config/Mysql\.php

```php
return [
    "host"     => '127.0.0.1',
    "port"     => '3306',
    "dbname"   => 'xiaophp',
    "user"     => 'root',
    "password" => '',
];
```

## 5\.4 中间件配置 Config/Middleware\.php

```php
return [
    "storage"       => 'cache',              // 存储方式：cache | redis
    "token_key"     => 'token',              // Token参数名
    "auth_mode"     => 'bearer,get,post,cookie', // 认证方式
    "cookie_name"   => 'auth_token',         // Cookie名称
    "cookie_expire" => '7200',               // 默认过期时间(秒)
];
```

## 5\.5 缓存配置 Config/Cache\.php

```php
return [
    "dir"    => '',     // 缓存目录，默认Temp/Cache
    "expire" => 3600,   // 默认过期时间(秒)
];
```

## 5\.6 日志配置 Config/Logs\.php

```php
return [
    "success" => true,  // 记录成功请求
    "error"   => true,  // 记录失败请求
];
```

# 六、路由系统

## 6\.1 自动路由（默认）

URL 格式：`域名/控制器名/方法名`

|URL|控制器文件|执行方法|
|---|---|---|
|/|App/Run/Index\.php|Main\(\)|
|/index|App/Run/Index\.php|Main\(\)|
|/user|App/Run/User\.php|Main\(\)|
|/user/login|App/Run/User\.php|login\(\)|

**1\.6 新特性：**路由、控制器文件名、方法名均**不区分大小写**。URL 中的 Query 参数（?id=1）不会影响路由匹配。

## 6\.2 自定义路由

在 Route/Route\.php 中配置：

```php
use XiaoPHP\systools\Config\Route;

// 格式：Route::add(请求方法, 路由路径, 控制器/方法, 控制器目录)
Route::add("GET", "/", "Index/Main", "App/Run");
Route::add("POST", "/api/user", "User/add", "App/Run");
```

自定义路由优先级高于自动路由。请求方法不匹配时返回 405 错误。

# 七、控制器

## 7\.1 创建控制器

在 App/Run/ 目录下创建 PHP 文件，文件名与类名一致：

```php
<?php
// App/Run/User.php
use XiaoPHP\systools\toolsbox\View;
use app\tools\MysqlTools;

class User
{
    // 默认方法：Main()，访问 /user
    public function Main()
    {
        $view = new View();
        $view->set(["title" => "用户列表"], "user")->show("index");
    }

    // 访问 /user/login
    public function login()
    {
        return "登录页面";
    }

    // 访问 /user/list，返回JSON
    public function list()
    {
        $db = new MysqlTools();
        $users = $db->table("users")->limit(10)->get();
        return json_encode($users);
    }
}
```

## 7\.2 控制器规范

- 默认方法名为 `Main()`

- 方法名不区分大小写（1\.6 特性）

- 控制器方法可以直接 return 输出内容

- 控制器类不需要继承任何基类

# 八、视图引擎

## 8\.1 模板语法

模板文件放在 view/ 目录下，使用 \.html 扩展名。

|语法|说明|示例|
|---|---|---|
|\{\{$变量名\}\}|输出普通变量|\{\{$title\}\}|
|\{\{$变量\.子属性\}\}|输出数组属性|\{\{$user\.name\}\}|

## 8\.2 基本用法

```php
use XiaoPHP\systools\toolsbox\View;

$view = new View();

// 链式调用
$view->set(["title" => "首页", "content" => "欢迎"], "index")->show("index");

// 分步调用（数据自动合并）
$view->set(["title" => "首页"]);
$view->set(["content" => "欢迎"]);
$view->show("index");
```

## 8\.3 子目录模板

```php
// 渲染 view/user/login.html
$view->set(["title" => "登录"], "user")->show("login");
```

**安全机制：**视图引擎自动过滤路径穿越（\.\.），并通过 realpath\(\) 确保模板文件在 view/ 目录内，防止任意文件读取漏洞。

# 九、数据库操作

## 9\.1 基本查询

```php
use app\tools\MysqlTools;

$db = new MysqlTools();

// 查询所有
$users = $db->table("users")->get();

// 查询单条
$user = $db->table("users")->where("id", "1")->first();

// 链式条件查询
$list = $db->table("users")
    ->where("status", "1")
    ->where("age", ">= 18")
    ->order("id desc")
    ->limit(10)
    ->offset(0)
    ->get();
```

## 9\.2 where 条件

```php
// 等于（默认）
$db->table("users")->where("id", "1");

// 比较运算符
$db->table("users")->where("age", ">= 18");
$db->table("users")->where("status", "!= 0");

// IN 查询
$db->table("users")->where("id", [1, 2, 3, 5]);
```

## 9\.3 模糊查询

```php
// 两端模糊：%keyword%
$db->table("users")->whereLike("name", "张三");

// 左模糊：%keyword
$db->table("users")->whereLike("name", "张三", "left");

// 右模糊：keyword%
$db->table("users")->whereLike("name", "张三", "right");

// 多列模糊搜索（OR关系）
$db->table("users")->whereMultiLike(["name", "email", "phone"], "关键词");

// 全表所有列模糊搜索
$db->table("users")->whereFullLike("关键词");
```

## 9\.4 写入操作

```php
// 插入数据，返回自增ID
$id = $db->table("users")->insert([
    "name"   => "张三",
    "email"  => "zhangsan@example.com",
    "status" => 1,
]);

// 更新数据（必须带WHERE条件，防止全表更新）
$affected = $db->table("users")
    ->where("id", "1")
    ->update([
        "name"   => "李四",
        "status" => 0,
    ]);

// 删除数据（必须带WHERE条件，防止全表删除）
$db->table("users")->where("id", "1")->delete();
```

## 9\.5 辅助方法

```php
// 获取ID范围
$range = $db->table("users")->getMinMaxId();
// 返回：["min_id" => 1, "max_id" => 1000]
```

**安全提醒：**update 和 delete 操作必须带 where 条件，否则框架会抛出错误，防止误操作全表。所有查询使用 PDO 预处理，防 SQL 注入。

# 十、Redis 缓存

## 10\.1 基本使用

```php
use app\tools\RedisTools;

$redis = new RedisTools();

// 字符串操作
$redis->set("key", "value", 3600);  // 设置，带过期时间(秒)
$value = $redis->get("key");        // 获取
$redis->del("key");                 // 删除
$exists = $redis->exists("key");    // 判断是否存在

// 检查Redis是否可用
$available = $redis->isAvailable();
```

**自动容错：**Redis 连接失败时不会抛出致命错误，中间件等功能会自动降级到文件缓存。

# 十一、文件缓存

无需 Redis 也能使用的轻量缓存，基于文件存储。

```php
use XiaoPHP\systools\System\Cache;

$cache = new Cache();

// 设置缓存
$cache->set("key", ["data" => "value"], 3600);

// 获取缓存
$data = $cache->get("key");

// 删除缓存
$cache->delete("key");

// 清空所有缓存
$cache->clear();
```

缓存文件存储在 Temp/Cache/ 目录，key 自动用 md5 命名，自动过期清理。

# 十二、中间件与 Token 认证

框架内置 Token 认证中间件，支持多种认证方式和存储后端。

## 12\.1 启用中间件

在控制器方法中调用：

```php
use XiaoPHP\systools\Middleware;

class User
{
    public function info()
    {
        // 执行Token校验，失败自动返回401
        $middleware = new Middleware();
        $middleware->check();

        // 校验通过，继续业务逻辑
        return "用户信息";
    }
}
```

## 12\.2 签发 Token

```php
$middleware = new Middleware();

// 签发Token，自动根据配置存储（Redis或文件缓存）
// Cookie模式会自动设置HttpOnly Cookie
$token = md5(uniqid());
$middleware->setToken($token, ["uid" => 1, "username" => "admin"], 7200);
```

## 12\.3 销毁 Token

```php
$middleware = new Middleware();
$middleware->delToken($token);
```

## 12\.4 支持的认证方式

- **bearer**：HTTP 请求头 Authorization: Bearer xxx

- **get**：URL 参数 ?token=xxx

- **post**：POST 参数 token=xxx

- **cookie**：Cookie 自动读取

可同时配置多种方式，按顺序依次尝试获取。

# 十三、白名单机制

在 Whitelist/Whitelist\.php 中配置不需要认证的路由：

```php
use XiaoPHP\systools\Config\Whitelist;

Whitelist::add("/Index/Main");
Whitelist::add("/api/health");
Whitelist::add("/api/public/*");  // 支持通配符
```

中间件 check\(\) 时，白名单内的路径直接放行，不校验 Token。

# 十四、加密工具

## 14\.1 AES 加密

```php
use XiaoPHP\systools\System\AesTools;

// 加密
$encrypted = AesTools::encrypt("明文", "密钥");

// 解密
$decrypted = AesTools::decrypt($encrypted, "密钥");
```

## 14\.2 RSA 加密

```php
use XiaoPHP\systools\System\RSATools;

// 公钥加密
$encrypted = RSATools::publicEncrypt("明文", $publicKey);

// 私钥解密
$decrypted = RSATools::privateDecrypt($encrypted, $privateKey);
```

# 十五、HTTP 请求工具（Wget）

基于 cURL 封装的 HTTP 请求类，支持 5 种请求方式。

```php
use XiaoPHP\systools\toolsbox\Wget;

// GET 请求
$response = Wget::get("https://api.example.com/data");

// POST 表单提交
$response = Wget::post("https://api.example.com/submit", [
    "name"  => "张三",
    "email" => "test@example.com",
]);

// POST JSON（传字符串自动设Content-Type）
$response = Wget::post("https://api.example.com/api", json_encode($data));

// PUT 请求
$response = Wget::put("https://api.example.com/update/1", $data);

// DELETE 请求
$response = Wget::delete("https://api.example.com/delete/1");

// 下载文件
$result = Wget::download("https://example.com/file.zip", "/path/to/save.zip");

// 开启SSL验证（默认关闭）
$response = Wget::get("https://api.example.com", true);

// 自定义curl选项
$response = Wget::get($url, false, [
    CURLOPT_TIMEOUT => 60,
    CURLOPT_HTTPHEADER => ["Authorization: Bearer xxx"],
]);
```

# 十六、Json 工具

```php
use XiaoPHP\systools\toolsbox\Json;

// 输出JSON响应（自动设header并exit）
Json::encode(["code" => 200, "msg" => "success", "data" => []]);

// 解析JSON
$data = Json::decode($jsonString);

// 直接请求远程URL并解析JSON
$data = Json::wdecode("https://api.example.com/data.json");
```

# 十七、阿里云 DNS

内置阿里云 DNS API 封装，可直接操作域名解析。

```php
use app\tools\AliyunDns;

$dns = new AliyunDns();

// 获取域名列表
$domains = $dns->getDomainList();

// 获取解析记录
$records = $dns->getRecords("example.com");

// 添加解析记录
$dns->addRecord("example.com", "www", "A", "1.2.3.4");

// 修改解析记录
$dns->updateRecord($recordId, "www", "A", "5.6.7.8");

// 删除解析记录
$dns->deleteRecord($recordId);

// 启用/禁用解析
$dns->setRecordStatus($recordId, "Enable");
```

使用前需在 Config/AliyunDns\.php 配置 AccessKey。

# 十八、IP 获取工具

```php
use XiaoPHP\systools\System\Ipaddr;

// 获取客户端真实IP
$ip = Ipaddr::get();
```

自动识别 X\-Forwarded\-For、HTTP\_CLIENT\_IP 等代理头，支持 CDN 和反向代理环境。

# 十九、日志系统

框架自动记录所有请求日志，按天分割文件。

## 19\.1 日志分类

- **成功日志：**logs/success/YYYY\-MM\-DD\.logs

- **失败日志：**logs/error/YYYY\-MM\-DD\.logs

## 19\.2 日志格式

```
success 2026-07-14 12:00:00--[192.168.1.1]:/index-code:200
error 2026-07-14 12:01:00--[192.168.1.1]:/notfound-code:404
```

## 19\.3 手动记录日志

```php
use XiaoPHP\systools\System\Logs;

$log = new Logs();
$log->logs(0, 200);  // 成功日志
$log->logs(1, 500);  // 错误日志
```

# 二十、自定义函数

在 App/Func/ 目录下创建的 \.php 文件会被自动加载，适合放全局辅助函数：

```php
<?php
// App/Func/helpers.php

function dd($var) {
    var_dump($var);
    die;
}

function format_date($timestamp) {
    return date("Y-m-d H:i:s", $timestamp);
}
```

# 二十一、错误处理与调试

## 21\.1 调试模式

通过 \.env 的 DEBUG 参数控制：

- **开启（true）：**展示详细错误信息、调用堆栈、变量信息

- **关闭（false）：**生产环境，只返回简洁错误页

## 21\.2 错误页面

|错误码|说明|模板文件|
|---|---|---|
|400|请求参数错误|通用错误页|
|401|未授权|Error/401\.html|
|403|禁止访问|Error/403\.html|
|404|页面不存在|Error/404\.html|
|405|请求方法不允许|通用错误页|
|500|服务器内部错误|通用错误页|

## 21\.3 三层错误捕获

- 普通错误：set\_error\_handler 捕获

- 未捕获异常：set\_exception\_handler 捕获

- 致命错误：register\_shutdown\_function 捕获

## 21\.4 错误输出格式

通过 error 配置控制：

- **html：**返回美观的错误页面，适合网站项目

- **json：**返回 JSON 格式错误，适合 API 项目

# 二十二、V1\.6 版本更新日志

## 主要更新

- **路由不区分大小写：**URL、控制器文件名、方法名均不区分大小写，兼容性更好

- **URL 参数自动剥离：**Query String 不会影响路由匹配

- **白名单支持通配符：**Whitelist::add\("/api/\*"\) 支持批量匹配

- **开源协议变更：**从 MIT 调整为 Apache\-2\.0

- **中间件路径统一小写：**白名单匹配更准确

- **白名单自动加载：**无需手动引入，框架启动时自动加载

- **中间件自动加载：**框架启动时自动加载 Middleware 类

- **Temp 目录回归：**文件缓存目录正式纳入发行包

## 兼容性说明

- 1\.5 升级 1\.6 基本无缝，路由大小写不敏感属于增强功能

- 控制器写法、工具类调用方式与 1\.5 完全一致

- 开源协议变更为 Apache\-2\.0，商用更友好

# 二十三、最佳实践

1. **生产环境务必关闭 DEBUG：**避免泄露敏感信息

2. **网站根目录指向 Public：**保护配置文件和核心代码不被直接访问

3. **设置正确的文件权限：**logs/ 和 Temp/ 目录需要写入权限

4. **API 项目用 json 错误格式：**Config/App\.php 中 error 设为 json

5. **有 Redis 优先用 Redis：**中间件 Token 存储性能更好

6. **数据库操作必须带 where：**框架已做强制校验，养成好习惯

