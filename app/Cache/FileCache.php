<?php

namespace App\Cache;

readonly class FileCache implements CacheInterface
{

    public function __construct(private string $file)
    {
    }

    public function get(string $key, $default = null): mixed
    {
        $data = $this->readFile();
        return $data[$key] ?? $default;
    }

    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        $data = $this->readFile();
        $data[$key] = $value;
        return $this->writeFile($data);
    }

    public function delete(string $key): bool
    {
        $data = $this->readFile();
        unset($data[$key]);
        return $this->writeFile($data);
    }

    public function clear(): bool
    {
        return $this->writeFile([]);
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $data = $this->readFile();
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $data[$key] ?? $default;
        }
        return $result;
    }

    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        $data = $this->readFile();
        foreach ($values as $key => $value) {
            $data[$key] = $value;
        }
        return $this->writeFile($data);
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $data = $this->readFile();
        foreach ($keys as $key) {
            unset($data[$key]);
        }
        return $this->writeFile($data);
    }

    public function has(string $key): bool
    {
        $data = $this->readFile();
        return array_key_exists($key, $data);
    }

    private function readFile(): array
    {
        if (file_exists($this->file)) {
            return include $this->file;
        }
        return [];
    }

    private function writeFile(array $data): bool
    {
        return (bool) file_put_contents(
            $this->file,
            '<?php return ' . var_export($data, true) . ';'
        );
    }
}
