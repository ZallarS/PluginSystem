<?php
declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap/environment.php';
require_once __DIR__ . '/../../bootstrap/helpers.php';

try {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        env('DB_HOST', 'localhost'),
        env('DB_PORT', '3306'),
        env('DB_DATABASE', 'SystemPlugins')
    );

    $pdo = new PDO(
        $dsn,
        env('DB_USERNAME', 'root'),
        env('DB_PASSWORD', ''),
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    echo "Starting migrations...\n";

    // Запускаем миграцию users таблицы
    require_once __DIR__ . '/migrations/001_create_users_table.php';
    $migration = new Database\Migrations\CreateUsersTable($pdo);
    $migration->up();

    echo "Migration completed successfully!\n";

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}