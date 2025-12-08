<?php

namespace App\Controller;

/**
 * Example API Controller - Basic GET/POST methods
 * This is a template for adding API endpoints
 */
class ApiController {
    
    /**
     * Example GET endpoint
     * Usage: GET /api/example
     */
    public function getExample(): void {
        header('Content-Type: application/json');
        
        $data = [
            'status' => 'success',
            'message' => 'This is a GET request example',
            'method' => 'GET',
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        echo json_encode($data);
    }
    
    /**
     * Example POST endpoint
     * Usage: POST /api/example
     */
    public function postExample(): void {
        header('Content-Type: application/json');
        
        // Get POST data
        $input = json_decode(file_get_contents('php://input'), true);
        
        $data = [
            'status' => 'success',
            'message' => 'This is a POST request example',
            'method' => 'POST',
            'received_data' => $input ?? $_POST,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        echo json_encode($data);
    }
}

