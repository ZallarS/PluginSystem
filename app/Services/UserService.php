<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;

class UserService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function createAdminUser(): User
    {
        $adminUsername = env('ADMIN_USERNAME', 'admin');
        $adminPassword = env('ADMIN_PASSWORD', 'admin');

        $user = new User($adminUsername, $adminPassword, true);

        // Сохраняем в БД, если возможно
        $this->userRepository->save($user);

        return $user;
    }

    public function findOrCreateAdmin(): User
    {
        $adminUsername = env('ADMIN_USERNAME', 'admin');
        $user = $this->userRepository->findByUsername($adminUsername);

        if (!$user) {
            $user = $this->createAdminUser();
        }

        return $user;
    }

    public function getAllUsers(): array
    {
        // Заглушка для будущей реализации
        return []; // Пока возвращаем пустой массив
    }

    public function validateUserCredentials(string $username, string $password): bool
    {
        $user = $this->userRepository->findByUsername($username);

        if (!$user) {
            return false;
        }

        return $user->verifyPassword($password);
    }

    public function updateUser(User $user, array $data): bool
    {
        // Обновление данных пользователя
        // Реализация будет добавлена позже
        return false;
    }
}