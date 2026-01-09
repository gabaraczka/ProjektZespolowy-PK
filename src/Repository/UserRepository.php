<?php

namespace App\Repository;

use App\Config\Database;
use App\Model\User;
use PDO;

class UserRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findByEmail(string $email): ?User {
        $stmt = $this->db->prepare('SELECT id, password, email, created_at FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$userData) {
            return null;
        }

        return new User(
            $userData['email'],
            $userData['password'],
            $userData['id'],
            $userData['created_at']
        );
    }

    public function save(User $user): void {
        if ($this->findByEmail($user->getEmail())) {
            throw new \RuntimeException('User with this email already exists');
        }

        $stmt = $this->db->prepare(
            'INSERT INTO users (email, password) VALUES (:email, :password)'
        );

        $stmt->execute([
            'email' => $user->getEmail(),
            'password' => $user->getPassword()
        ]);

        $user->setId((int)$this->db->lastInsertId('users_id_seq'));
    }
} 