<?php
declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap/environment.php';
require_once __DIR__ . '/../../bootstrap/helpers.php';

echo "Resetting users table...\n";

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

    // Удаляем таблицу если существует
    $pdo->exec("DROP TABLE IF EXISTS users");
    echo "Table dropped\n";

    // Создаем заново
    $sql = "
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            is_admin TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";

    $pdo->exec($sql);
    echo "Table created\n";

    // Создаем администратора
    $adminUsername = env('ADMIN_USERNAME', 'admin');
    $adminPassword = env('ADMIN_PASSWORD', 'admin');

    $user = new \App\Models\User($adminUsername, $adminPassword, true);
    $data = $user->toArray();

    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, is_admin) VALUES (?, ?, ?)");
    $stmt->execute([
        $data['username'],
        $data['password_hash'],
        $data['is_admin']
    ]);

    echo "Admin user created:\n";
    echo "  Username: {$adminUsername}\n";
    echo "  Password: {$adminPassword}\n";
    echo "  ID: " . $pdo->lastInsertId() . "\n";

    echo "✅ Reset completed successfully!\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}