<?php
declare(strict_types=1);

namespace Database\Migrations;

use PDO;

class CreateUsersTable
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function up(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(100) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                is_admin BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_username (username),
                INDEX idx_is_admin (is_admin)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";

        $this->connection->exec($sql);

        // Создаем администратора
        $this->createAdminUser();
    }

    public function down(): void
    {
        $sql = "DROP TABLE IF EXISTS users";
        $this->connection->exec($sql);
    }

    private function createAdminUser(): void
    {
        $adminUsername = env('ADMIN_USERNAME', 'admin');
        $adminPassword = env('ADMIN_PASSWORD', 'admin');

        // Проверяем, существует ли уже администратор
        $stmt = $this->connection->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$adminUsername]);

        if ($stmt->fetchColumn() === 0) {
            $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);

            $stmt = $this->connection->prepare("
                INSERT INTO users (username, password_hash, is_admin) 
                VALUES (?, ?, 1)
            ");
            $stmt->execute([$adminUsername, $passwordHash]);
        }
    }
}