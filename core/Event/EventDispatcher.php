<?php

namespace Core\Event;

class EventDispatcher
{
    private $listeners = [];

    public function addListener($eventName, $listener, $priority = 0)
    {
        $this->listeners[$eventName][$priority][] = $listener;

        // Сортируем по приоритету
        krsort($this->listeners[$eventName]);
    }

    public function dispatch($eventName, $event = null)
    {
        if (!isset($this->listeners[$eventName])) {
            return $event;
        }

        if ($event === null) {
            $event = new Event($eventName);
        }

        foreach ($this->listeners[$eventName] as $priority => $listeners) {
            foreach ($listeners as $listener) {
                if ($event->isPropagationStopped()) {
                    break 2;
                }

                if (is_callable($listener)) {
                    call_user_func($listener, $event);
                } elseif (is_array($listener) && count($listener) === 2) {
                    call_user_func($listener, $event);
                }
            }
        }

        return $event;
    }

    public function getListeners($eventName = null)
    {
        if ($eventName === null) {
            return $this->listeners;
        }

        return $this->listeners[$eventName] ?? [];
    }

    public function hasListeners($eventName)
    {
        return !empty($this->listeners[$eventName]);
    }
}