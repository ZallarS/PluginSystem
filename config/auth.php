<?php

return [
    /**
     * Default admin username.
     *
     * The default username for the admin account.
     * Can be overridden by the ADMIN_USERNAME environment variable.
     */
    'admin_username' => env('ADMIN_USERNAME', 'admin'),

    /**
     * Default admin password.
     *
     * The default password for the admin account.
     * Can be overridden by the ADMIN_PASSWORD environment variable.
     */
    'admin_password' => env('ADMIN_PASSWORD', 'admin'),

    /**
     * Session timeout.
     *
     * The number of seconds before admin session expires.
     * Defaults to 3600 seconds (1 hour).
     */
    'session_timeout' => env('SESSION_TIMEOUT', 3600), // 1 час

    /**
     * CSRF protection settings.
     *
     * Configuration for CSRF token generation and validation.
     */
    'csrf' => [
        /**
         * The name of the CSRF token field in forms.
         */
        'token_name' => '_token',

        /**
         * The name of the CSRF token header for AJAX requests.
         */
        'header_name' => 'X-CSRF-TOKEN',
    ],
];
