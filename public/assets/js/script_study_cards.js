document.addEventListener('DOMContentLoaded', function () {
    const app = new CardApp();
    app.init();
});

class CardApp {
    constructor() {
        this.generateBtn = document.getElementById('generateBtn');
        this.deleteBtn = document.getElementById('deleteBtn');
        this.folderItems = document.querySelectorAll('.sidebar ul li');
        this.titleEl = document.getElementById('card-title');
        this.contentEl = document.getElementById('card-content');
        this.backEl = document.getElementById('card-back');
        this.reverseBtn = document.getElementById("reverseBtn");
        this.prevBtn = document.querySelector('.btn-prev');
        this.nextBtn = document.querySelectorAll('.btn-next')[1];

        this.cards = [];
        this.currentIndex = 0;
    }

    init() {
        this.setupFolderSelection();
        this.setupGenerate();
        this.setupDelete();
        this.setupReverse();
        this.setupNavigation();
        this.loadDefaultFolder();
    }

    setupFolderSelection() {
        this.folderItems.forEach(item => {
            item.addEventListener('click', () => {
                this.folderItems.forEach(el => el.classList.remove('active'));
                item.classList.add('active');

                const folderID = item.dataset.folderid;
                if (this.generateBtn) this.generateBtn.dataset.folderid = folderID;
                if (this.deleteBtn) this.deleteBtn.dataset.folderid = folderID;

                setTimeout(() => {
                    this.loadCards(folderID);
                }, 100);
            });
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
                const folderID = this.deleteBtn.dataset.folderid;
                this.deleteCards(folderID);
            });
        }
    }

    setupReverse() {
        if (this.reverseBtn) {
            this.reverseBtn.addEventListener('click', () => {
                const isFront = this.contentEl.style.display !== "none";
                this.contentEl.style.display = isFront ? "none" : "block";
                this.backEl.style.display = isFront ? "block" : "none";
            });
        }
    }

    setupNavigation() {
        if (this.nextBtn) {
            this.nextBtn.addEventListener('click', () => {
                if (this.cards.length === 0) return;
                this.currentIndex = (this.currentIndex + 1) % this.cards.length;
                this.renderCard(this.currentIndex);
            });
        }

        if (this.prevBtn) {
            this.prevBtn.addEventListener('click', () => {
                if (this.cards.length === 0) return;
                this.currentIndex = (this.currentIndex - 1 + this.cards.length) % this.cards.length;
                this.renderCard(this.currentIndex);
            });
        }
    }

    loadDefaultFolder() {
        const defaultFolder = document.querySelector('.sidebar ul li.active');
        if (defaultFolder) {
            const defaultFolderID = defaultFolder.dataset.folderid;
            if (this.generateBtn) this.generateBtn.dataset.folderid = defaultFolderID;
            if (this.deleteBtn) this.deleteBtn.dataset.folderid = defaultFolderID;
            this.loadCards(defaultFolderID);
        }
    }

    renderCard(index) {
        if (!this.cards[index]) return;

        const card = this.cards[index];
        this.titleEl.textContent = card.title + ` (${index + 1} z ${this.cards.length})`;
        this.contentEl.innerText = card.content;
        this.backEl.innerText = card.back_content;

        this.contentEl.style.display = "block";
        this.backEl.style.display = "none";
    }

    loadCards(folderID) {
        fetch(`load-cards.php?folderid=${folderID}`, {
            method: 'GET',
            credentials: 'include'
        })
            .then(response => response.json())
            .then(data => {
                this.cards = data;
                this.currentIndex = 0;

                if (this.cards.length > 0) {
                    this.renderCard(this.currentIndex);
                } else {
                    this.titleEl.textContent = "Brak fiszek";
                    this.contentEl.innerText = "";
                    this.backEl.innerText = "";
                }
            })
            .catch(err => console.error("Błąd ładowania fiszek:", err));
    }

    generateCards(folderID) {
        fetch('generate-cards.php?folderid=' + folderID, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({ difficulty: 'easy' })
        })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('❌ ' + data.message);
                    return;
                }

                alert('✅ ' + data.message);
                this.loadCards(folderID);
            })
            .catch(err => {
                alert('❌ Błąd połączenia z API.');
                console.error(err);
            });
    }

    deleteCards(folderID) {
        if (!confirm("Czy na pewno chcesz usunąć wszystkie fiszki z tego folderu?")) return;

        fetch('delete-cards.php?folderid=' + folderID, {
            method: 'DELETE',
            credentials: 'include'
        })
            .then(response => response.json())
            .then(data => {
                alert(data.success ? '✅ ' + data.message : '❌ ' + data.message);
                this.loadCards(folderID);
            })
            .catch(err => {
                alert('❌ Błąd usuwania fiszek.');
                console.error(err);
            });
    }
}
