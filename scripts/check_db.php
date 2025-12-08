<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;

try {
    $db = Database::getInstance();
    
    $result = $db->query("
        SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = 'users'
        );
    ")->fetch(PDO::FETCH_ASSOC);
    
    echo "Users table exists: " . ($result['exists'] ? 'Yes' : 'No') . "\n";
    
    if ($result['exists']) {
        $columns = $db->query("
            SELECT column_name, data_type, column_default 
            FROM information_schema.columns 
            WHERE table_schema = 'public' 
            AND table_name = 'users';
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\nTable structure:\n";
        foreach ($columns as $column) {
            echo "{$column['column_name']} - {$column['data_type']}" . 
                 ($column['column_default'] ? " (default: {$column['column_default']})" : "") . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 