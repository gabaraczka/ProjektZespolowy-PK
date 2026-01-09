<?php

namespace App\Repository;

use App\Config\Database;
use App\Model\Material;
use PDO;

class MaterialRepository {
    private PDO $database;

    public function __construct() {
        $this->database = Database::getInstance();
    }

    public function create(int $userId, int $folderId, string $materialName, string $materialPath): bool {
        $stmt = $this->database->prepare('
            INSERT INTO materials (userid, folderid, material_name, material_path) 
            VALUES (:userid, :folderid, :material_name, :material_path)
        ');
        
        return $stmt->execute([
            'userid' => $userId,
            'folderid' => $folderId,
            'material_name' => $materialName,
            'material_path' => $materialPath
        ]);
    }

    public function findByFolderId(int $folderId): array {
        $stmt = $this->database->prepare('
            SELECT * FROM materials 
            WHERE folderid = :folderid 
            ORDER BY material_name ASC
        ');
        
        $stmt->execute(['folderid' => $folderId]);
        $materials = [];
        
        while ($materialData = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $materials[] = new Material(
                $materialData['materialid'],
                $materialData['userid'],
                $materialData['folderid'],
                $materialData['material'] ?? null,
                $materialData['material_name'],
                $materialData['material_data'] ?? null,
                $materialData['material_path']
            );
        }
        
        return $materials;
    }

    public function findByUserId(int $userId): array {
        $stmt = $this->database->prepare('
            SELECT * FROM materials 
            WHERE userid = :userid 
            ORDER BY material_name ASC
        ');
        
        $stmt->execute(['userid' => $userId]);
        $materials = [];
        
        while ($materialData = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $materials[] = new Material(
                $materialData['materialid'],
                $materialData['userid'],
                $materialData['folderid'],
                $materialData['material'] ?? null,
                $materialData['material_name'],
                $materialData['material_data'] ?? null,
                $materialData['material_path']
            );
        }
        
        return $materials;
    }

    public function delete(int $materialId, int $userId): bool {
        $stmt = $this->database->prepare('
            DELETE FROM materials 
            WHERE materialid = :materialid AND userid = :userid
        ');
        
        return $stmt->execute([
            'materialid' => $materialId,
            'userid' => $userId
        ]);
    }

    public function findById(int $materialId): ?Material {
        $stmt = $this->database->prepare('
            SELECT * FROM materials WHERE materialid = :materialid
        ');
        
        $stmt->execute(['materialid' => $materialId]);
        $materialData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$materialData) {
            return null;
        }
        
        return new Material(
            $materialData['materialid'],
            $materialData['userid'],
            $materialData['folderid'],
            $materialData['material'] ?? null,
            $materialData['material_name'],
            $materialData['material_data'] ?? null,
            $materialData['material_path']
        );
    }
    
    public function createNote(int $userId, int $folderId, string $noteText): bool {
        $timestamp = date('ymd_His');
        
        $noteText = mb_convert_encoding($noteText, 'UTF-8', 'UTF-8');
        $noteText = trim($noteText);
        $noteText = htmlspecialchars($noteText, ENT_QUOTES, 'UTF-8');
        
        $stmt = $this->database->prepare('
            INSERT INTO materials (userid, folderid, material_name, material_path) 
            VALUES (:userid, :folderid, :material_name, :material_path)
        ');
        
        return $stmt->execute([
            'userid' => $userId,
            'folderid' => $folderId,
            'material_name' => $timestamp . '_NOTATKA: ' . substr($noteText, 0, 200),
            'material_path' => ''
        ]);
    }
    
    public function isNote(Material $material): bool {
        return preg_match('/^\d{6}_\d{6}_NOTATKA: /', $material->getMaterialName());
    }
    
    public function getNoteText(Material $material): string {
        if ($this->isNote($material)) {
            return preg_replace('/^\d{6}_\d{6}_NOTATKA: /', '', $material->getMaterialName());
        }
        return '';
    }
} 