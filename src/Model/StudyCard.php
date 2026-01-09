<?php

namespace App\Model;

class StudyCard {
    private ?int $id;
    private int $user_id;
    private string $question;
    private string $answer;
    private ?int $folder_id;
    private ?string $created_at;

    public function __construct(
        int $user_id,
        string $question,
        string $answer,
        ?int $id = null,
        ?int $folder_id = null,
        ?string $created_at = null
    ) {
        $this->user_id = $user_id;
        $this->question = $question;
        $this->answer = $answer;
        $this->id = $id;
        $this->folder_id = $folder_id;
        $this->created_at = $created_at;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getUserId(): int {
        return $this->user_id;
    }

    public function getQuestion(): string {
        return $this->question;
    }

    public function getAnswer(): string {
        return $this->answer;
    }

    public function getFolderId(): ?int {
        return $this->folder_id;
    }

    public function getCreatedAt(): ?string {
        return $this->created_at;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function setFolderId(?int $folder_id): void {
        $this->folder_id = $folder_id;
    }
} 