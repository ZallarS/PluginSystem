<?php
declare(strict_types=1);

namespace App\Core\Session;

/**
 * SessionInterface interface
 *
 * Defines the contract for session management in the application.
 * Provides methods for standard session operations and flash messages.
 *
 * @package App\Core\Session
 */
interface SessionInterface
{
    /**
     * Start the session with the given options.
     *
     * @param array $options Session configuration options
     * @return void
     */
    public function start(array $options = []): void;
    /**
     * Check if the session has been started.
     *
     * @return bool True if session is started
     */
    public function isStarted(): bool;
    /**
     * Get a session value by key.
     *
     * @param string $key The key to retrieve
     * @param mixed $default The default value if key doesn't exist
     * @return mixed The session value or default
     */
    public function get(string $key, $default = null);
    /**
     * Set a session value.
     *
     * @param string $key The key to set
     * @param mixed $value The value to store
     * @return void
     */
    public function set(string $key, $value): void;
    /**
     * Check if a session key exists.
     *
     * @param string $key The key to check
     * @return bool True if the key exists
     */
    public function has(string $key): bool;
    /**
     * Remove a session key.
     *
     * @param string $key The key to remove
     * @return void
     */
    public function remove(string $key): void;
    /**
     * Set a flash message that will be available for the next request.
     *
     * @param string $key The flash message key
     * @param mixed $value The flash message value
     * @return void
     */
    public function flash(string $key, $value): void;
    /**
     * Get a flash message by key.
     *
     * @param string $key The flash message key
     * @param mixed $default The default value if key doesn't exist
     * @return mixed The flash message value or default
     */
    public function getFlash(string $key, $default = null);
    /**
     * Regenerate the session ID.
     *
     * Useful for preventing session fixation attacks.
     *
     * @return void
     */
    public function regenerate(): void;
    /**
     * Destroy the current session.
     *
     * Clears all session data and destroys the session.
     *
     * @return void
     */
    public function destroy(): void;
    /**
     * Get all session data.
     *
     * @return array All session data
     */
    public function all(): array;
}