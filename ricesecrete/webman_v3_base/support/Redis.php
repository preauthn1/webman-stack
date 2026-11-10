<?php

declare(strict_types=1);

namespace support;

class Redis
{
    protected static ?\Redis $instance = null;

    public static function connection(): \Redis
    {
        if (static::$instance === null) {
            $config = config('redis.default');
            $redis = new \Redis();
            $redis->connect($config['host'], $config['port'], $config['timeout'] ?? 2);
            if (!empty($config['auth'])) {
                $redis->auth($config['auth']);
            }
            if (isset($config['database'])) {
                $redis->select((int) $config['database']);
            }
            static::$instance = $redis;
        }
        return static::$instance;
    }

    public static function get(string $key): mixed
    {
        $value = static::connection()->get($key);
        return $value === false ? null : $value;
    }

    public static function setex(string $key, int $ttl, mixed $value): bool
    {
        return static::connection()->setex($key, $ttl, (string) $value);
    }

    public static function del(string ...$keys): int
    {
        if ($keys === []) {
            return 0;
        }
        return static::connection()->del(...$keys);
    }

    public static function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $redis = static::connection();
        if ($ttl !== null && $ttl > 0) {
            return $redis->setex($key, $ttl, (string) $value);
        }
        return $redis->set($key, (string) $value);
    }

    public static function incr(string $key): int
    {
        return (int) static::connection()->incr($key);
    }

    public static function expire(string $key, int $ttl): bool
    {
        return static::connection()->expire($key, $ttl);
    }

    public static function ttl(string $key): int
    {
        return (int) static::connection()->ttl($key);
    }
}
