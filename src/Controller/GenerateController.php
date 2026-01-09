<?php

namespace App\Controller;

use App\Repository\FolderRepository;
use App\Repository\MaterialRepository;
use App\Services\OpenAIService;

class GenerateController {
    private FolderRepository $folderRepository;
    private MaterialRepository $materialRepository;
    private OpenAIService $openAIService;

    public function __construct() {
        $this->folderRepository = new FolderRepository();
        $this->materialRepository = new MaterialRepository();
        $this->openAIService = new OpenAIService();
    }
    
    public function index(): void {
        $isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
        $folders = [];
        $folderMaterials = [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$isLoggedIn) {
                if ($this->isJsonRequest()) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => 'Musisz być zalogowany']);
                    exit();
                }
                header('Location: /login');
                exit();
            }
            
            $userId = $_SESSION['user_id'];
            
            if ($this->isJsonRequest()) {
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (isset($input['folderName'])) {
                    $folderName = trim($input['folderName']);
                    if (!empty($folderName)) {
                        $success = $this->folderRepository->create($folderName, $userId);
                        header('Content-Type: application/json');
                        echo json_encode(['success' => $success]);
                        exit();
                    }
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => 'Nazwa folderu nie może być pusta']);
                    exit();
                }
                
                if (isset($input['noteText'])) {
                    ini_set('display_errors', 1);
                    error_reporting(E_ALL);
                    
                    $noteText = trim($input['noteText']);
                    $selectedFolders = $input['selectedFolders'] ?? [];
                    
                    if (empty($noteText)) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'error' => 'Notatka nie może być pusta']);
                        exit();
                    }
                    
                    if (count($selectedFolders) !== 1) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'error' => 'Zaznacz dokładnie jeden folder']);
                        exit();
                    }
                    
                    $folderId = intval($selectedFolders[0]);
                    
                    $userFolders = $this->folderRepository->findByUserId($userId);
                    $folderExists = false;
                    foreach ($userFolders as $folder) {
                        if ($folder->getId() === $folderId) {
                            $folderExists = true;
                            break;
                        }
                    }
                    
                    if (!$folderExists) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'error' => 'Folder nie należy do użytkownika']);
                        exit();
                    }
                    
                    try {
                        $success = $this->materialRepository->createNote($userId, $folderId, $noteText);
                        
                        header('Content-Type: application/json');
                        echo json_encode(['success' => $success]);
                        exit();
                    } catch (\Exception $e) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
                        exit();
                    }
                }
                
                if (isset($input['selectedFiles'])) {
                    $selectedFileIds = $input['selectedFiles'];
                    $summaryData = [];
                    
                    foreach ($selectedFileIds as $materialId) {
                        $material = $this->materialRepository->findById(intval($materialId));
                        if ($material && $material->getUserId() === $userId) {
                            try {
                                if (empty($material->getMaterialPath()) && $this->materialRepository->isNote($material)) {
                                    $noteText = $this->materialRepository->getNoteText($material);
                                    $summary = $this->openAIService->generateSummaryFromText($noteText, $material->getMaterialName());
                                } else {
                                    $filePath = __DIR__ . '/../../uploads/' . $material->getMaterialPath();
                                    $summary = $this->openAIService->generateSummary($filePath, $material->getMaterialName());
                                }
                                
                                $summaryData[] = [
                                    'material_name' => $material->getMaterialName(),
                                    'summary' => $summary,
                                    'material_path' => $material->getMaterialPath()
                                ];
                            } catch (\Exception $e) {
                                $summaryData[] = [
                                    'material_name' => $material->getMaterialName(),
                                    'summary' => 'Error generating summary: ' . $e->getMessage(),
                                    'material_path' => $material->getMaterialPath()
                                ];
                            }
                        }
                    }
                    
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'data' => $summaryData]);
                    exit();
                }
            }
            
            if (isset($_FILES['file']) && isset($_POST['selectedFolderID'])) {
                $file = $_FILES['file'];
                $folderId = intval($_POST['selectedFolderID']);
                
                if ($file['error'] === UPLOAD_ERR_OK) {
                    $allowedTypes = ['application/pdf', 'application/msword', 
                                   'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                   'text/plain'];
                    $fileType = $file['type'];
                    $fileName = $file['name'];
                    
                    if (!in_array($fileType, $allowedTypes)) {
                        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                            header('Content-Type: application/json');
                            echo json_encode(['success' => false, 'error' => 'Nieobsługiwany typ pliku. Prześlij pliki PDF, DOC, DOCX lub TXT.']);
                            exit();
                        }
                        $_SESSION['error'] = 'Nieobsługiwany typ pliku. Prześlij pliki PDF, DOC, DOCX lub TXT.';
                        header('Location: /generate');
                        exit();
                    }
                    
                    $uploadDir = __DIR__ . '/../../uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                    $uniqueFileName = uniqid() . '_' . time() . '.' . $extension;
                    $uploadPath = $uploadDir . $uniqueFileName;
                    
                    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                        $success = $this->materialRepository->create($userId, $folderId, $fileName, $uniqueFileName);
                        
                        if ($success) {
                            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                                header('Content-Type: application/json');
                                echo json_encode(['success' => true, 'message' => 'Plik przesłano pomyślnie!']);
                                exit();
                            }
                            $_SESSION['success'] = 'Plik przesłano pomyślnie!';
                            header('Location: /generate');
                            exit();
                        } else {
                            unlink($uploadPath);
                            $errorMessage = 'Failed to save file information to database.';
                            
                            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                                header('Content-Type: application/json');
                                echo json_encode(['success' => false, 'error' => $errorMessage]);
                                exit();
                            }
                            $_SESSION['error'] = $errorMessage;
                            header('Location: /generate');
                            exit();
                        }
                    } else {
                        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                            header('Content-Type: application/json');
                            echo json_encode(['success' => false, 'error' => 'Nie udało się przesłać pliku.']);
                            exit();
                        }
                        $_SESSION['error'] = 'Failed to upload file.';
                        header('Location: /generate');
                        exit();
                    }
                } else {
                    $errorMessage = 'Błąd przesyłania: ';
                    switch ($file['error']) {
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            $errorMessage .= 'File is too large.';
                            break;
                        case UPLOAD_ERR_PARTIAL:
                            $errorMessage .= 'File was only partially uploaded.';
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            $errorMessage .= 'No file was uploaded.';
                            break;
                        default:
                            $errorMessage .= 'Unknown error occurred.';
                    }
                    
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'error' => $errorMessage]);
                        exit();
                    }
                    $_SESSION['error'] = $errorMessage;
                    header('Location: /generate');
                    exit();
                }
            }
            
            if (isset($_POST['folderName'])) {
                $folderName = trim($_POST['folderName']);
                if (!empty($folderName)) {
                    $this->folderRepository->create($folderName, $userId);
                }
                header('Location: /generate');
                exit();
            }
            
            if (isset($_POST['deleteID']) && isset($_POST['deleteType']) && $_POST['deleteType'] === 'folder') {
                $folderId = intval($_POST['deleteID']);
                $this->folderRepository->delete($folderId, $userId);
                header('Location: /generate');
                exit();
            }
            
            if (isset($_POST['deleteID']) && isset($_POST['deleteType']) && $_POST['deleteType'] === 'material') {
                $materialId = intval($_POST['deleteID']);
                
                $material = $this->materialRepository->findById($materialId);
                if ($material && $material->getUserId() === $userId) {
                    $success = $this->materialRepository->delete($materialId, $userId);
                    
                    if ($success && $material->getMaterialPath()) {
                        $filePath = __DIR__ . '/../../uploads/' . $material->getMaterialPath();
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
                }
                header('Location: /generate');
                exit();
            }
        }
        
        if ($isLoggedIn) {
            $userId = $_SESSION['user_id'];
            $folders = $this->folderRepository->findByUserId($userId);
            
            foreach ($folders as $folder) {
                $folderMaterials[$folder->getId()] = $this->materialRepository->findByFolderId($folder->getId());
            }
        }
        
        $materialRepository = $this->materialRepository;
        
        require_once __DIR__ . '/../View/generate/index.php';
    }
    
    private function isJsonRequest(): bool {
        return isset($_SERVER['CONTENT_TYPE']) && 
               strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;
    }
}