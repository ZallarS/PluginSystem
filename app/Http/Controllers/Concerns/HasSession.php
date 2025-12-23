<?php
declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use App\Core\Session\SessionInterface;

trait HasSession
{
    protected function session(): SessionInterface
    {
        if (isset($this->session) && $this->session instanceof SessionInterface) {
            return $this->session;
        }

        try {
            return app(SessionInterface::class);
        } catch (\Exception $e) {
            // Fallback для обратной совместимости
            return app(\App\Core\Session\SessionManager::class);
        }
    }

    protected function flashMessage(string $message, string $type = 'success'): void
    {
        $this->session()->flash("flash_{$type}", $message);
    }

    protected function getFlash(string $type = null)
    {
        if ($type) {
            return $this->session()->getFlash("flash_{$type}");
        }

        return [
            'success' => $this->session()->getFlash('flash_success'),
            'error' => $this->session()->getFlash('flash_error'),
            'warning' => $this->session()->getFlash('flash_warning'),
            'info' => $this->session()->getFlash('flash_info'),
        ];
    }
}