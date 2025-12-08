<?php

namespace App\Controller;

class HomeController {
    public function index(): void {
        require __DIR__ . '/../View/home/index.php';
    }
} 