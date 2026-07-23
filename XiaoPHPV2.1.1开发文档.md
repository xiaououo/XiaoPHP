# XiaoPHP v2.1.1 开发文档

> 轻量级 PHP MVC 框架，零外部依赖，开箱即用
> 作者：小新

---

## 目录

1. [项目概览](#1-项目概览)
2. [环境要求](#2-环境要求)
3. [目录结构](#3-目录结构)
4. [安装与配置](#4-安装与配置)
5. [应用（App）系统](#5-应用app系统)
6. [路由系统](#6-路由系统)
7. [控制器](#7-控制器)
8. [视图与模板](#8-视图与模板)
9. [模型与数据库](#9-模型与数据库)
10. [中间件与认证](#10-中间件与认证)
11. [Token 认证系统](#11-token-认证系统)
12. [CSRF 防护](#12-csrf-防护)
13. [白名单系统](#13-白名单系统)
14. [工具类](#14-工具类)
15. [依赖注入容器](#15-依赖注入容器)
16. [缓存系统](#16-缓存系统)
17. [日志系统](#17-日志系统)
18. [错误与调试](#18-错误与调试)
19. [命令行工具](#19-命令行工具)
20. [安全最佳实践](#20-安全最佳实践)

---

## 1. 项目概览

XiaoPHP 是一款完全零外部依赖的 PHP MVC 框架，专为追求极致轻量与高效的小型项目打造。

**核心特性：**

- **纯粹原生** — 不依赖任何第三方 Composer 包，部署只需 PHP 7.4+ 环境
- **自动路由 + 自定义路由** — 支持 URL 自动映射到控制器，也支持手动注册路由
- **ORM 查询构造器** — 链式调用，PDO 参数绑定防 SQL 注入
- **模板引擎** — 支持变量输出、自动 HTML 转义、原始输出
- **中间件系统** — Token 认证 + 白名单，支持 Bearer/Cookie/POST 三种方式
- **依赖注入容器** — 支持自动装配、单例管理、循环依赖检测
- **双存储后端** — 文件缓存 + Redis 任选
- **工具类丰富** — 缓存、Redis、加密（AES/RSA）、数据验证、HTTP 客户端等

---

## 2. 环境要求

- PHP 7.4+
- PDO MySQL 扩展
- （可选）Redis 扩展
- （可选）OpenSSL 扩展（用于 AES/RSA 加密）
- （可选）curl 扩展（用于 HTTP 请求）

---

## 3. 目录结构

```
XiaoPHP/
├── App/                          # 应用目录（业务代码）
│   ├── Index/                    # 示例应用：Index
│   │   ├── Controller/           # 控制器
│   │   │   └── Index.php         # 首页控制器
│   │   ├── Function/             # 业务工具函数
│   │   ├── Model/                # 数据模型
│   │   ├── View/                 # 视图模板
│   │   │   └── index.html        # 首页模板
│   │   └── app.json              # 应用配置文件
│   └── Loading.php               # 应用加载器
├── Config/                       # 全局配置
│   ├── App.php                   # 应用配置（调试模式等）
│   ├── Cache.php                 # 缓存配置
│   ├── Logs.php                  # 日志配置
│   ├── Middleware.php            # 中间件配置
│   ├── Mysql.php                 # 数据库配置
│   ├── Redis.php                 # Redis 配置
│   └── AliyunDns.php             # 阿里云 DNS 配置
├── Public/                       # 公开入口
│   ├── .htaccess                 # Apache URL 重写
│   ├── nginx.htaccess            # Nginx URL 重写配置参考
│   ├── router.php                # PHP 内置服务器路由
│   └── index.php                 # 系统入口文件
├── Route/                        # 自定义路由
│   └── Route.php                 # 路由注册文件
├── Temp/                         # 临时文件目录
│   └── Cache/                    # 文件缓存目录
├── Whitelist/                    # 白名单配置
│   └── Whitelist.php             # 白名单路由注册
├── XiaoPHP/                      # 框架核心
│   ├── System/
│   │   ├── Error/                # 错误页面模板
│   │   ├── Tools/
│   │   │   ├── App/              # 应用级工具（Mysql, Redis, 阿里云DNS）
│   │   │   ├── Config/           # 配置加载类
│   │   │   ├── Function/        # 功能类（Auth, Cache, Logs, View, Validate 等）
│   │   │   └── encrypt/          # 加密类（AES, RSA）
│   │   └── ...
│   ├── Middleware.php            # 中间件入口类
│   ├── Routing.php               # 路由解析与分发
│   ├── console.php               # 控制台自动加载
│   ├── debug.php                 # 调试信息页面
│   └── start.php                 # 错误处理 + 启动引导
├── composer.json
├── composer.lock
├── vendor/                       # Composer 自动加载
└── logs/                         # 日志目录
    ├── error/                    # 错误日志
    └── success/                  # 成功日志
```

---

## 4. 安装与配置

### 4.1 Nginx 配置参考

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/XiaoPHP/Public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 4.2 Apache 配置

`.htaccess` 已内置在 Public 目录，启用 `mod_rewrite` 即可。

### 4.3 PHP 内置服务器

```bash
cd XiaoPHP/Public
php -S 0.0.0.0:8080 router.php
```

### 4.4 环境配置

编辑 `.env` 文件配置数据库和 Redis 连接：

```ini
#mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=database_name
DB_USER=username
DB_PASSWORD=password
#redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=
#XiaoPHP
DEBUG=false
```

---

## 5. 应用（App）系统

XiaoPHP 采用多应用架构，每个应用是一个独立模块。

### 5.1 创建应用

使用命令行：

```bash
php App/Loading.php add
```

或者手动创建目录结构：

```
App/YourApp/
├── Controller/    # 控制器
├── Function/      # 业务工具函数
├── Model/         # 数据模型
├── View/          # 视图模板
└── app.json       # 应用配置
```

### 5.2 app.json 配置

```json
{
    "name": "YourApp",
    "status": "on",
    "description": "status值off为关闭应用文件加载,on启用应用文件加载",
    "version": "1.0.0"
}
```

将 `status` 设为 `off` 可临时禁用该应用，框架不会加载该应用下的任何文件。

### 5.3 应用加载机制

`App/Loading.php` 中的 `Loading::run()` 方法会：
1. 扫描 App 目录下所有子目录
2. 读取每个应用的 `app.json`，检查 `status`
3. 加载启用的应用中 `Config/`、`Function/`、`Model/` 子目录下的所有 PHP 文件

---

## 6. 路由系统

### 6.1 自动路由（默认）

URL 格式：`/{app}/{controller}/{method}`

示例：
```
/                      → 自动路由到 Index 应用的 Main 方法（通过 Route::add 注册）
/Index/Index/Main      → Index 应用 → Index 控制器 → Main 方法
/Admin/User/List       → Admin 应用 → User 控制器 → List 方法
/Admin/Setting         → Admin 应用 → Setting 控制器 → Main 方法（省略 method 时默认 Main）
```

路由匹配过程：
1. 解析 URL 得到 `app`、`controller`、`method` 三段
2. 先查找自定义路由（如匹配则直接走自定义路由）
3. 未匹配则走自动路由：在 `App/{app}/Controller/` 下查找控制器文件
4. 控制器类名不区分大小写查找
5. 方法名不区分大小写查找（默认方法名：`Main`）

### 6.2 自定义路由

编辑 `Route/Route.php`：

```php
use XiaoPHP\System\Config\Route;

// 注册路由: Route::add(HTTP方法, URL路径, "控制器类名", "应用名")
Route::add("GET", "/", "Index", "Index");
Route::add("GET", "/about", "About", "Index");
Route::add("POST", "/api/login", "Auth", "Admin");
```

自定义路由支持按 HTTP 方法（GET/POST/PUT/DELETE）区分。

### 6.3 URL 重写

Apache（`Public/.htaccess`）或 Nginx 将请求重写到 `Public/index.php`，框架内部解析 `$_SERVER["REQUEST_URI"]` 进行路由分发。

---

## 7. 控制器

### 7.1 基本控制器

所有控制器放在 `App/{app}/Controller/` 目录下。

```php
<?php
// App/Index/Controller/Index.php

class Index
{
    public function Main()
    {
        // 返回纯文本
        echo "你好世界";
    }

    public function About()
    {
        return "关于页面";
    }
}
```

### 7.2 控制器返回值

控制器方法可以通过 `echo` 直接输出，也可以通过 `return` 返回字符串，框架会自动捕获并输出。

### 7.3 依赖注入

控制器可以通过构造函数参数自动注入依赖：

```php
class User
{
    private $db;

    public function __construct(MysqlTools $db)
    {
        $this->db = $db;
    }

    public function List()
    {
        $users = $this->db->table("users")->get();
        // ...
    }
}
```

### 7.4 使用视图

```php
class Page
{
    public function Show()
    {
        $view = new View();
        $view->set(['title' => '页面标题', 'content' => '页面内容'], 'page');
        return $view->show('index');
    }
}
```

---

## 8. 视图与模板

### 8.1 模板位置

视图文件放在 `App/{app}/View/` 目录下，使用 `.html` 扩展名。

### 8.2 变量输出

在模板中使用 `{{ $var }}` 语法：

```html
<h1>{{ $title }}</h1>
<p>{{ $content }}</p>
```

变量默认经过 `htmlspecialchars` 自动转义，防止 XSS 攻击。

### 8.3 原始输出（不转义）

当需要输出 HTML 等原始内容时，使用 `{{! $var }}` 语法：

```html
<div>{{! $content }}</div>
```

### 8.4 对象属性访问

支持点号语法访问数组或对象属性：

```
{{ $user.name }}    → 相当于 $user['name'] 或 $user->name
{{ $user.email }}   → 相当于 $user['email']
```

### 8.5 手动转义/原始输出辅助函数

```php
echo View::e($string);    // HTML 转义输出
echo View::raw($string);  // 原始输出
```

### 8.6 子目录模板

在 `View::set()` 的第二个参数指定子目录路径：

```php
$view->set($data, 'admin/user');
// 渲染 App/{app}/View/admin/user/index.html
```

---

## 9. 模型与数据库

### 9.1 查询构造器（MysqlTools）

框架使用 PDO + Prepared Statements，所有参数都经过绑定，防止 SQL 注入。

```php
$db = new MysqlTools();

// 查询
$users = $db->table("users")
            ->where("status", 1)
            ->where("age", ">=18")
            ->order("id", "DESC")
            ->limit(10)
            ->offset(0)
            ->get();

// 查询单条
$user = $db->table("users")
           ->where("id", 1)
           ->first();

// 统计
$count = $db->table("users")
            ->where("status", 1)
            ->count();

// 求和
$total = $db->table("orders")
            ->where("status", "paid")
            ->sum("amount");

// 模糊搜索
$users = $db->table("users")
            ->whereLike("username", "admin", "both")  // %admin%
            ->get();

// 多列模糊搜索
$users = $db->table("users")
            ->whereMultiLike(["username", "nickname", "email"], "keyword")
            ->get();

// 全表搜索（所有列）
$users = $db->table("articles")
            ->whereFullLike("搜索关键词")
            ->get();

// IN 查询
$users = $db->table("users")
            ->where("id", [1, 2, 3, 4, 5])
            ->get();

// 插入
$id = $db->table("users")
         ->insert([
             "username" => "admin",
             "password" => "xxx",
             "status"   => 1
         ]);

// 更新（必须带 WHERE 条件）
$affected = $db->table("users")
               ->where("id", 1)
               ->update(["username" => "new_name"]);

// 删除（必须带 WHERE 条件）
$affected = $db->table("users")
               ->where("status", 0)
               ->delete();

// 事务
$db->beginTransaction();
try {
    $db->table("orders")->insert([...]);
    $db->table("inventory")->where("id", 1)->update([...]);
    $db->commit();
} catch (\Exception $e) {
    $db->rollBack();
}

// 关闭连接
$db->close();
```

> **安全说明：** 表名和列名只允许 `a-zA-Z0-9_` 字符，超出部分会被自动过滤。

### 9.2 模型类（Model）

```php
<?php
// App/Index/Model/UserModel.php

use XiaoPHP\System\Model;

class UserModel extends Model
{
    protected $table = 'users';  // 指定数据表名

    // 自定义查询方法
    public function findByUsername($username)
    {
        return $this->db->table($this->table)
                        ->where('username', $username)
                        ->first();
    }

    public function updateLogin($id, $ip)
    {
        return $this->db->table($this->table)
                        ->where('id', $id)
                        ->update([
                            'last_login_at' => date('Y-m-d H:i:s'),
                            'last_login_ip' => $ip,
                        ]);
    }
}
```

模型提供的基础方法：

| 方法 | 说明 |
|------|------|
| `find($id)` | 按主键查找 |
| `all($where)` | 获取所有记录 |
| `create($data)` | 插入记录 |
| `update($id, $data)` | 按主键更新 |
| `delete($id)` | 按主键删除（硬删除） |
| `save($data, $id)` | 自动判断插入或更新 |
| `paginate($page, $size, $where)` | 分页查询 |
| `db()` | 返回查询构造器实例（链式调用） |

---

## 10. 中间件与认证

### 10.1 中间件工作流程

```
请求到达
  ↓
Middleware::check()  ← 路由分发前执行
  ↓
白名单检查 → 在白名单内 → 跳过认证
  ↓ 不在白名单
Token 认证
  ↓ 通过
路由分发 → 控制器执行 → 响应
```

### 10.2 配置中间件

编辑 `Config/Middleware.php`：

```php
return [
    "storage"       => 'cache',                      // 存储方式：cache | redis
    "token_key"     => 'token',                      // 令牌参数名
    "auth_mode"     => 'bearer,post,cookie',         // 认证方式
    "cookie_name"   => 'auth_token',                 // Cookie 名称
    "cookie_expire" => '7200',                       // 过期时间（秒）
];
```

### 10.3 Token 认证支持的方式

| 方式 | 说明 | 获取位置 |
|------|------|----------|
| `bearer` | Bearer Token | `Authorization: Bearer <token>` 请求头 |
| `post` | POST 表单参数 | `$_POST['token']` |
| `cookie` | Cookie | `$_COOKIE['auth_token']` |

> **安全提示：** 不建议使用 `get` 方式，Token 会出现在 URL 中导致 Referer 泄漏和日志泄漏。

### 10.4 设置 Token

```php
$middleware = new Middleware();
$middleware->setToken("user_token_value", $userData, 7200);
```

### 10.5 删除 Token

```php
$middleware->delToken("user_token_value");
```

---

## 11. CSRF 防护

### 11.1 生成 CSRF Token

```php
$csrfToken = Auth::csrfToken();
```

### 11.2 在表单中使用

```html
<form method="POST" action="/Admin/User/Save">
    <input type="hidden" name="_csrf_token" value="<?php echo Auth::csrfToken(); ?>">
    <!-- 其他表单字段 -->
</form>
```

### 11.3 在控制器中校验

```php
class User
{
    public function Save()
    {
        $token = $_POST['_csrf_token'] ?? '';
        if (!Auth::checkCsrf($token)) {
            // CSRF 校验失败
            Error(403, 'CSRF 令牌无效');
        }
        // 处理表单数据...
    }
}
```

### 11.4 双 Token 机制

框架采用双 Token 机制解决浏览器自动二次请求的问题：
- `csrf_token`：当前有效的 Token
- `csrf_token_prev`：上一个 Token（校验通过后自动轮换）

校验成功后，Token 自动轮换，旧的 Token 作为 `csrf_token_prev` 保留，短时间内仍可使用。

---

## 12. 白名单系统

### 12.1 配置白名单

编辑 `Whitelist/Whitelist.php`：

```php
use XiaoPHP\System\Config\Whitelist;

Whitelist::add("/");                 // 首页
Whitelist::add("/login");            // 登录页
Whitelist::add("/Login/DoLogin");    // 登录接口
Whitelist::add("/sitemap.xml");      // Sitemap
Whitelist::add("/robots.txt");       // Robots
```

### 12.2 通配符支持

白名单支持 `*` 通配符，例如 `Whitelist::add("/public/*")` 会匹配所有以 `/public/` 开头的路径。

> **安全警告：** 不要使用 `Whitelist::add("/*")`，这会绕过所有 Token 认证，使中间件完全失效。

### 12.3 白名单管理方法

| 方法 | 说明 |
|------|------|
| `add($url)` | 添加白名单路径 |
| `remove($url)` | 移除白名单路径 |
| `get()` | 获取所有白名单 |
| `clear()` | 清空白名单 |
| `exists($url)` | 检查路径是否在白名单中 |
| `check($url)` | 检查路径是否匹配白名单（支持通配符） |

---

## 13. 依赖注入容器

### 13.1 容器基础用法

```php
$container = Container::getInstance();

// 绑定服务
$container->bind('db', function ($c) {
    return new MysqlTools();
});

// 解析服务
$db = $container->make('db');

// 注册单例
$container->singleton(Logs::class, function () {
    return new Logs();
});

// 自动装配（无构造函数依赖）
$controller = $container->make('UserController');
```

### 13.2 依赖注入

容器支持通过构造函数参数类型提示自动注入：

```php
class UserController
{
    private $db;
    private $middleware;

    // 容器会自动解析这些参数
    public function __construct(MysqlTools $db, Middleware $middleware)
    {
        $this->db = $db;
        $this->middleware = $middleware;
    }
}
```

### 13.3 Service Provider

核心服务通过 `ServiceProvider::register()` 注册到容器：

```php
// 在 XiaoPHP/System/Tools/Config/ServiceProvider.php 中
$container->singleton(MysqlTools::class, ...);
$container->singleton(RedisTools::class, ...);
$container->singleton(Cache::class, ...);
$container->singleton(Logs::class, ...);
$container->singleton(Middleware::class, ...);
$container->singleton(Validate::class, ...);
```

所有核心服务都可以通过容器获取，也可以通过别名快速访问：

```php
$db = $container->make('db');
$redis = $container->make('redis');
$logs = $container->make('logs');
```

---

## 14. 工具类

### 14.1 Validate — 数据验证

```php
use XiaoPHP\System\Validate;

Validate::phone("13800138000");       // 手机号验证
Validate::email("user@example.com");  // 邮箱验证
Validate::username("小新123");        // 用户名（2-20位，支持中文）
Validate::password("Abc123");         // 密码（6-20位，字母+数字）
Validate::url("https://example.com"); // URL 验证
Validate::ip("192.168.1.1");          // IP 地址
Validate::idCard("110101199001011234"); // 身份证号
Validate::numeric("123.45");          // 数字验证
Validate::between(50, 1, 100);        // 范围验证
Validate::length("hello", 1, 10);     // 字符串长度
Validate::date("2026-07-19");         // 日期格式
Validate::time("14:30:00");           // 时间格式
Validate::postcode("100000");         // 邮政编码
Validate::qq("123456789");            // QQ 号
```

所有验证方法验证通过返回原值，失败返回 `false`。

### 14.2 Wget — HTTP 客户端

```php
use XiaoPHP\System\Wget;

// GET 请求
$response = Wget::get("https://api.example.com/data");

// POST 请求
$response = Wget::post("https://api.example.com/login", [
    "username" => "admin",
    "password" => "123456"
]);

// PUT 请求
$response = Wget::put("https://api.example.com/update", $data);

// DELETE 请求
$response = Wget::delete("https://api.example.com/delete/1");

// 下载文件
$response = Wget::download("https://example.com/file.zip", "/path/to/save.zip");

// SSL 验证
$response = Wget::get("https://secure.example.com", true);  // 第二个参数开启 SSL 验证
```

> **安全提示：** 如果业务逻辑需要处理用户传入的 URL，请自行添加域名白名单，防止 SSRF 攻击。

### 14.3 Json — JSON 工具

```php
use XiaoPHP\System\Json;

// 输出 JSON 并终止
Json::encode(['code' => 0, 'msg' => 'success']);

// 解析 JSON
$data = Json::decode('{"name": "XiaoPHP"}');

// 请求外部 JSON 接口
$data = Json::wdecode("https://api.example.com/data");
```

### 14.4 Auth — 前台登录认证

```php
use XiaoPHP\System\Auth;

// 登录尝试
$result = Auth::attempt("admin", "password123");
if ($result['success']) {
    // 登录成功
} else {
    echo $result['message']; // '用户不存在' / '账号已被禁用' / '密码错误'
}

// 检查是否已登录
if (Auth::check()) {
    $user = Auth::user();   // 获取用户信息
    $id = Auth::id();        // 获取用户 ID
    $role = Auth::role();    // 获取角色
}

// 角色权限检查
Auth::hasRole('admin');             // 是否是指定角色
Auth::atLeast('editor');            // 是否至少 editor 级别

// 登出
Auth::logout();

// 密码工具
$salt = Auth::generateSalt();
$hash = Auth::hashPassword("mypassword", $salt);
```

> 注意：Auth 类是基于 Session 的用户登录认证，与中间件的 Token 认证是不同的系统。

### 14.5 加密工具

```php
use XiaoPHP\System\AesTool;

// AES 加密
$encrypted = AesTool::encode("hello", "secret_key");
// AES 解密
$decrypted = AesTool::decode($encrypted, "secret_key");
```

### 14.6 IP 地址获取

```php
use XiaoPHP\System\Ipaddr;

$ip = Ipaddr::get();  // 获取客户端 IP
```

---

## 15. 缓存系统

### 15.1 文件缓存

```php
use XiaoPHP\System\Cache;

$cache = new Cache();

// 设置缓存（默认过期时间 3600 秒）
$cache->set("user_123", $userData, 7200);

// 获取缓存
$data = $cache->get("user_123");

// 删除缓存
$cache->delete("user_123");

// 清空所有缓存
$cache->clear();
```

### 15.2 Redis 缓存

Redis 操作通过 `RedisTools` 类封装，支持所有 Redis 方法：

```php
use XiaoPHP\System\Tools\App\RedisTools;

$redis = new RedisTools();

$redis->set("key", "value");
$value = $redis->get("key");
$redis->expire("key", 3600);
$redis->del("key");
```

---

## 16. 日志系统

### 16.1 日志配置

编辑 `Config/Logs.php` 控制日志开关：

```php
return [
    "success" => 'true',  // 是否记录成功日志
    "error"   => 'true',  // 是否记录错误日志
];
```

### 16.2 日志记录

日志自动由框架在中件间和路由中记录，格式如下：

```
success 2026-07-19 14:30:00--[127.0.0.1]:/Index/Index/Main-code:200
error 2026-07-19 14:30:01--[127.0.0.1]:/Admin/Auth/Login-code:401
```

日志文件按日期存储：
- `logs/success/2026-07-19.logs`
- `logs/error/2026-07-19.logs`

---

## 17. 错误与调试

### 17.1 调试模式

在 `.env` 或 `Config/App.php` 中控制：

```php
// Config/App.php
"debug" => 'false',    // 关闭调试模式
"error" => 'html',      // 错误响应格式
```

- `debug = false`：生产模式，显示"系统繁忙，请稍后再试"
- `debug = true`：开发模式，显示详细错误信息（包括代码片段、调用堆栈、请求上下文）

> **安全提示：** 生产环境务必关闭调试模式（`DEBUG=false`），否则会暴露 $_GET、$_POST、$_SESSION、$_COOKIE 等敏感信息。

### 17.2 主动触发错误

```php
Error(404, "页面未找到");
Error(401, "缺少认证令牌");
Error(403, "权限不足");
Error(500, "服务器内部错误");
```

### 17.3 自定义错误页面

在 `XiaoPHP/System/Error/` 目录下创建 `{code}.html` 文件即可自定义错误页面。

---

## 18. 命令行工具

### 18.1 创建应用

```bash
php App/Loading.php add
```

进入交互模式后输入应用名称，会自动创建应用的目录结构。

---

## 19. 安全最佳实践

### 19.1 部署前 Checklist

- [ ] 删除开发环境和测试文件
- [ ] 设置 `Config/App.php` 中 `debug` 为 `false`
- [ ] 修改 `.env` 中的数据库密码
- [ ] 清理 `logs/` 目录下的测试日志
- [ ] 配置 `Whitelist/Whitelist.php`，**不要使用 `/*` 通配符**
- [ ] 检查 `Config/Middleware.php` 中的 `auth_mode`，移除 `get` 方式
- [ ] 确保 `Public/` 是 Web 服务器的文档根目录

### 19.2 常见安全注意事项

| 项目 | 建议 |
|------|------|
| SQL 注入 | 框架已使用 PDO Prepared Statements，但避免使用原生 SQL 查询 |
| XSS | 模板引擎默认开启转义，使用 `{{! }}` 原始输出时确保内容可信 |
| CSRF | 在需要写操作的表单中使用 `Auth::csrfToken()` + `Auth::checkCsrf()` |
| Token 认证 | 白名单不要过度放宽，每个接口都需要评估是否需要认证 |
| 文件上传 | 前端如实现了上传功能，务必验证文件类型和大小 |
| 调试模式 | 生产环境必须关闭 |
| 密码存储 | 使用 `Auth::hashPassword()` + `Auth::generateSalt()` |
| 日志 | 日志目录不应暴露到 Web 可访问路径下 |

### 19.3 API 推荐实践

```php
// 统一 JSON 响应
use XiaoPHP\System\Json;

class ApiController
{
    public function GetUser()
    {
        // 验证 Token（已在中间件层完成）
        // 校验 CSRF
        $csrfToken = $_POST['_csrf_token'] ?? '';
        if (!Auth::checkCsrf($csrfToken)) {
            Json::encode(['code' => 403, 'msg' => 'CSRF 校验失败']);
        }

        // 业务逻辑
        $user = (new UserModel())->find($_GET['id'] ?? 0);
        Json::encode(['code' => 0, 'data' => $user]);
    }
}
```

---

> **文档版本：** v2.1.1
> **最后更新：** 2026-07-24
> **作者：** 小新
> **框架主页：** https://xiaophp.xiaououo.com
