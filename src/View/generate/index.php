<!DOCTYPE html>
<?php
    $isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    $email = $isLoggedIn ? $_SESSION['email'] ?? '' : '';
?>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Summary</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/navbar.css">
    <link rel="stylesheet" href="/assets/css/styles_generate_new.css">

    <script src="/assets/js/script_generate.js" defer></script>
    <script src="/assets/js/hamburger.js" defer></script>
</head>
<body>
    <div class="navbar">
        <div class="nav-logo">
            <img src="/assets/images/logo.png" alt="Study Buddy">
        </div>
        <div class="nav-menu" id="nav-menu">
            <div class="nav-links">
                <a href="/">Home</a>
                <a href="/generate" class="active">Create Summary</a>
                <a href="/study-cards">Study Cards</a>
            </div>
            <div>
                <?php if ($isLoggedIn): ?>
                    <span>Witaj, <?php echo htmlspecialchars($email); ?></span>
                    <a href="/logout" class="login-btn">Wyloguj się</a>
                <?php else: ?>
                    <a href="/login" class="login-btn">Log in</a>
                    <a href="/register" class="signup-btn">Sign up</a> 
                <?php endif; ?>
            </div>
        </div>
        <button class="hamburger" id="hamburger">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>

    <div id="loading" class="loading hidden">
        <i class="fas fa-spinner fa-spin"></i> Generowanie streszczenia...
    </div>

    <!-- Session Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success" style="position: fixed; top: 80px; right: 20px; background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; z-index: 1000; border: 1px solid #c3e6cb;">
            <?php echo htmlspecialchars($_SESSION['success']); ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error" style="position: fixed; top: 80px; right: 20px; background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; z-index: 1000; border: 1px solid #f5c6cb;">
            <?php echo htmlspecialchars($_SESSION['error']); ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="container">
        <div class="white-block">
            <div class="left-section">
                <h1>Add Materials</h1>
                
                <?php if ($isLoggedIn): ?>                    
                    <!-- Folders List -->
                    <div class="file-list" id="fileList">
                        <?php if (!empty($folders)): ?>
                            <?php foreach ($folders as $folder): ?>
                                <div class="folder-item">
                                    <div class="folder-header">
                                        <div class="folder-info">
                                            <i class="fas fa-folder"></i> 
                                            <input type="checkbox" class="folder-checkbox" id="folder-<?php echo htmlspecialchars($folder->getId()); ?>" value="<?php echo htmlspecialchars($folder->getId()); ?>">
                                            <label for="folder-<?php echo htmlspecialchars($folder->getId()); ?>" class="folder-label">
                                                <?php echo htmlspecialchars($folder->getFolderName()); ?>
                                            </label>
                                        </div>
                                        <form method="POST" action="/generate" style="display: inline;">
                                            <input type="hidden" name="deleteID" value="<?php echo $folder->getId(); ?>">
                                            <input type="hidden" name="deleteType" value="folder">
                                            <button type="submit" class="delete-btn" title="Usuń folder">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                    
                                    <!-- Materials in this folder -->
                                    <?php if (isset($folderMaterials[$folder->getId()]) && !empty($folderMaterials[$folder->getId()])): ?>
                                        <ul class="material-list">
                                            <?php 
                                            $regularMaterials = [];
                                            $notes = [];
                                            foreach ($folderMaterials[$folder->getId()] as $material) {
                                                if ($materialRepository->isNote($material)) {
                                                    $notes[] = $material;
                                                } else {
                                                    $regularMaterials[] = $material;
                                                }
                                            }
                                            ?>
                                            
                                            <!-- Regular materials -->
                                            <?php foreach ($regularMaterials as $material): ?>
                                                <li class="material-item">
                                                    <div class="material-content">
                                                        <input type="checkbox" class="material-checkbox" id="material-<?php echo $material->getId(); ?>" value="<?php echo $material->getId(); ?>">
                                                        <label for="material-<?php echo $material->getId(); ?>" class="material-label">
                                                            <i class="fas fa-file"></i>
                                                            <?php if ($material->getMaterialPath() && $material->getMaterialPath() !== ''): ?>
                                                                <a href="/uploads/<?php echo htmlspecialchars($material->getMaterialPath()); ?>" target="_blank">
                                                                    <?php echo htmlspecialchars($material->getMaterialName()); ?>
                                                                </a>
                                                            <?php else: ?>
                                                                <?php echo htmlspecialchars($material->getMaterialName()); ?>
                                                            <?php endif; ?>
                                                        </label>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                        
                                        <!-- Notes in this folder -->
                                        <?php if (!empty($notes)): ?>
                                            <div class="notes-section">
                                                <h4><i class="fas fa-sticky-note"></i> Notatki:</h4>
                                                <ul class="notes-list">
                                                    <?php foreach ($notes as $note): ?>
                                                        <li class="note-item">
                                                            <div class="note-content">
                                                                <input type="checkbox" class="material-checkbox" id="note-<?php echo $note->getId(); ?>" value="<?php echo $note->getId(); ?>">
                                                                <label for="note-<?php echo $note->getId(); ?>" style="display: flex; align-items: center; gap: 8px; flex: 1;">
                                                                    <i class="fas fa-note-sticky"></i>
                                                                    <span class="note-text"><?php echo htmlspecialchars($materialRepository->getNoteText($note)); ?></span>
                                                                </label>
                                                                <form method="POST" action="/generate" style="display: inline; margin-left: auto;">
                                                                    <input type="hidden" name="deleteID" value="<?php echo $note->getId(); ?>">
                                                                    <input type="hidden" name="deleteType" value="material">
                                                                    <button type="submit" class="delete-note-btn" title="Usuń notatkę">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <p class="no-materials">Brak materiałów w tym folderze.</p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-folders">Brak folderów. Dodaj pierwszy folder.</div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="file-list" id="fileList">
                        <div class="no-folders">Zaloguj się aby dodawać materiały.</div>
                    </div>
                <?php endif; ?>

                <!-- Hidden Upload Form -->
                <form id="uploadForm" method="POST" action="/generate" enctype="multipart/form-data" style="display: none;">
                    <input type="file" id="fileInput" name="file" accept=".pdf,.doc,.docx,.txt">
                    <input type="hidden" id="selectedFolderID" name="selectedFolderID">
                </form>
        
                <div class="button-group">
                    <button class="add-folder-btn" id="addFolderBtn" title="Dodaj folder">
                        <i class="fas fa-folder-plus"></i>
                        <span class="btn-text">Add Folder</span>
                    </button>

                    <button id="triggerFileInput" class="add-material-btn" title="Dodaj materiał">
                        <i class="fas fa-plus-circle"></i>
                        <span class="btn-text">Add Material</span>
                    </button>

                    <form method="POST" action="/generate" class="remove-item-form">
                        <input type="hidden" name="deleteID" id="deleteID">
                        <input type="hidden" name="deleteType" id="deleteType">
                        <button type="submit" class="remove-item-btn" title="Usuń">
                            <i class="fas fa-trash-alt"></i>
                            <span class="btn-text">Move to Trash</span>
                        </button>
                    </form>

                    <form method="POST" action="#" id="generateResponseForm">
                        <button type="button" id="generateResponse" class="generate-response-btn" title="Wygeneruj streszczenie">
                            <i class="fas fa-magic"></i>
                            <span class="btn-text">Generate Response</span>
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="right-section">
                <h2>Response</h2>

                <div id="summaryResult" class="hidden">
                    <h2>Generated Summary</h2>
                    <p id="summaryText"></p>
                </div>
                
                <h2>Add Notes</h2>
                <textarea id="notesInput" placeholder="Write your notes here..."></textarea>
                <button id="sendNoteButton" class="generate-response-btn">
                    <i class="fas fa-paper-plane"></i> Send Note
                </button>
            </div>
        </div>
    </div>

    <div class="background-image"></div>

    <script>
        // Auto-hide session messages after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease-out';
                setTimeout(() => {
                    alert.remove();
                }, 500);
            });
        }, 5000);
    </script>
</body>
</html> 