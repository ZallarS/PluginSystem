<?php
declare(strict_types=1);

namespace App\Core\Session;

interface SessionInterface
{
    public function start(array $options = []): void;
    public function isStarted(): bool;
    public function get(string $key, $default = null);
    public function set(string $key, $value): void;
    public function has(string $key): bool;
    public function remove(string $key): void;
    public function flash(string $key, $value): void;
    public function getFlash(string $key, $default = null);
    public function regenerate(): void;
    public function destroy(): void;
    public function all(): array;
}