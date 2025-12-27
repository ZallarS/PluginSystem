<?php
declare(strict_types=1);

namespace App\Services;

/**
 * CacheService class
 *
 * Provides file-based caching functionality for the application.
 * Supports storing, retrieving, and managing cached data with TTL.
 *
 * @package App\Services
 */
class CacheService
{
    /**
     * @var string The path to the cache directory
     */
    private string $cachePath;

    /**
     * @var int The default time-to-live for cached items
     */
    private int $defaultTtl;


    /**
     * Create a new cache service instance.
     *
     * @param string|null $cachePath The path to the cache directory
     * @param int $defaultTtl The default time-to-live in seconds
     */
    public function __construct(string $cachePath = null, int $defaultTtl = 3600)
    {
        $this->cachePath = $cachePath ?? storage_path('cache');
        $this->defaultTtl = $defaultTtl;

        // Создаем директорию если не существует
        if (!file_exists($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    /**
     * Retrieve an item from the cache.
     *
     * @param string $key The cache key
     * @param mixed $default The default value if key not found
     * @return mixed The cached value or default
     */
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

    /**
     * Store an item in the cache.
     *
     * @param string $key The cache key
     * @param mixed $value The value to store
     * @param int|null $ttl The time-to-live in seconds
     * @return bool True if successful
     */
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

    /**
     * Retrieve an item from the cache or store the result of a callback.
     *
     * @param string $key The cache key
     * @param callable $callback The callback to execute if key not found
     * @param int|null $ttl The time-to-live in seconds
     * @return mixed The cached value or callback result
     */
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

    /**
     * Remove an item from the cache.
     *
     * @param string $key The cache key
     * @return bool True if successful
     */
    public function forget(string $key): bool
    {
        $file = $this->getCacheFile($key);

        if (file_exists($file)) {
            return unlink($file);
        }

        return true;
    }

    /**
     * Clear all items from the cache.
     *
     * @return bool True if successful
     */
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

    /**
     * Check if an item exists in the cache and is not expired.
     *
     * @param string $key The cache key
     * @return bool True if item exists and is not expired
     */
    public function has(string $key): bool
    {
        $file = $this->getCacheFile($key);

        if (!file_exists($file)) {
            return false;
        }

        $data = unserialize(file_get_contents($file));
        return time() <= $data['expires_at'];
    }

    /**
     * Get the cache file path for a given key.
     *
     * @param string $key The cache key
     * @return string The full path to the cache file
     */
    private function getCacheFile(string $key): string
    {
        $hash = md5($key);
        return $this->cachePath . '/' . $hash . '.cache';
    }

    /**
     * Get cache statistics.
     *
     * @return array Statistics including total files, size, and expired files
     */
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
