<?php
declare(strict_types=1);

namespace App\Services;

class CacheService
{
    private string $cachePath;
    private int $defaultTtl;

    public function __construct(string $cachePath = null, int $defaultTtl = 3600)
    {
        $this->cachePath = $cachePath ?? storage_path('cache');
        $this->defaultTtl = $defaultTtl;

        // Создаем директорию если не существует
        if (!file_exists($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    public function get(string $key, $default = null)
    {
        $file = $this->getCacheFile($key);

        if (!file_exists($file)) {
            return $default;
        }

        $data = unserialize(file_get_contents($file));

        if (time() > $data['expires_at']) {
            $this->forget($key);
            return $default;
        }

        return $data['value'];
    }

    public function put(string $key, $value, int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $file = $this->getCacheFile($key);

        $data = [
            'value' => $value,
            'expires_at' => time() + $ttl,
            'created_at' => time()
        ];

        return file_put_contents($file, serialize($data)) !== false;
    }

    public function remember(string $key, callable $callback, int $ttl = null)
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->put($key, $value, $ttl);

        return $value;
    }

    public function forget(string $key): bool
    {
        $file = $this->getCacheFile($key);

        if (file_exists($file)) {
            return unlink($file);
        }

        return true;
    }

    public function clear(): bool
    {
        $files = glob($this->cachePath . '/*');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        return true;
    }

    public function has(string $key): bool
    {
        $file = $this->getCacheFile($key);

        if (!file_exists($file)) {
            return false;
        }

        $data = unserialize(file_get_contents($file));
        return time() <= $data['expires_at'];
    }

    private function getCacheFile(string $key): string
    {
        $hash = md5($key);
        return $this->cachePath . '/' . $hash . '.cache';
    }

    public function getStats(): array
    {
        $files = glob($this->cachePath . '/*.cache');
        $stats = [
            'total_files' => count($files),
            'total_size' => 0,
            'expired_files' => 0
        ];

        foreach ($files as $file) {
            $stats['total_size'] += filesize($file);

            $data = unserialize(file_get_contents($file));
            if (time() > $data['expires_at']) {
                $stats['expired_files']++;
            }
        }

        return $stats;
    }
}