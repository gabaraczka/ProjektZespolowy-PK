<?php

namespace App\Model;

class Material {
    private ?int $id;
    private int $userId;
    private ?int $folderId;
    private ?string $material;
    private string $materialName;
    private ?string $materialData;
    private ?string $materialPath;

    public function __construct(
        ?int $id = null,
        int $userId = 0,
        ?int $folderId = null,
        ?string $material = null,
        string $materialName = '',
        ?string $materialData = null,
        ?string $materialPath = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->folderId = $folderId;
        $this->material = $material;
        $this->materialName = $materialName;
        $this->materialData = $materialData;
        $this->materialPath = $materialPath;
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

    public function getFolderId(): ?int {
        return $this->folderId;
    }

    public function setFolderId(?int $folderId): void {
        $this->folderId = $folderId;
    }

    public function getMaterial(): ?string {
        return $this->material;
    }

    public function setMaterial(?string $material): void {
        $this->material = $material;
    }

    public function getMaterialName(): string {
        return $this->materialName;
    }

    public function setMaterialName(string $materialName): void {
        $this->materialName = $materialName;
    }

    public function getMaterialData(): ?string {
        return $this->materialData;
    }

    public function setMaterialData(?string $materialData): void {
        $this->materialData = $materialData;
    }

    public function getMaterialPath(): ?string {
        return $this->materialPath;
    }

    public function setMaterialPath(?string $materialPath): void {
        $this->materialPath = $materialPath;
    }
} 