<!DOCTYPE html>
<?php
    $isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    $email = $isLoggedIn ? $_SESSION['email'] ?? '' : '';
?>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Cards - StudyBuddy</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/navbar.css">
    <link rel="stylesheet" href="/assets/css/styles-study-cards.css">
    <script src="/assets/js/hamburger.js" defer></script>
    <style>
        .btn-reverse {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 18px;
            line-height: 1;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body style="background: url('/assets/images/image.png') no-repeat center center/cover;">
    <div class="navbar">
        <div class="nav-logo">
            <img src="/assets/images/logo.png" alt="Study Buddy">
        </div>
        <div class="nav-menu" id="nav-menu">
            <div class="nav-links">
                <a href="/">Home</a>
                <a href="/generate">Create Summary</a>
                <a href="/study-cards" class="active">Study Cards</a>
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

    <?php if ($isLoggedIn): ?>
        <div class="study-cards-container">
            <div class="sidebar">
                <h2>Foldery</h2>
                <ul>
                    <?php if (!empty($folders)): ?>
                        <?php foreach ($folders as $index => $folder): ?>
                            <li 
                                class="<?php echo $index === 0 ? 'active' : ''; ?>" 
                                data-folderid="<?php echo $folder->getId(); ?>">
                                <?php echo htmlspecialchars($folder->getFolderName()); ?>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>Brak dostępnych folderów</li>
                    <?php endif; ?>
                </ul>

                <?php if (!empty($folders)): ?>
                    <button class="btn-generate" id="generateBtn" data-folderid="<?php echo $folders[0]->getId(); ?>">
                        Generuj Fiszki
                    </button>
                    <button class="btn-delete" id="deleteBtn" data-folderid="<?php echo $folders[0]->getId(); ?>">
                        Usuń Fiszki
                    </button>
                <?php endif; ?>
            </div>

            <div class="study-card">
                <?php if (!empty($cards)): ?>
                    <p><strong id="card-title"><?php echo htmlspecialchars($cards[0]->getQuestion()); ?></strong></p>
                    <p id="card-content"><?php echo nl2br(htmlspecialchars($cards[0]->getAnswer())); ?></p>
                    <p id="card-back" style="display:none;"><?php echo nl2br(htmlspecialchars($cards[0]->getAnswer())); ?></p>
                <?php else: ?>
                    <p><strong id="card-title">Brak fiszek</strong></p>
                    <p id="card-content">Wybierz folder i wygeneruj fiszki</p>
                    <p id="card-back" style="display:none;"></p>
                <?php endif; ?>
    </div>

            <div class="buttons">
                <button class="btn-prev" style="z-index: 100; position: relative; pointer-events: auto;">Poprzednia</button>
                <button class="btn-reverse" id="reverseBtn" title="Odwróć" style="z-index: 100; position: relative; pointer-events: auto;"><i class="fas fa-sync-alt"></i></button>
                <button class="btn-next" style="z-index: 100; position: relative; pointer-events: auto;">Następna</button>
            </div>
        </div>
    <?php else: ?>
        <div class="study-cards-container">
            <div class="study-card">
                <p><strong>Zaloguj się</strong></p>
                <p>Musisz się zalogować, aby korzystać z fiszek.</p>
                <a href="/login" style="color: var(--primary); text-decoration: none; font-weight: bold;">
                    <i class="fas fa-sign-in-alt"></i> Zaloguj się
                </a>
            </div>
        </div>
    <?php endif; ?>

    <script>
        // pop up pojawia sie na 5 sekund
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

        class StudyCardsApp {
            constructor() {
                this.generateBtn = document.getElementById('generateBtn');
                this.deleteBtn = document.getElementById('deleteBtn');
                this.folderItems = document.querySelectorAll('.sidebar ul li');
                this.titleEl = document.getElementById('card-title');
                this.contentEl = document.getElementById('card-content');
                this.backEl = document.getElementById('card-back');
                this.reverseBtn = document.getElementById("reverseBtn");
                this.prevBtn = document.querySelector('.btn-prev');
                this.nextBtn = document.querySelector('.btn-next');

                this.cards = [];
                this.currentIndex = 0;
                this.isFlipped = false;

                this.init();
            }

            init() {
                this.setupFolderSelection();
                this.setupGenerate();
                this.setupDelete();
                this.setupNavigation();
                this.setupReverse();
                this.loadDefaultFolder();
            }

            setupFolderSelection() {
                this.folderItems.forEach(item => {
                    if (item.textContent.trim() !== 'Brak dostępnych folderów') {
                        item.addEventListener('click', () => {
                            this.folderItems.forEach(el => el.classList.remove('active'));
                            item.classList.add('active');

                            const folderID = item.dataset.folderid;
                            if (this.generateBtn) this.generateBtn.dataset.folderid = folderID;
                            if (this.deleteBtn) this.deleteBtn.dataset.folderid = folderID;

                            this.loadCards(folderID);
                        });
                    }
                });
            }

            setupGenerate() {
                if (this.generateBtn) {
                    this.generateBtn.addEventListener('click', () => {
                        const folderID = this.generateBtn.dataset.folderid;
                        this.generateCards(folderID);
                    });
                }
            }

            setupDelete() {
                if (this.deleteBtn) {
                    this.deleteBtn.addEventListener('click', () => {
                        if (confirm('Czy na pewno chcesz usunąć wszystkie fiszki z tego folderu?')) {
                            const folderID = this.deleteBtn.dataset.folderid;
                            this.deleteAllCards(folderID);
                        }
                    });
                }
            }

            setupNavigation() {
                if (this.prevBtn) {
                    this.prevBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        if (this.cards.length > 0 && this.currentIndex > 0) {
                            this.currentIndex--;
                            this.renderCard(this.currentIndex);
                        }
                    });
                }

                if (this.nextBtn) {
                    this.nextBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        if (this.cards.length > 0 && this.currentIndex < this.cards.length - 1) {
                            this.currentIndex++;
                            this.renderCard(this.currentIndex);
                        }
                    });
                }
            }

            setupReverse() {
                if (this.reverseBtn) {
                    this.reverseBtn.addEventListener('click', () => {
                        this.isFlipped = !this.isFlipped;
                        if (this.isFlipped) {
                            this.contentEl.style.display = 'none';
                            this.backEl.style.display = 'block';
                        } else {
                            this.contentEl.style.display = 'block';
                            this.backEl.style.display = 'none';
                        }
                    });
                }
            }

            loadDefaultFolder() {
                const defaultFolder = document.querySelector('.sidebar ul li.active');
                if (defaultFolder && defaultFolder.dataset.folderid) {
                    const defaultFolderID = defaultFolder.dataset.folderid;
                    if (this.generateBtn) this.generateBtn.dataset.folderid = defaultFolderID;
                    if (this.deleteBtn) this.deleteBtn.dataset.folderid = defaultFolderID;
                    // Only load cards if there are already cards for this folder
                    this.loadCards(defaultFolderID);
                }
            }

            renderCard(index) {
                if (!this.cards[index]) {
                    return;
                }

                const card = this.cards[index];
                
                this.titleEl.textContent = `Pytanie ${index + 1} z ${this.cards.length}`;
                
                this.contentEl.innerHTML = card.question;
                
                this.backEl.innerHTML = card.answer;
                this.backEl.style.display = "none";
                this.contentEl.style.display = "block";
                
                this.isFlipped = false;
            }

            loadCards(folderID) {
                fetch(`/study-cards/load/${folderID}`, {
                    method: 'GET',
                    credentials: 'include'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.cards = data.cards || [];
                        this.currentIndex = 0;
                        this.isFlipped = false;

                        if (this.cards.length > 0) {
                            this.renderCard(this.currentIndex);
                        } else {
                            this.titleEl.textContent = "Brak fiszek";
                            this.contentEl.innerHTML = "Wybierz folder i wygeneruj fiszki";
                            this.backEl.innerHTML = "";
                            this.contentEl.style.display = "block";
                            this.backEl.style.display = "none";
                        }
                    } else {
                        console.error("Błąd ładowania fiszek:", data.error);
                        this.titleEl.textContent = "Błąd";
                        this.contentEl.innerHTML = "Nie udało się załadować fiszek";
                        this.backEl.innerHTML = "";
                        this.contentEl.style.display = "block";
                        this.backEl.style.display = "none";
                    }
                })
                .catch(err => {
                    console.error("Błąd ładowania fiszek:", err);
                    this.titleEl.textContent = "Błąd";
                    this.contentEl.innerHTML = "Błąd połączenia";
                    this.backEl.innerHTML = "";
                    this.contentEl.style.display = "block";
                    this.backEl.style.display = "none";
                });
            }

            generateCards(folderID) {
                fetch('/study-cards/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    credentials: 'include',
                    body: JSON.stringify({ 
                        folder_id: folderID,
                        difficulty: 'easy' 
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ ' + data.message);
                        this.loadCards(folderID);
                    } else {
                        alert('❌ ' + (data.error || data.message || 'Błąd generowania fiszek'));
                    }
                })
                .catch(err => {
                    alert('❌ Błąd połączenia z API.');
                    console.error(err);
                });
            }

            deleteAllCards(folderID) {
                fetch('/study-cards/delete-all', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    credentials: 'include',
                    body: JSON.stringify({ 
                        folder_id: folderID
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ ' + (data.message || 'Usunięto wszystkie fiszki'));
                        this.loadCards(folderID); // Reload to show empty state
                    } else {
                        alert('❌ ' + (data.error || data.message || 'Błąd usuwania fiszek'));
                    }
                })
                .catch(err => {
                    alert('❌ Błąd połączenia z API.');
                    console.error(err);
                });
            }
        }

        // Initialize the app when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            if (document.querySelector('.study-cards-container')) {
                new StudyCardsApp();
            }
        });
    </script>
</body>
</html> 