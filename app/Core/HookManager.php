<?php
declare(strict_types=1);

namespace App\Core;

/**
 * HookManager class
 *
 * Implements the action and filter hook system for the application.
 * Allows plugins and core components to extend functionality.
 *
 * @package App\Core
 */
class HookManager
{
    /**
     * @var self|null The singleton instance
     */
    private static $instance;

    /**
     * @var array The registered action hooks
     */
    private $hooks = [];

    /**
     * @var array The registered filter hooks
     */
    private $filters = [];


    /**
     * Get the singleton instance of the hook manager.
     *
     * @return self The hook manager instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Create a new hook manager instance.
     *
     * Private constructor to enforce singleton pattern.
     */
    private function __construct() {}


    /**
     * Add an action hook.
     *
     * Registers a callback to be executed when the specified action is triggered.
     *
     * @param string $hook The hook name
     * @param callable $callback The callback function
     * @param int $priority The execution priority (lower numbers execute first)
     * @return void
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

        usort($this->hooks[$hook], function($a, $b) {
            return $a['priority'] - $b['priority'];
        });
    }

    /**
     * Execute all callbacks for an action hook.
     *
     * Triggers all registered callbacks for the specified action.
     *
     * @param string $hook The hook name
     * @param mixed ...$args Arguments to pass to the callbacks
     * @return void
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
     * Add a filter hook.
     *
     * Registers a callback to filter/modify a value when the specified filter is applied.
     *
     * @param string $filter The filter name
     * @param callable $callback The callback function
     * @param int $priority The execution priority (lower numbers execute first)
     * @return void
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

        usort($this->filters[$filter], function($a, $b) {
            return $a['priority'] - $b['priority'];
        });
    }

    /**
     * Apply all callbacks for a filter hook.
     *
     * Processes a value through all registered callbacks for the specified filter.
     *
     * @param string $filter The filter name
     * @param mixed $value The value to filter
     * @param mixed ...$args Additional arguments to pass to the callbacks
     * @return mixed The filtered value
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
     * Check if any callbacks are registered for an action hook.
     *
     * @param string $hook The hook name
     * @return bool True if callbacks are registered
     */
    public function hasAction($hook)
    {
        return isset($this->hooks[$hook]) && count($this->hooks[$hook]) > 0;
    }

    /**
     * Check if any callbacks are registered for a filter hook.
     *
     * @param string $filter The filter name
     * @return bool True if callbacks are registered
     */
    public function hasFilter($filter)
    {
        return isset($this->filters[$filter]) && count($this->filters[$filter]) > 0;
    }

    /**
     * Get all registered action hooks.
     *
     * @return array The hooks array
     */
    public function getHooks()
    {
        return $this->hooks;
    }

    /**
     * Get all registered filter hooks.
     *
     * @return array The filters array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Clear all registered hooks and filters.
     *
     * Removes all callbacks from both action and filter hooks.
     *
     * @return void
     */
    public function clearHooks()
    {
        $this->hooks = [];
        $this->filters = [];
    }
}
