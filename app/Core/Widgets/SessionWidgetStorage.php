<?php

    declare(strict_types=1);

    namespace App\Core\Widgets;

    use App\Core\Session\SessionInterface;

    /**
     * SessionWidgetStorage class
     *
     * Implements widget state storage using PHP sessions.
     * Stores widget visibility states in the user session.
     *
     * @package App\Core\Widgets
     */
    class SessionWidgetStorage implements WidgetStorageInterface
    {
        /**
         * @var string The session key for storing widget states
         */
        private const SESSION_KEY = 'user_widgets';

        /**
         * @var SessionInterface The session interface instance
         */
        private SessionInterface $session;


        /**
         * Create a new session widget storage instance.
         *
         * @param SessionInterface $session The session interface
         */
        public function __construct(SessionInterface $session)
        {
            $this->session = $session;
        }

        /**
         * Get the state of a widget.
         *
         * @param string $widgetId The widget identifier
         * @return bool True if the widget is visible (defaults to true)
         */
        public function getWidgetState(string $widgetId): bool
        {
            $widgets = $this->session->get(self::SESSION_KEY, []);
            return $widgets[$widgetId] ?? true; // По умолчанию виджет видим
        }

        /**
         * Set the state of a widget.
         *
         * @param string $widgetId The widget identifier
         * @param bool $isVisible Whether the widget should be visible
         * @return void
         */
        public function setWidgetState(string $widgetId, bool $isVisible): void
        {
            $widgets = $this->session->get(self::SESSION_KEY, []);
            $widgets[$widgetId] = $isVisible;
            $this->session->set(self::SESSION_KEY, $widgets);
        }

        /**
         * Get the states of all widgets.
         *
         * @return array Widget states array (widget_id => is_visible)
         */
        public function getAllWidgetStates(): array
        {
            return $this->session->get(self::SESSION_KEY, []);
        }

        /**
         * Remove the state of a widget.
         *
         * @param string $widgetId The widget identifier
         * @return void
         */
        public function removeWidgetState(string $widgetId): void
        {
            $widgets = $this->session->get(self::SESSION_KEY, []);
            unset($widgets[$widgetId]);
            $this->session->set(self::SESSION_KEY, $widgets);
        }

        /**
         * Clear all widget states.
         *
         * Removes all widget state data from the session.
         *
         * @return void
         */
        public function clearWidgetStates(): void
        {
            $this->session->remove(self::SESSION_KEY);
        }
    }