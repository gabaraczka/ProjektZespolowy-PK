<?php

namespace App\Controller;

use App\Model\User;
use App\Repository\UserRepository;

class AuthController {
    private UserRepository $userRepository;

    public function __construct() {
        $this->userRepository = new UserRepository();
    }

    public function loginForm(): void {
        require __DIR__ . '/../View/auth/login.php';
    }

    public function registerForm(): void {
        require __DIR__ . '/../View/auth/register.php';
    }

    public function login(): void {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Wypełnij wszystkie pola';
            header('Location: /login');
            return;
        }

        $user = $this->userRepository->findByEmail($email);

        if (!$user || !password_verify($password, $user->getPassword())) {
            $_SESSION['error'] = 'Invalid credentials';
            header('Location: /login');
            return;
        }

        $_SESSION['user_id'] = $user->getId();
        $_SESSION['email'] = $user->getEmail();
        header('Location: /');
    }

    public function register(): void {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($email) || empty($password) || empty($confirm_password)) {
            $_SESSION['error'] = 'Wypełnij wszystkie pola';
            header('Location: /register');
            return;
        }

        if ($password !== $confirm_password) {
            $_SESSION['error'] = 'Passwords do not match';
            header('Location: /register');
            return;
        }

        try {
            $user = new User(
                $email,
                password_hash($password, PASSWORD_DEFAULT)
            );
            
            $this->userRepository->save($user);
            
            $_SESSION['success'] = 'Rejestracja udana! Zaloguj się.';
            header('Location: /login');
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /register');
        }
    }

    public function logout(): void {
        session_destroy();
        header('Location: /login');
    }
} 