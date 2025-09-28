<?php

namespace App\Cache;

use Redis;
use DateInterval;
use Exception;
use RedisException;

class RedisCache implements CacheInterface
{
    private Redis $redis;

    /**
     * @throws RedisException
     * @throws Exception
     */
    public function __construct(string $host = '127.0.0.1', int $port = 6379, int $db = 0)
    {
        $this->redis = new Redis();
        if (!$this->redis->connect($host, $port)) {
            throw new Exception("Could not connect to Redis at {$host}:{$port}");
        }
        $this->redis->select($db);
    }

    public function get(string $key, $default = null): mixed
    {
        $value = $this->redis->get($key);
        return $value === false ? $default : unserialize($value);
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        $serialized = serialize($value);

        if ($ttl instanceof DateInterval) {
            $ttl = (int) (new \DateTimeImmutable())->add($ttl)->format('U') - time();
        }

        return $ttl !== null
            ? $this->redis->setex($key, $ttl, $serialized)
            : $this->redis->set($key, $serialized);
    }

    public function delete(string $key): bool
    {
        return $this->redis->del($key) > 0;
    }

    public function clear(): bool
    {
        return $this->redis->flushDB();
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $keys = is_array($keys) ? $keys : iterator_to_array($keys);
        $values = $this->redis->mGet($keys);
        $result = [];
        foreach ($keys as $i => $key) {
            $result[$key] = $values[$i] !== false ? unserialize($values[$i]) : $default;
        }
        return $result;
    }

    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
    {
        $success = true;
        foreach ($values as $key => $value) {
            $success = $success && $this->set($key, $value, $ttl);
        }
        return $success;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $keys = is_array($keys) ? $keys : iterator_to_array($keys);
        return $this->redis->del($keys) > 0;
    }

    public function has(string $key): bool
    {
        return $this->redis->exists($key) > 0;
    }
}
