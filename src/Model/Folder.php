<?php

namespace App\Model;

class Folder {
    private ?int $id;
    private int $userId;
    private string $folderName;
    private ?string $createdAt;

    public function __construct(
        ?int $id = null,
        int $userId = 0,
        string $folderName = '',
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->folderName = $folderName;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function getUserId(): int {
        return $this->userId;
    }

    public function setUserId(int $userId): void {
        $this->userId = $userId;
    }

    public function getFolderName(): string {
        return $this->folderName;
    }

    public function setFolderName(string $folderName): void {
        $this->folderName = $folderName;
    }

    public function getCreatedAt(): ?string {
        return $this->createdAt;
    }

    public function setCreatedAt(?string $createdAt): void {
        $this->createdAt = $createdAt;
    }
} 