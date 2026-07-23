<?php
/**
 * 依赖注入容器类
 * Date: 2026-07-23
 * Author: 小新
 * SystemName: XiaoPHP
 */
namespace XiaoPHP\System;

class Container
{
    /**
     * 服务定义存储
     * @var array
     */
    private $bindings = [];

    /**
     * 单例实例缓存
     * @var array
     */
    private $instances = [];

    /**
     * 正在解析中的服务（用于循环依赖检测）
     * @var array
     */
    private $resolving = [];

    /**
     * 类型索引（接口 => 实现类）
     * @var array
     */
    private $typeMap = [];

    /**
     * 自动装配白名单命名空间
     * @var array
     */
    private $autowireNamespaces = [];

    /**
     * 容器实例（单例）
     * @var Container|null
     */
    private static $instance = null;

    /**
     * 获取容器单例
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 注册服务绑定
     * 
     * @param string $abstract 服务标识（类名/接口名/别名）
     * @param callable|string|null $concrete 服务实现（闭包/类名）
     * @param bool $shared 是否单例
     */
    public function bind(string $abstract, $concrete = null, bool $shared = false): void
    {
        if ($concrete === null) {
            $concrete = $abstract;
        }

        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'shared'   => $shared,
        ];

        // 如果是类名，建立类型索引
        if (is_string($concrete) && class_exists($concrete)) {
            $this->typeMap[$abstract] = $concrete;
        }
    }

    /**
     * 注册单例服务
     */
    public function singleton(string $abstract, $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * 注册服务并立即解析
     * 
     * @param string $abstract 服务标识
     * @param callable|string|null $concrete 服务实现
     * @param array $parameters 构造参数
     * @param bool $shared 是否单例
     * @return mixed
     */
    public function run(string $abstract, $concrete = null, array $parameters = [], bool $shared = false)
    {
        $this->bind($abstract, $concrete, $shared);
        return $this->make($abstract, $parameters);
    }

    /**
     * 解析服务实例
     * 
     * @param string $abstract 服务标识
     * @param array $parameters 额外参数
     * @return mixed
     */
    public function make(string $abstract, array $parameters = [])
    {
        // 检查循环依赖
        if (isset($this->resolving[$abstract])) {
            throw new \RuntimeException("检测到循环依赖: " . implode(' -> ', array_keys($this->resolving)) . " -> $abstract");
        }

        // 如果已存在单例实例，直接返回
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // 标记正在解析
        $this->resolving[$abstract] = true;

        try {
            // 获取服务定义
            if (!isset($this->bindings[$abstract])) {
                // 如果没有绑定，尝试自动装配
                if ($this->canAutowire($abstract)) {
                    $instance = $this->autowire($abstract, $parameters);
                } else {
                    throw new \InvalidArgumentException("服务未注册: $abstract");
                }
            } else {
                $binding = $this->bindings[$abstract];
                $concrete = $binding['concrete'];

                if (is_callable($concrete)) {
                    // 闭包形式
                    $instance = call_user_func($concrete, $this, $parameters);
                } else {
                    // 类名形式，自动装配
                    $instance = $this->autowire($concrete, $parameters);
                }
            }

            // 如果是单例，缓存实例
            if (isset($this->bindings[$abstract]) && $this->bindings[$abstract]['shared']) {
                $this->instances[$abstract] = $instance;
            }

            return $instance;
        } finally {
            // 移除解析标记
            unset($this->resolving[$abstract]);
        }
    }

    /**
     * 直接获取实例（如果已缓存）
     * 
     * @param string $abstract 服务标识
     * @return mixed|null
     */
    public function get(string $abstract)
    {
        return $this->instances[$abstract] ?? null;
    }

    /**
     * 设置已解析的实例到容器
     * 
     * @param string $abstract 服务标识
     * @param mixed $instance 实例
     */
    public function set(string $abstract, $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    /**
     * 检查服务是否已注册
     */
    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }

    /**
     * 清除服务实例缓存
     * 
     * @param string|null $abstract 服务标识，不传则清除所有
     */
    public function clear(string $abstract = null): void
    {
        if ($abstract !== null) {
            unset($this->instances[$abstract]);
        } else {
            $this->instances = [];
        }
    }

    /**
     * 自动装配类实例
     * 
     * @param string $className 类名
     * @param array $parameters 额外参数（优先使用）
     * @return object
     */
    private function autowire(string $className, array $parameters = []): object
    {
        if (!class_exists($className)) {
            throw new \InvalidArgumentException("类不存在: $className");
        }

        $reflection = new \ReflectionClass($className);
        
        if (!$reflection->isInstantiable()) {
            throw new \InvalidArgumentException("类无法实例化: $className");
        }

        $constructor = $reflection->getConstructor();
        
        if ($constructor === null) {
            // 无构造函数，直接实例化
            return new $className();
        }

        $dependencies = [];
        foreach ($constructor->getParameters() as $parameter) {
            $name = $parameter->getName();
            
            // 如果提供了额外参数，优先使用
            if (isset($parameters[$name])) {
                $dependencies[] = $parameters[$name];
                continue;
            }

            $type = $parameter->getType();
            
            if ($type === null || $type->isBuiltin()) {
                // 无类型提示或内置类型，检查是否有默认值
                if ($parameter->isOptional()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new \RuntimeException("无法解析参数: $name (类: $className)");
                }
            } else {
                // 有类型提示，尝试从容器获取
                $typeName = $type->getName();
                $dependencies[] = $this->make($typeName);
            }
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * 检查是否可以自动装配
     */
    private function canAutowire(string $className): bool
    {
        if (!class_exists($className)) {
            return false;
        }

        // 如果没有设置白名单，允许自动装配所有类
        if (empty($this->autowireNamespaces)) {
            return true;
        }

        // 检查类名是否在白名单命名空间内
        foreach ($this->autowireNamespaces as $namespace) {
            if (strpos($className, $namespace) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * 添加自动装配白名单命名空间
     */
    public function addAutowireNamespace(string $namespace): void
    {
        $this->autowireNamespaces[] = $namespace;
    }

    /**
     * 设置自动装配白名单命名空间
     */
    public function setAutowireNamespaces(array $namespaces): void
    {
        $this->autowireNamespaces = $namespaces;
    }

    /**
     * 获取已注册的服务列表
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * 获取已缓存的实例列表
     */
    public function getInstances(): array
    {
        return $this->instances;
    }
}
