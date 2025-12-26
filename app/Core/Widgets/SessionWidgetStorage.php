<?php

    declare(strict_types=1);

    namespace App\Core\Widgets;

    use App\Core\Session\SessionInterface;

    class SessionWidgetStorage implements WidgetStorageInterface
    {
        private const SESSION_KEY = 'user_widgets';

        private SessionInterface $session;

        public function __construct(SessionInterface $session)
        {
            $this->session = $session;
        }

        public function getWidgetState(string $widgetId): bool
        {
            $widgets = $this->session->get(self::SESSION_KEY, []);
            return $widgets[$widgetId] ?? true; // По умолчанию виджет видим
        }

        public function setWidgetState(string $widgetId, bool $isVisible): void
        {
            $widgets = $this->session->get(self::SESSION_KEY, []);
            $widgets[$widgetId] = $isVisible;
            $this->session->set(self::SESSION_KEY, $widgets);
        }

        public function getAllWidgetStates(): array
        {
            return $this->session->get(self::SESSION_KEY, []);
        }

        public function removeWidgetState(string $widgetId): void
        {
            $widgets = $this->session->get(self::SESSION_KEY, []);
            unset($widgets[$widgetId]);
            $this->session->set(self::SESSION_KEY, $widgets);
        }

        public function clearWidgetStates(): void
        {
            $this->session->remove(self::SESSION_KEY);
        }
    }