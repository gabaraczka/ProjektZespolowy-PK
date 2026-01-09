<?php

namespace App\Controller;

use App\Repository\StudyCardRepository;

class HomeController {
    private StudyCardRepository $cardRepository;

    public function __construct() {
        $this->cardRepository = new StudyCardRepository();
    }

    public function index(): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            return;
        }

        $cards = $this->cardRepository->findByUserId($_SESSION['user_id']);
        require __DIR__ . '/../View/home/index.php';
    }
} 