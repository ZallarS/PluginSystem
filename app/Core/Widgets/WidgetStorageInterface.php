<?php

    declare(strict_types=1);

    namespace App\Core\Widgets;

    /**
     * WidgetStorageInterface interface
     *
     * Defines the contract for storing and retrieving widget states.
     * Allows different storage strategies (session, database, etc.).
     *
     * @package App\Core\Widgets
     */
    interface WidgetStorageInterface
    {
        /**
         * Get the state of a widget.
         *
         * @param string $widgetId The widget identifier
         * @return bool True if the widget is visible
         */
        public function getWidgetState(string $widgetId): bool;

        /**
         * Set the state of a widget.
         *
         * @param string $widgetId The widget identifier
         * @param bool $isVisible Whether the widget should be visible
         * @return void
         */
        public function setWidgetState(string $widgetId, bool $isVisible): void;

        /**
         * Get the states of all widgets.
         *
         * @return array Widget states array (widget_id => is_visible)
         */
        public function getAllWidgetStates(): array;

        /**
         * Remove the state of a widget.
         *
         * @param string $widgetId The widget identifier
         * @return void
         */
        public function removeWidgetState(string $widgetId): void;

        /**
         * Clear all widget states.
         *
         * @return void
         */
        public function clearWidgetStates(): void;
    }