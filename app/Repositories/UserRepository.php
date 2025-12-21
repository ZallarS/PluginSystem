<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use PDO;
use PDOException;

class UserRepository
{
    private ?PDO $connection;
    private bool $hasDatabase = false;

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection;
        $this->hasDatabase = $connection !== null;

        if ($this->hasDatabase) {
            try {
                $this->createTable();
            } catch (PDOException $e) {
                error_log("UserRepository: Database error, using fallback mode: " . $e->getMessage());
                $this->hasDatabase = false;
            }
        }
    }

    public function findByUsername(string $username): ?User
    {
        // Если есть БД, ищем в ней
        if ($this->hasDatabase && $this->connection) {
            try {
                $stmt = $this->connection->prepare("SELECT * FROM users WHERE username = ?");
                $stmt->execute([$username]);
                $data = $stmt->fetch();

                if ($data) {
                    return User::createFromArray($data);
                }
            } catch (PDOException $e) {
                error_log("UserRepository: Error finding user by username: " . $e->getMessage());
            }
        }

        // Fallback: проверяем администратора из .env
        $adminUsername = env('ADMIN_USERNAME', 'admin');

        if ($username === $adminUsername) {
            $adminPassword = env('ADMIN_PASSWORD', 'admin');
            $user = new User($adminUsername, $adminPassword, true);
            $userData = $user->toArray();
            $userData['id'] = 1;
            return User::createFromArray($userData);
        }

        return null;
    }

    public function find(int $id): ?User
    {
        // Если есть БД, ищем в ней
        if ($this->hasDatabase && $this->connection) {
            try {
                $stmt = $this->connection->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $data = $stmt->fetch();

                if ($data) {
                    return User::createFromArray($data);
                }
            } catch (PDOException $e) {
                error_log("UserRepository: Error finding user by ID: " . $e->getMessage());
            }
        }

        // Fallback: только администратор с ID 1
        if ($id === 1) {
            $adminUsername = env('ADMIN_USERNAME', 'admin');
            $adminPassword = env('ADMIN_PASSWORD', 'admin');
            $user = new User($adminUsername, $adminPassword, true);
            $userData = $user->toArray();
            $userData['id'] = 1;
            return User::createFromArray($userData);
        }

        return null;
    }

    public function save(User $user): bool
    {
        // Если нет БД, просто возвращаем true для совместимости
        if (!$this->hasDatabase || !$this->connection) {
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
            error_log("UserRepository: Error saving user: " . $e->getMessage());
            return false;
        }
    }

    public function createTable(): void
    {
        if (!$this->hasDatabase || !$this->connection) {
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
                $this->insertUser($admin);
            }
        } catch (PDOException $e) {
            error_log("UserRepository: Error creating table: " . $e->getMessage());
            throw $e;
        }
    }

    private function insertUser(User $user): void
    {
        if (!$this->hasDatabase || !$this->connection) {
            return;
        }

        $sql = "INSERT INTO users (username, password_hash, is_admin) VALUES (?, ?, ?)";
        $stmt = $this->connection->prepare($sql);
        $data = $user->toArray();
        $stmt->execute([
            $data['username'],
            $data['password_hash'],
            $data['is_admin']
        ]);

        $user->id = (int)$this->connection->lastInsertId();
    }
}