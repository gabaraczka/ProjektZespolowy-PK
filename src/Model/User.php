<?php

namespace App\Model;

class User {
    private int $id;
    private string $email;
    private string $password;
    private ?string $created_at;

    public function __construct(
        string $email,
        string $password,
        ?int $id = null,
        ?string $created_at = null
    ) {
        $this->email = $email;
        $this->password = $password;
        if ($id) {
            $this->id = $id;
        }
        $this->created_at = $created_at;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function getCreatedAt(): ?string {
        return $this->created_at;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function setPassword(string $password): void {
        $this->password = $password;
    }
} 