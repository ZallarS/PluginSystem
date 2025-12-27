<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use PDO;
use PDOException;

class UserRepository
{
    private ?PDO $connection;

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection;
    }

    public function findByUsername(string $username): ?User
    {
        // 1. Пробуем БД если есть соединение
        if ($this->connection) {
            try {
                $stmt = $this->connection->prepare("SELECT * FROM users WHERE username = ?");
                $stmt->execute([$username]);

                if ($data = $stmt->fetch()) {
                    return User::createFromArray($data);
                }
            } catch (PDOException $e) {

            }
        }

        // 2. Fallback на admin из .env
        $adminUsername = env('ADMIN_USERNAME', 'admin');

        if ($username === $adminUsername) {
            return $this->createAdminUser();
        }

        return null;
    }

    public function find(int $id): ?User
    {
        // 1. Пробуем БД если есть соединение
        if ($this->connection) {
            try {
                $stmt = $this->connection->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$id]);

                if ($data = $stmt->fetch()) {
                    return User::createFromArray($data);
                }
            } catch (PDOException $e) {

            }
        }

        // 2. Fallback: только администратор с ID 1
        if ($id === 1) {
            return $this->createAdminUser();
        }

        return null;
    }

    private function createAdminUser(): User
    {
        $adminUsername = env('ADMIN_USERNAME', 'admin');
        $adminPassword = env('ADMIN_PASSWORD', 'admin');

        $user = new User($adminUsername, $adminPassword, true);

        // Создаем массив как если бы он был из БД
        $userData = [
            'id' => 1,
            'username' => $user->getUsername(),
            'password_hash' => $user->toArray()['password_hash'],
            'is_admin' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];

        return User::createFromArray($userData);
    }

    public function save(User $user): bool
    {
        // Если нет БД, просто возвращаем true для совместимости
        if (!$this->connection) {
            return true;
        }

        try {
            $data = $user->toArray();

            if ($user->getId()) {
                // Update
                $sql = "UPDATE users SET username = ?, password_hash = ?, is_admin = ? WHERE id = ?";
                $stmt = $this->connection->prepare($sql);
                return $stmt->execute([
                    $data['username'],
                    $data['password_hash'],
                    $data['is_admin'],
                    $data['id']
                ]);
            } else {
                // Insert
                $sql = "INSERT INTO users (username, password_hash, is_admin) VALUES (?, ?, ?)";
                $stmt = $this->connection->prepare($sql);
                $result = $stmt->execute([
                    $data['username'],
                    $data['password_hash'],
                    $data['is_admin']
                ]);

                if ($result) {
                    $user->id = (int)$this->connection->lastInsertId();
                }

                return $result;
            }
        } catch (PDOException $e) {

            return false;
        }
    }

    public function createTable(): void
    {
        if (!$this->connection) {
            return;
        }

        $sql = "
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(100) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                is_admin TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ";

        try {
            $this->connection->exec($sql);

            // Создаем администратора, если таблица пуста
            $adminUsername = env('ADMIN_USERNAME', 'admin');

            $stmt = $this->connection->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$adminUsername]);

            if ($stmt->fetchColumn() === 0) {
                $adminPassword = env('ADMIN_PASSWORD', 'admin');
                $admin = new User($adminUsername, $adminPassword, true);
                $this->save($admin);
            }
        } catch (PDOException $e) {

        }
    }
}