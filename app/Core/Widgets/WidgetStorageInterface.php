<?php

    declare(strict_types=1);

    namespace App\Core\Widgets;

    interface WidgetStorageInterface
    {
        /**
         * Получить состояние виджета
         */
        public function getWidgetState(string $widgetId): bool;

        /**
         * Установить состояние виджета
         */
        public function setWidgetState(string $widgetId, bool $isVisible): void;

        /**
         * Получить все состояния виджетов
         */
        public function getAllWidgetStates(): array;

        /**
         * Удалить состояние виджета
         */
        public function removeWidgetState(string $widgetId): void;

        /**
         * Очистить все состояния виджетов
         */
        public function clearWidgetStates(): void;
    }