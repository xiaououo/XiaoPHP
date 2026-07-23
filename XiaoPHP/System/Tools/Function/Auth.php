<?php
/**
 * 后台认证工具（Session 方式）
 * Date: 2026-07-18
 * Author: 小新
 * SystemName: XiaoPHP
 */

namespace XiaoPHP\System;

class Auth
{
    const SESSION_KEY = 'admin_user';
    const SESSION_ID_KEY = 'admin_user_id';

    /** @var array|null 当前用户缓存 */
    private static $currentUser = null;

    /**
     * 启动会话（幂等）
     */
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // 防止会话固定攻击
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_samesite', 'Lax');
            session_start();
        }
    }

    /**
     * 尝试登录
     * @return array ['success'=>bool, 'message'=>string]
     */
    public static function attempt(string $username, string $password): array
    {
        self::startSession();

        $userModel = new \UserModel();
        $user = $userModel->findByUsername($username);

        if (empty($user)) {
            return ['success' => false, 'message' => '用户不存在'];
        }

        if ((int)$user['status'] !== 1) {
            return ['success' => false, 'message' => '账号已被禁用'];
        }

        // 校验密码
        $passwordWithSalt = $password . $user['salt'];
        if (!password_verify($passwordWithSalt, $user['password'])) {
            return ['success' => false, 'message' => '密码错误'];
        }

        // 登录成功
        self::login($user);

        // 更新最后登录信息
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $userModel->updateLogin((int)$user['id'], $ip);

        return ['success' => true, 'message' => '登录成功'];
    }

    /**
     * 登录指定用户（直接创建会话）
     */
    public static function login(array $user): void
    {
        self::startSession();
        session_regenerate_id(true);
        $_SESSION[self::SESSION_KEY] = [
            'id'       => (int)$user['id'],
            'username' => $user['username'],
            'nickname' => $user['nickname'] ?? $user['username'],
            'email'    => $user['email'] ?? '',
            'role'     => $user['role'] ?? 'editor',
            'avatar'   => $user['avatar'] ?? '',
        ];
        $_SESSION[self::SESSION_ID_KEY] = (int)$user['id'];
        self::$currentUser = $_SESSION[self::SESSION_KEY];
    }

    /**
     * 登出
     */
    public static function logout(): void
    {
        self::startSession();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
        self::$currentUser = null;
    }

    /**
     * 是否已登录
     */
    public static function check(): bool
    {
        self::startSession();
        return isset($_SESSION[self::SESSION_KEY]);
    }

    /**
     * 获取当前用户（数组）
     */
    public static function user(): ?array
    {
        if (self::$currentUser !== null) {
            return self::$currentUser;
        }
        self::startSession();
        self::$currentUser = $_SESSION[self::SESSION_KEY] ?? null;
        return self::$currentUser;
    }

    /**
     * 获取当前用户ID
     */
    public static function id(): int
    {
        $user = self::user();
        return $user['id'] ?? 0;
    }

    /**
     * 获取当前角色
     */
    public static function role(): string
    {
        $user = self::user();
        return $user['role'] ?? 'guest';
    }

    /**
     * 检查是否拥有指定角色
     */
    public static function hasRole(string $role): bool
    {
        return self::role() === $role;
    }

    /**
     * 检查是否至少是某个角色（按权限等级）
     * super_admin > admin > editor
     */
    public static function atLeast(string $role): bool
    {
        $levels = ['editor' => 1, 'admin' => 2, 'super_admin' => 3];
        $current = $levels[self::role()] ?? 0;
        $required = $levels[$role] ?? 0;
        return $current >= $required;
    }

    /**
     * 要求登录，未登录则跳转登录页
     */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: /Admin/Auth/Login');
            exit;
        }
    }

    /**
     * 要求至少某角色，不满足则 403
     */
    public static function requireRole(string $role): void
    {
        self::requireLogin();
        if (!self::atLeast($role)) {
            \Error(403, '权限不足，需要 ' . $role . ' 或以上权限');
        }
    }

    /**
     * 加密密码
     */
    public static function hashPassword(string $password, string $salt): string
    {
        return password_hash($password . $salt, PASSWORD_DEFAULT);
    }

    /**
     * 生成盐值
     */
    public static function generateSalt(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * 生成 CSRF Token 并存入 session
     */
    public static function csrfToken(): string
    {
        self::startSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * 校验 CSRF Token
     *
     * 使用一次性 Token 模式（校验后自动生成新的替代），
     * 解决了浏览器自动二次请求导致 Token 不一致的问题。
     * 同时保留旧的 Token 在短时间内仍然有效（双 Token 机制）。
     */
    public static function checkCsrf(?string $token): bool
    {
        self::startSession();

        if (empty($token)) {
            return false;
        }

        // 获取当前 Token 和上一个 Token
        $currentToken = $_SESSION['csrf_token'] ?? '';
        $prevToken = $_SESSION['csrf_token_prev'] ?? '';

        // 匹配当前 Token 或上一个 Token
        $valid = ($currentToken !== '' && hash_equals($currentToken, $token))
              || ($prevToken !== '' && hash_equals($prevToken, $token));

        if ($valid) {
            // 轮换 Token：当前变成上一个，生成新的当前 Token
            $_SESSION['csrf_token_prev'] = $currentToken;
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }

        return $valid;
    }
}
