<?php
declare(strict_types=1);

namespace App\Models;

class User
{
    private ?int $id = null;
    private string $username;
    private string $passwordHash;
    private bool $isAdmin = false;
    private \DateTime $createdAt;

    public function __construct(string $username, string $password, bool $isAdmin = false)
    {
        $this->username = $username;
        $this->setPassword($password);
        $this->isAdmin = $isAdmin;
        $this->createdAt = new \DateTime();
    }

    public static function createFromArray(array $data): self
    {
        // Преобразуем is_admin из int в bool
        $isAdmin = false;
        if (isset($data['is_admin'])) {
            $isAdmin = (bool)$data['is_admin'];
        }

        $user = new self($data['username'], '', $isAdmin);
        $user->id = $data['id'] ?? null;
        $user->passwordHash = $data['password_hash'] ?? '';

        if (isset($data['created_at'])) {
            try {
                $user->createdAt = \DateTime::createFromFormat('Y-m-d H:i:s', $data['created_at']);
            } catch (\Exception $e) {
                $user->createdAt = new \DateTime();
            }
        }

        return $user;
    }

    // Добавляем метод для преобразования is_admin из разных форматов
    private static function normalizeIsAdmin($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'yes', 'on']);
        }

        return false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    public function setPassword(string $password): void
    {
        $this->passwordHash = password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'password_hash' => $this->passwordHash,
            'is_admin' => $this->isAdmin ? 1 : 0, // Сохраняем как int для БД
            'created_at' => $this->createdAt->format('Y-m-d H:i:s')
        ];
    }
}