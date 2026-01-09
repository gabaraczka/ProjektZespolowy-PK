<?php

namespace App\Repository;

use App\Config\Database;
use App\Model\Folder;
use PDO;

class FolderRepository {
    private PDO $database;

    public function __construct() {
        $this->database = Database::getInstance();
    }

    public function create(string $folderName, int $userId): bool {
        $stmt = $this->database->prepare('
            INSERT INTO folders (folder_name, userid) 
            VALUES (:folder_name, :user_id)
        ');
        
        return $stmt->execute([
            'folder_name' => $folderName,
            'user_id' => $userId
        ]);
    }

    public function findByUserId(int $userId): array {
        $stmt = $this->database->prepare('
            SELECT * FROM folders 
            WHERE userid = :user_id 
            ORDER BY folder_name ASC
        ');
        
        $stmt->execute(['user_id' => $userId]);
        $folders = [];
        
        while ($folderData = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $folders[] = new Folder(
                $folderData['folderid'],
                $folderData['userid'],
                $folderData['folder_name'],
                $folderData['created_at'] ?? null
            );
        }
        
        return $folders;
    }

    public function delete(int $folderId, int $userId): bool {
        $stmt = $this->database->prepare('
            DELETE FROM folders 
            WHERE folderid = :folder_id AND userid = :user_id
        ');
        
        return $stmt->execute([
            'folder_id' => $folderId,
            'user_id' => $userId
        ]);
    }

    public function findById(int $folderId): ?Folder {
        $stmt = $this->database->prepare('
            SELECT * FROM folders WHERE folderid = :folder_id
        ');
        
        $stmt->execute(['folder_id' => $folderId]);
        $folderData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$folderData) {
            return null;
        }
        
        return new Folder(
            $folderData['folderid'],
            $folderData['userid'],
            $folderData['folder_name'],
            $folderData['created_at'] ?? null
        );
    }
} 