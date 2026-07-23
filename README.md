# XiaoPHP 2.1.0 使用文档

> 基于 PHP 的轻量级 MVC 框架，适合小型项目快速开发

---

## 目录

1. [项目概览](#项目概览)
2. [目录结构](#目录结构)
3. [安装与配置](#安装与配置)
4. [路由系统](#路由系统)
5. [控制器](#控制器)
6. [视图](#视图)
7. [模型与数据库](#模型与数据库)
8. [中间件与白名单](#中间件与白名单)
9. [工具类](#工具类)
10. [命令行工具](#命令行工具)
11. [错误处理](#错误处理)

---

## 项目概览

XiaoPHP 是一个轻量级 PHP MVC 框架，具有以下特点：

- **简洁的路由**：支持自动路由和自定义路由
- **ORM 支持**：内置 MysqlTools 查询构造器
- **模板引擎**：支持变量输出和自动转义
- **中间件系统**：支持 Token 认证和白名单
- **工具类丰富**：包含缓存、Redis、加密、验证等
- **命名空间规范**：框架核心 `XiaoPHP\System\` 与业务逻辑分离

---

## 目录结构

```
XiaoPHPV2.0/
├── App/                    # 应用目录
│   ├── Index/              # 示例应用
│   │   ├── Controller/     # 控制器
│   │   ├── View/           # 视图
│   │   ├── Model/          # 模型
│   │   ├── Function/       # 应用级函数
│   │   ├── Config/         # 应用级配置
│   │   └── app.json        # 应用配置文件
│   └── Loading.php         # 应用加载器
├── Config/                 # 系统配置
│   ├── App.php             # 应用配置
│   ├── Mysql.php           # 数据库配置
│   ├── Redis.php           # Redis 配置
│   ├── Cache.php           # 缓存配置
│   ├── Logs.php            # 日志配置
│   ├── Middleware.php      # 中间件配置
│   └── AliyunDns.php       # 阿里云 DNS 配置
├── Public/                 # Web 入口
│   ├── index.php           # 入口文件
│   ├── .htaccess           # Apache 配置
│   └── nginx.htaccess      # Nginx 配置
├── Route/                  # 路由配置
│   └── Route.php           # 自定义路由
├── Whitelist/              # 白名单配置
│   └── Whitelist.php       # 白名单路由
├── XiaoPHP/                # 框架核心
│   ├── System/             # 系统组件
│   │   ├── Tools/          # 工具类
│   │   │   ├── App/        # 应用工具
│   │   │   ├── Config/     # 配置管理
│   │   │   ├── Function/   # 功能类
│   │   │   └── encrypt/    # 加密类
│   │   └── Error/          # 错误处理
│   ├── console.php         # 依赖加载
│   ├── Routing.php         # 路由解析
│   ├── Middleware.php      # 中间件入口
│   ├── debug.php           # 调试信息
│   └── start.php           # 错误处理注册
├── .env                    # 环境变量
├── composer.json           # Composer 配置
└── logs/                   # 日志目录
```

---

## 安装与配置

### 环境要求

- PHP >= 7.4
- MySQL (PDO)
- Redis (可选)

### 配置文件

#### .env 文件

```env
DEBUG=true
DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASSWORD=123456
DB_NAME=xiaophp
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=
```

#### 系统配置

配置文件位于 `Config/` 目录，支持通过 `Env::Load()` 读取环境变量：

```php
// Config/App.php
return [
    "debug" => Env::Load(null, "DEBUG") ?? 'false',
    "error" => 'html',  // 错误响应格式: html | json
];
```

---

## 路由系统

### URL 格式

```
http://domain/应用/控制器/方法
```

### 自动路由

框架会自动将 URL 映射到控制器：

| URL | 应用 | 控制器 | 方法 |
|-----|------|--------|------|
| `/Index/Index/Main` | Index | Index | Main |
| `/Admin/User/List` | Admin | User | List |
| `/Index/Index` | Index | Index | Main (默认) |

### 自定义路由

在 `Route/Route.php` 中注册自定义路由：

```php
use XiaoPHP\System\Config\Route;

Route::add("GET", "/", "Index", "Index");
Route::add("POST", "/api/login", "Auth", "Api");
Route::add("GET", "/api/users", "User", "Api");
```

**参数说明**：
- 第一个参数：请求方法 (`GET`, `POST`, `PUT`, `DELETE`)
- 第二个参数：路由路径
- 第三个参数：控制器名称
- 第四个参数：应用名称

---

## 控制器

### 创建控制器

在 `App/{应用}/Controller/` 目录下创建控制器文件：

```php
<?php
/**
 * 用户控制器
 * Date: 2026-07-18
 * Author: 小新
 * SystemName: XiaoPHP
 */

use XiaoPHP\System\View;
use XiaoPHP\System\Json;

class User
{
    public function Main()
    {
        $view = new View();
        $view->set(["title" => "用户列表"])->show("index");
    }

    public function GetInfo()
    {
        Json::encode([
            'code' => 0,
            'data' => ['name' => '张三']
        ]);
    }
}
```

### 基类控制器

控制器目录下的 `*Base.php` 文件会优先加载，可用于封装公共逻辑：

```php
// App/Admin/Controller/AdminBase.php
class AdminBase
{
    public function __construct()
    {
        Auth::requireLogin();
    }
}
```

---

## 视图

### 模板语法

视图文件位于 `App/{应用}/View/` 目录，支持以下语法：

```html
<!-- 自动转义（推荐） -->
<h1>{{ $title }}</h1>

<!-- 原始输出（不转义） -->
<div>{{! $content }}</div>
```

### 在控制器中使用

```php
use XiaoPHP\System\View;

class Index
{
    public function Main()
    {
        $view = new View();
        $view->set([
            "h1" => "XiaoPHP V 2.0.0",
            "title" => "Hello World!"
        ])->show("index");
    }
}
```

### View 类方法

| 方法 | 说明 |
|------|------|
| `set($data, $path)` | 设置模板变量和路径 |
| `show($filename)` | 渲染模板文件 |
| `setAutoEscape($enabled)` | 设置是否自动转义 |
| `e($string)` | 静态方法，HTML 转义 |
| `raw($string)` | 静态方法，原始输出 |

---

## 模型与数据库

### 创建模型

继承 `XiaoPHP\System\Model` 类：

```php
<?php
/**
 * 用户模型
 * Date: 2026-07-18
 * Author: 小新
 * SystemName: XiaoPHP
 */

use XiaoPHP\System\Model;

class UserModel extends Model
{
    protected $table = 'users';
}
```

### 基础操作

```php
$userModel = new UserModel();

// 查询单条
$user = $userModel->find(1);

// 查询全部
$users = $userModel->all();

// 条件查询
$activeUsers = $userModel->all(['status' => 1]);

// 插入
$id = $userModel->create([
    'username' => 'test',
    'password' => '123456'
]);

// 更新
$rows = $userModel->update(1, ['status' => 1]);

// 删除
$rows = $userModel->delete(1);

// 分页
$result = $userModel->paginate(1, 10, ['status' => 1]);
// 返回: ['data' => [], 'total' => 100, 'page' => 1, 'size' => 10]
```

### MysqlTools 查询构造器

直接使用 `MysqlTools` 进行复杂查询：

```php
use XiaoPHP\System\Tools\App\MysqlTools;

$db = new MysqlTools();

// 链式查询
$users = $db->table('users')
            ->where('status', 1)
            ->whereLike('username', '张')
            ->order('id', 'DESC')
            ->limit(10)
            ->offset(0)
            ->get();

// 统计
$count = $db->table('users')->where('status', 1)->count();

// 事务
$db->beginTransaction();
try {
    $db->table('orders')->insert($data);
    $db->table('inventory')->where('id', $id)->update(['stock' => 99]);
    $db->commit();
} catch (\Exception $e) {
    $db->rollBack();
}
```

**MysqlTools 方法列表**：

| 方法 | 说明 |
|------|------|
| `table($name)` | 指定表名 |
| `where($col, $val)` | 添加 WHERE 条件 |
| `whereLike($col, $val, $pos)` | LIKE 查询 |
| `whereMultiLike($cols, $val)` | 多字段 LIKE |
| `whereFullLike($val)` | 全表字段 LIKE |
| `order($col, $dir)` | 排序 |
| `limit($n)` | 限制条数 |
| `offset($n)` | 偏移量 |
| `get()` | 获取多条记录 |
| `first()` | 获取单条记录 |
| `insert($data)` | 插入记录 |
| `update($data)` | 更新记录 |
| `delete()` | 删除记录 |
| `count()` | 统计数量 |
| `sum($col)` | 求和 |

---

## 中间件与白名单

### 白名单配置

在 `Whitelist/Whitelist.php` 中配置无需认证的路由：

```php
use XiaoPHP\System\Config\Whitelist;

// 精确匹配
Whitelist::add("/");
Whitelist::add("/login");

// 通配符匹配
Whitelist::add("/*");
Whitelist::add("/api/*");
```

### Whitelist 类方法

| 方法 | 说明 |
|------|------|
| `add($url)` | 添加白名单路由 |
| `remove($url)` | 移除白名单路由 |
| `clear()` | 清空白名单 |
| `exists($url)` | 检查路由是否存在 |
| `check($url)` | 检查请求路径是否在白名单 |
| `get()` | 获取所有白名单路由 |

### Token 认证

中间件支持多种认证方式：

```php
// Config/Middleware.php
return [
    'token_key' => 'token',
    'storage' => 'redis',      // redis | cache
    'auth_mode' => 'bearer,cookie',  // bearer, get, post, cookie
    'cookie_name' => 'auth_token',
    'cookie_expire' => 7200,
];
```

**使用方式**：

```php
$middleware = new \XiaoPHP\System\Middleware();

// 设置 Token
$middleware->setToken($token, ['user_id' => 1]);

// 删除 Token
$middleware->delToken($token);
```

---

## 工具类

### Auth 认证类

```php
use XiaoPHP\System\Auth;

// 登录
$result = Auth::attempt($username, $password);

// 登出
Auth::logout();

// 检查登录状态
if (Auth::check()) {
    $user = Auth::user();
    $userId = Auth::id();
}

// 角色权限
Auth::requireLogin();           // 要求登录
Auth::requireRole('admin');     // 要求 admin 角色
Auth::hasRole('editor');        // 检查角色
Auth::atLeast('admin');         // 至少 admin 权限
```

### Validate 验证类

```php
use XiaoPHP\System\Validate;

Validate::phone('13800138000');      // 手机号
Validate::email('test@example.com'); // 邮箱
Validate::username('张三');          // 用户名
Validate::password('Abc123');        // 密码
Validate::idCard('110101199001011234'); // 身份证
Validate::url('https://example.com'); // URL
Validate::ip('192.168.1.1');         // IP 地址
Validate::date('2026-07-18');        // 日期
Validate::numeric(123);              // 数字
Validate::between(50, 1, 100);       // 范围
Validate::length('hello', 2, 10);    // 长度
Validate::time('14:30:00');          // 时间
Validate::postcode('100000');        // 邮政编码
Validate::qq('12345678');            // QQ 号
```

### Cache 缓存类

```php
use XiaoPHP\System\Cache;

$cache = new Cache();

$cache->set('key', 'value', 3600);  // 设置缓存（有效期 3600 秒）
$value = $cache->get('key');         // 获取缓存
$cache->delete('key');               // 删除缓存
$cache->clear();                     // 清空所有缓存
```

### RedisTools Redis 工具

```php
use XiaoPHP\System\Tools\App\RedisTools;

$redis = new RedisTools();

$redis->set('key', 'value');
$redis->get('key');
$redis->setex('key', 3600, 'value'); // 设置过期时间
$redis->del('key');
$redis->exists('key');
```

### Wget HTTP 请求

```php
use XiaoPHP\System\Wget;

// GET 请求
$response = Wget::get('https://api.example.com/data');

// HTTPS 请求
$response = Wget::get('https://api.example.com/data', true);

// 自定义选项
$response = Wget::get('https://api.example.com/data', false, [
    CURLOPT_TIMEOUT => 10,
    CURLOPT_USERAGENT => 'XiaoPHP',
]);
```

### Json JSON 处理

```php
use XiaoPHP\System\Json;

// 输出 JSON 并退出
Json::encode(['code' => 0, 'data' => []]);

// 解码 JSON
$data = Json::decode('{"key":"value"}');

// 从 URL 获取 JSON
$data = Json::wdecode('https://api.example.com/data');
```

### AesTool AES 加密

```php
use XiaoPHP\System\AesTool;

$key = 'your_secret_key';
$plaintext = 'hello world';

// 加密
$ciphertext = AesTool::encode($plaintext, $key);

// 解密
$decrypted = AesTool::decode($ciphertext, $key);
```

### RSATool RSA 加密

```php
use XiaoPHP\System\RSATool;

$rsa = new RSATool();

// 公钥加密
$encrypted = $rsa->encode('hello', $publicKey);

// 私钥解密
$decrypted = $rsa->decode($encrypted, $privateKey);
```

### Logs 日志类

```php
use XiaoPHP\System\Logs;

$logs = new Logs();

$logs->logs(0, 200);  // 成功日志（code 200）
$logs->logs(1, 500);  // 错误日志（code 500）
```

---

## 命令行工具

### 应用管理命令

#### 创建应用

```bash
php App/Loading.php add
```

执行后进入交互模式，按照提示输入应用名称：

```
XiaoPHP V 2.1.0
命令模式：创建应用-输入Exit退出
请输入应用名称: Blog
已生成默认配置文件: /path/to/App/Blog/app.json
```

**退出命令**：输入 `Exit` 退出交互模式。

#### 创建的目录结构

框架会自动创建以下目录：

```
App/{应用名}/
├── Config/         # 应用级配置文件
├── Controller/     # 控制器文件
├── Function/       # 应用级函数
├── Model/          # 模型文件
├── View/           # 视图模板
└── app.json        # 应用配置文件
```

#### app.json 配置说明

每个应用都有一个 `app.json` 配置文件：

```json
{
    "name": "Blog",
    "status": "on",
    "description": "status值off为关闭应用文件加载,on启用应用文件加载",
    "version": "1.0.0"
}
```

| 字段 | 说明 |
|------|------|
| `name` | 应用名称 |
| `status` | 启用状态：`on` 启用，`off` 禁用 |
| `description` | 应用描述 |
| `version` | 应用版本号 |

### Loading 类自动加载机制

`Loading` 类负责自动加载所有应用的配置、函数和模型文件：

```php
use Loading;

// 自动加载所有启用的应用文件
Loading::run();
```

**加载规则**：

1. 扫描 `App/` 目录下所有子目录作为应用
2. 检查每个应用的 `app.json` 配置
3. 如果 `status` 为 `off`，跳过该应用
4. 自动加载以下目录的 PHP 文件：
   - `Config/` - 应用级配置
   - `Function/` - 应用级函数
   - `Model/` - 应用级模型
5. `Controller/` 和 `View/` 目录不会自动加载，按需加载

**注意**：`Loading::run()` 在框架启动时由 `console.php` 自动调用，无需手动调用。

---

## 错误处理

### 错误响应

框架提供统一的错误处理函数：

```php
\Error($code, $info);
```

**示例**：

```php
\Error(404, "页面不存在");
\Error(401, "未授权");
\Error(500, "服务器错误");
```

### 错误响应格式

根据 `Config/App.php` 中的配置：

```php
return [
    "error" => 'html',  // html | json
];
```

**HTML 格式**：返回对应的错误页面（`XiaoPHP/System/Error/{code}.html`）

**JSON 格式**：
```json
{
    "code": 404,
    "date": "2026-07-18 14:30:00",
    "data": {"msg": "页面不存在"}
}
```

### 调试模式

开启调试模式后，错误会显示详细的调试信息：

```env
DEBUG=true
```

---

## 部署配置

### Apache (.htaccess)

项目默认 `.htaccess` 文件为空，需手动添加以下内容：

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ index.php/$1 [L]
```

### Nginx

参考 `Public/nginx.htaccess` 文件：

```nginx
location / {
    try_files $uri $uri/ /index.php?$args;
}
```

---

## 安全注意事项

1. **SQL 注入**：使用 `MysqlTools` 的参数绑定，自动防止 SQL 注入
2. **XSS 攻击**：视图模板默认自动转义，使用 `{{! $var }}` 输出原始内容时需谨慎
3. **CSRF 防护**：当前默认关闭 CSRF 校验，如需启用请修改 `Auth::checkCsrf()` 方法
4. **会话安全**：`Auth` 类已启用 `httponly` 和 `samesite` Cookie 选项
5. **路径遍历**：视图和控制器加载均经过路径校验，防止目录遍历攻击

---

## 开发规范

1. **文件头注释**：每个 PHP 文件必须包含标准文件头注释
2. **命名空间**：框架核心使用 `XiaoPHP\System\`，业务逻辑使用 `XiaoPHP\app\tools\`
3. **全局函数**：避免使用全局函数，封装为类方法
4. **数据库操作**：使用 `MysqlTools` 的 `where()` 方法，避免直接拼接 SQL
5. **视图输出**：默认使用 `{{ $var }}` 自动转义，仅在确认安全时使用 `{{! $var }}`

---

*XiaoPHP 2.1.0 | Author: 小新 | SystemName: XiaoPHP*
