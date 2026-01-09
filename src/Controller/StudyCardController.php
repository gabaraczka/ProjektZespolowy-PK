<?php

namespace App\Controller;

use App\Model\StudyCard;
use App\Repository\StudyCardRepository;
use App\Repository\FolderRepository;
use App\Repository\MaterialRepository;
use App\Services\OpenAIService;

class StudyCardController {
    private StudyCardRepository $cardRepository;
    private FolderRepository $folderRepository;
    private MaterialRepository $materialRepository;
    private OpenAIService $openAIService;

    public function __construct() {
        $this->cardRepository = new StudyCardRepository();
        $this->folderRepository = new FolderRepository();
        $this->materialRepository = new MaterialRepository();
        $this->openAIService = new OpenAIService();
    }

    public function index(): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            return;
        }

        $userId = $_SESSION['user_id'];
        $cards = $this->cardRepository->findByUserId($userId);
        $folders = $this->folderRepository->findByUserId($userId);
        
        require __DIR__ . '/../View/study-cards/index.php';
    }

    public function loadByFolder(string $folderId): void {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }

        try {
            $folderIdInt = (int)$folderId;
            
            $folder = $this->folderRepository->findById($folderIdInt);
            if (!$folder || $folder->getUserId() !== $_SESSION['user_id']) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Access denied']);
                return;
            }

            $cards = $this->cardRepository->findByFolderId($folderIdInt);
            
            $cardData = [];
            foreach ($cards as $card) {
                $cardData[] = [
                    'id' => $card->getId(),
                    'question' => $card->getQuestion(),
                    'answer' => $card->getAnswer(),
                    'folder_id' => $card->getFolderId()
                ];
            }

            echo json_encode([
                'success' => true,
                'cards' => $cardData
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function generate(): void {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['folder_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Folder ID is required']);
            return;
        }

        try {
            $folderId = (int)$data['folder_id'];
            $userId = $_SESSION['user_id'];

            $folder = $this->folderRepository->findById($folderId);
            if (!$folder || $folder->getUserId() !== $userId) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Access denied']);
                return;
            }

            $materials = $this->materialRepository->findByFolderId($folderId);
            if (empty($materials)) {
                echo json_encode(['success' => false, 'error' => 'Brak materiałów w folderze']);
                return;
            }

            $allContent = '';
            foreach ($materials as $material) {
                if ($this->materialRepository->isNote($material)) {
                    $allContent .= $this->materialRepository->getNoteText($material) . "\n\n";
                } else {
                    $filePath = __DIR__ . '/../../uploads/' . $material->getMaterialPath();
                    if (file_exists($filePath)) {
                        $fileContent = $this->openAIService->extractFileContent($filePath);
                        $allContent .= $fileContent . "\n\n";
                    }
                }
            }

            if (empty(trim($allContent))) {
                echo json_encode(['success' => false, 'error' => 'Brak treści do analizy']);
                return;
            }

            $prompt = "Na podstawie poniższej treści, stwórz 5-10 fiszek do nauki w formacie JSON. Każda fiszka to obiekt z polami 'question' i 'answer'. Odpowiedź w języku polskim w formacie:\n[\n  {\"question\": \"pytanie\", \"answer\": \"odpowiedź\"},\n  {\"question\": \"pytanie2\", \"answer\": \"odpowiedź2\"}\n]\n\nTreść:\n" . $allContent;
            
            $response = $this->openAIService->generateText($prompt);
            
            $cards = $this->parseStudyCardsFromResponse($response);
            
            if (empty($cards)) {
                echo json_encode(['success' => false, 'error' => 'Nie udało się wygenerować fiszek']);
                return;
            }

            $savedCards = [];
            foreach ($cards as $cardData) {
                $card = new StudyCard(
                    $userId,
                    $cardData['question'],
                    $cardData['answer'],
                    null,
                    $folderId
                );
                $this->cardRepository->save($card);
                $savedCards[] = $card;
            }

            echo json_encode([
                'success' => true,
                'message' => 'Wygenerowano ' . count($savedCards) . ' fiszek',
                'count' => count($savedCards)
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    private function parseStudyCardsFromResponse(string $response): array {
        $pattern = '/\[[\s\S]*?\]/';
        if (preg_match($pattern, $response, $matches)) {
            $jsonString = $matches[0];
            $cards = json_decode($jsonString, true);
            if (is_array($cards) && !empty($cards)) {
                $validCards = [];
                foreach ($cards as $card) {
                    if (is_array($card) && isset($card['question']) && isset($card['answer'])) {
                        $validCards[] = [
                            'question' => trim($card['question']),
                            'answer' => trim($card['answer'])
                        ];
                    }
                }
                if (!empty($validCards)) {
                    return $validCards;
                }
            }
        }

        $lines = explode("\n", $response);
        $cards = [];
        $currentCard = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (preg_match('/^(?:Q:|Pytanie:|Question:|\d+\.)\s*(.+)/', $line, $matches)) {
                if ($currentCard && isset($currentCard['answer'])) {
                    $cards[] = $currentCard;
                }
                $currentCard = ['question' => trim($matches[1])];
            }
            elseif (preg_match('/^(?:A:|Odpowiedź:|Answer:)\s*(.+)/', $line, $matches)) {
                if ($currentCard) {
                    $currentCard['answer'] = trim($matches[1]);
                }
            }
            elseif ($currentCard && !isset($currentCard['answer']) && !empty($line)) {
                $currentCard['answer'] = $line;
            }
        }

        if ($currentCard && isset($currentCard['answer'])) {
            $cards[] = $currentCard;
        }

        return $cards;
    }

    public function create(): void {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['question']) || !isset($data['answer'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        try {
            $card = new StudyCard(
                $_SESSION['user_id'],
                $data['question'],
                $data['answer'],
                null,
                $data['folder_id'] ?? null
            );
            
            $this->cardRepository->save($card);
            
            echo json_encode([
                'success' => true,
                'card' => [
                    'id' => $card->getId(),
                    'question' => $card->getQuestion(),
                    'answer' => $card->getAnswer()
                ]
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function delete(): void {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['card_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Card ID is required']);
            return;
        }

        try {
            $success = $this->cardRepository->delete(
                (int)$data['card_id'],
                $_SESSION['user_id']
            );
            
            if ($success) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Card not found']);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function deleteAllByFolder(): void {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['folder_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Folder ID is required']);
            return;
        }

        try {
            $folderId = (int)$data['folder_id'];
            $userId = $_SESSION['user_id'];

            $folder = $this->folderRepository->findById($folderId);
            if (!$folder || $folder->getUserId() !== $userId) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Access denied']);
                return;
            }

            $cards = $this->cardRepository->findByFolderId($folderId);
            $deletedCount = 0;

            foreach ($cards as $card) {
                if ($this->cardRepository->delete($card->getId(), $userId)) {
                    $deletedCount++;
                }
            }

            echo json_encode([
                'success' => true,
                'message' => "Usunięto {$deletedCount} fiszek",
                'count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
} 