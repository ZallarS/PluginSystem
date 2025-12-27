<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;

/**
 * UserService class
 *
 * Provides user management functionality including
 * admin user creation and credential validation.
 *
 * @package App\Services
 */
class UserService
{
    /**
     * @var UserRepository The user repository instance
     */
    private UserRepository $userRepository;


    /**
     * Create a new user service instance.
     *
     * @param UserRepository $userRepository The user repository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Create an admin user with default credentials.
     *
     * @return User The created admin user
     */
    public function createAdminUser(): User
    {
        $adminUsername = env('ADMIN_USERNAME', 'admin');
        $adminPassword = env('ADMIN_PASSWORD', 'admin');

        $user = new User($adminUsername, $adminPassword, true);

        // Сохраняем в БД, если возможно
        $this->userRepository->save($user);

        return $user;
    }

    /**
     * Find an existing admin user or create one if it doesn't exist.
     *
     * @return User The admin user
     */
    public function findOrCreateAdmin(): User
    {
        $adminUsername = env('ADMIN_USERNAME', 'admin');
        $user = $this->userRepository->findByUsername($adminUsername);

        if (!$user) {
            $user = $this->createAdminUser();
        }

        return $user;
    }

    /**
     * Get all users from the system.
     *
     * @return array The array of users
     */
    public function getAllUsers(): array
    {
        // Заглушка для будущей реализации
        return []; // Пока возвращаем пустой массив
    }

    /**
     * Validate user credentials.
     *
     * @param string $username The username
     * @param string $password The password
     * @return bool True if credentials are valid
     */
    public function validateUserCredentials(string $username, string $password): bool
    {
        $user = $this->userRepository->findByUsername($username);

        if (!$user) {
            return false;
        }

        return $user->verifyPassword($password);
    }

    /**
     * Update user data.
     *
     * @param User $user The user to update
     * @param array $data The new data
     * @return bool True if update was successful
     */
    public function updateUser(User $user, array $data): bool
    {
        // Обновление данных пользователя
        // Реализация будет добавлена позже
        return false;
    }
}
