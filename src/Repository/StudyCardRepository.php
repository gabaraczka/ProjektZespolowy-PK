<?php

namespace App\Repository;

use App\Config\Database;
use App\Model\StudyCard;
use PDO;

class StudyCardRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function save(StudyCard $card): void {
        $stmt = $this->db->prepare(
            'INSERT INTO studycards (user_id, question, answer, folder_id, created_at) 
             VALUES (:user_id, :question, :answer, :folder_id, NOW())'
        );

        $stmt->execute([
            'user_id' => $card->getUserId(),
            'question' => $card->getQuestion(),
            'answer' => $card->getAnswer(),
            'folder_id' => $card->getFolderId()
        ]);

        $card->setId((int)$this->db->lastInsertId('studycards_id_seq'));
    }

    public function findByUserId(int $userId): array {
        $stmt = $this->db->prepare('SELECT * FROM studycards WHERE user_id = :user_id ORDER BY created_at DESC');
        $stmt->execute(['user_id' => $userId]);
        
        $cards = [];
        while ($row = $stmt->fetch()) {
            $cards[] = new StudyCard(
                $row['user_id'],
                $row['question'],
                $row['answer'],
                $row['id'],
                $row['folder_id'],
                $row['created_at']
            );
        }
        return $cards;
    }

    public function findByFolderId(int $folderId): array {
        $stmt = $this->db->prepare('SELECT * FROM studycards WHERE folder_id = :folder_id ORDER BY created_at DESC');
        $stmt->execute(['folder_id' => $folderId]);
        
        $cards = [];
        while ($row = $stmt->fetch()) {
            $cards[] = new StudyCard(
                $row['user_id'],
                $row['question'],
                $row['answer'],
                $row['id'],
                $row['folder_id'],
                $row['created_at']
            );
        }
        return $cards;
    }

    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare('DELETE FROM studycards WHERE id = :id AND user_id = :user_id');
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId
        ]);
        return $stmt->rowCount() > 0;
    }

    public function saveMany(array $cards): void {
        $this->db->beginTransaction();
        try {
            foreach ($cards as $card) {
                $this->save($card);
            }
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
} 