<?php

namespace Core;

class HookManager
{
    private static $instance;
    private $hooks = [];
    private $filters = [];

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {}

    /**
     * Добавить хук (действие)
     */
    public function addAction($hook, $callback, $priority = 10)
    {
        if (!isset($this->hooks[$hook])) {
            $this->hooks[$hook] = [];
        }

        $this->hooks[$hook][] = [
            'callback' => $callback,
            'priority' => $priority
        ];

        // Сортируем по приоритету
        usort($this->hooks[$hook], function($a, $b) {
            return $a['priority'] - $b['priority'];
        });
    }

    /**
     * Выполнить хук (действие)
     */
    public function doAction($hook, ...$args)
    {
        if (!isset($this->hooks[$hook])) {
            return;
        }

        foreach ($this->hooks[$hook] as $hookData) {
            call_user_func_array($hookData['callback'], $args);
        }
    }

    /**
     * Добавить фильтр
     */
    public function addFilter($filter, $callback, $priority = 10)
    {
        if (!isset($this->filters[$filter])) {
            $this->filters[$filter] = [];
        }

        $this->filters[$filter][] = [
            'callback' => $callback,
            'priority' => $priority
        ];

        // Сортируем по приоритету
        usort($this->filters[$filter], function($a, $b) {
            return $a['priority'] - $b['priority'];
        });
    }

    /**
     * Применить фильтр
     */
    public function applyFilter($filter, $value, ...$args)
    {
        if (!isset($this->filters[$filter])) {
            return $value;
        }

        foreach ($this->filters[$filter] as $filterData) {
            $value = call_user_func_array($filterData['callback'], array_merge([$value], $args));
        }

        return $value;
    }

    /**
     * Проверить наличие хука
     */
    public function hasAction($hook)
    {
        return isset($this->hooks[$hook]) && count($this->hooks[$hook]) > 0;
    }

    /**
     * Проверить наличие фильтра
     */
    public function hasFilter($filter)
    {
        return isset($this->filters[$filter]) && count($this->filters[$filter]) > 0;
    }

    /**
     * Получить список всех хуков
     */
    public function getHooks()
    {
        return $this->hooks;
    }

    /**
     * Получить список всех фильтров
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Очистить все хуки
     */
    public function clearHooks()
    {
        $this->hooks = [];
        $this->filters = [];
    }
}