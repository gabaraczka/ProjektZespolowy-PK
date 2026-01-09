document.addEventListener("DOMContentLoaded", function () {
    const app = new UploadApp();
    app.init();
});

class UploadApp {
    constructor() {
        this.fileInput = document.getElementById("fileInput");
        this.uploadForm = document.getElementById("uploadForm");
        this.loading = document.getElementById("loading");
        this.summaryResult = document.getElementById("summaryResult");
        this.generateButton = document.getElementById("generateSummary");
        this.generateResponseBtn = document.getElementById("generateResponse");
        this.addFolderBtn = document.getElementById("addFolderBtn");
        this.sendNoteButton = document.getElementById("sendNoteButton");
        this.notesInput = document.getElementById("notesInput");
        this.hamburger = document.querySelector(".hamburger");
        this.navMenu = document.querySelector(".nav-menu");
        this.generateBtn = document.querySelector(".generate-btn");
        this.materialCheckboxes = document.querySelectorAll(".material-checkbox");
        this.folderCheckboxes = document.querySelectorAll(".folder-checkbox");
        this.deleteCheckboxes = document.querySelectorAll("input[name='deleteFiles[]']");
        this.deleteBtn = document.getElementById("deleteSelectedBtn");

        this.setupFileTriggers();
    }

    init() {
        this.setupRedirectToGenerate();
        this.setupSendNote();
        this.setupUpload();
        this.setupAddFolder();
        this.setupGenerateResponse();
        this.setupGenerateSummary();
        this.setupMaterialDelete();
    }

    setupRedirectToGenerate() {
        if (this.generateBtn) {
            this.generateBtn.addEventListener("click", (e) => {
                e.preventDefault();
                window.location.href = "/generate";
            });
        }
    }

    setupSendNote() {
        if (this.sendNoteButton) {
            this.sendNoteButton.addEventListener("click", () => {
                const noteText = this.notesInput.value.trim();
                const selectedFolder = Array.from(this.folderCheckboxes)
                    .filter(cb => cb.checked)
                    .map(cb => cb.value);

                if (!noteText) return alert("Notatka jest pusta!");
                if (selectedFolder.length !== 1) return alert("Zaznacz dokładnie jeden folder.");

                fetch("/generate", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        noteText,
                        selectedFolders: selectedFolder
                    })
                })
                    .then(res => res.text())
                    .then(text => {
                        console.log("Raw server response:", text);
                        try {
                            const data = JSON.parse(text);
                            if (data.success) {
                                alert("✅ Notatka zapisana!");
                                location.reload();
                            } else {
                                alert("❌ Błąd: " + (data.error || "nieznany problem."));
                            }
                        } catch (parseError) {
                            console.error("JSON Parse Error:", parseError);
                            console.error("Server returned:", text);
                            alert("❌ Serwer zwrócił nieprawidłową odpowiedź. Sprawdź konsolę.");
                        }
                    })
                    .catch(err => {
                        console.error("❌ Błąd details:", err);
                        console.error("❌ Error name:", err.name);
                        console.error("❌ Error message:", err.message);
                        console.error("❌ Error stack:", err.stack);
                        alert("❌ Nie udało się zapisać notatki. Sprawdź konsolę dla szczegółów.");
                    });
            });
        }
    }

    setupFileTriggers() {
        const triggerFileInput = document.getElementById("triggerFileInput");
        const fileInput = document.getElementById("fileInput");
        const uploadForm = document.getElementById("uploadForm");
        const selectedFolderID = document.getElementById("selectedFolderID");
        const folderCheckboxes = document.querySelectorAll(".folder-checkbox");

        if (triggerFileInput && fileInput && uploadForm) {
            triggerFileInput.addEventListener("click", () => {
                const selectedFolder = Array.from(folderCheckboxes).find(cb => cb.checked);
                if (!selectedFolder) {
                    alert("Wybierz folder przed dodaniem materiałów.");
                    return;
                }
                selectedFolderID.value = selectedFolder.value;
                fileInput.click();
            });

            fileInput.addEventListener("change", () => {
                if (fileInput.files.length > 0) {
                    uploadForm.submit();
                }
            });
        }
    }

    setupUpload() {
        if (this.uploadForm) {
            this.uploadForm.addEventListener("submit", (e) => {
                e.preventDefault();
                if (!this.fileInput.files.length) {
                    alert("Wybierz plik przed generowaniem podsumowania.");
                    return;
                }

                const selectedFolderID = document.getElementById("selectedFolderID");
                if (!selectedFolderID || !selectedFolderID.value) {
                    alert("Wybierz folder najpierw.");
                    return;
                }

                this.loading.classList.remove("hidden");
                this.summaryResult.classList.add("hidden");

                const formData = new FormData();
                formData.append("file", this.fileInput.files[0]);
                formData.append("selectedFolderID", selectedFolderID.value);

                fetch("/generate", {
                    method: "POST",
                    body: formData,
                })
                    .then(response => response.text())
                    .then(text => {
                        this.loading.classList.add("hidden");
                        
                        try {
                            // Try to parse as JSON first
                            const data = JSON.parse(text);
                            if (data.success) {
                                alert("✅ File uploaded successfully!");
                                location.reload();
                            } else {
                                alert("❌ Error: " + (data.error || "Unknown error"));
                            }
                        } catch (e) {
                            // If not JSON, check if it's a redirect (successful upload)
                            if (text.includes("<!DOCTYPE html>") || text === "") {
                                alert("✅ File uploaded successfully!");
                                location.reload();
                            } else {
                                alert("Wystąpił błąd. Sprawdź konsolę aby zobaczyć szczegóły.");
                                console.error("Response:", text);
                            }
                        }
                    })
                    .catch(error => {
                        this.loading.classList.add("hidden");
                        alert("Wystąpił błąd podczas przesyłania pliku.");
                        console.error(error);
                    });
            });
        }
    }

    setupAddFolder() {
        if (this.addFolderBtn) {
            this.addFolderBtn.addEventListener("click", () => {
                const folderName = prompt("Podaj nazwę folderu:");
                if (!folderName) return;

                fetch("/generate", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ folderName }),
                })
                    .then((res) => res.text())
                    .then((text) => {
                        const data = JSON.parse(text);
                        if (data.success) {
                            alert("📁 Folder dodany!");
                            location.reload();
                        } else {
                            alert("❌ " + data.error);
                        }
                    })
                    .catch((err) => {
                        console.error("❌ Błąd JSON lub fetch:", err);
                        alert("Wystąpił błąd (brak JSON lub odpowiedź HTML).");
                    });
            });
        }
    }

    setupGenerateResponse() {
        if (this.generateResponseBtn) {
            this.generateResponseBtn.addEventListener("click", () => {
                const selectedFiles = Array.from(this.materialCheckboxes)
                    .filter(cb => cb.checked)
                    .map(cb => cb.value);

                if (selectedFiles.length === 0) {
                    alert("Wybierz co najmniej jeden plik przed generowaniem streszczenia.");
                    return;
                }

                this.loading.classList.remove("hidden");
                this.summaryResult.classList.add("hidden");

                fetch("/generate", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({ selectedFiles })
                })
                    .then(response => response.json())
                    .then(data => {
                        this.loading.classList.add("hidden");
                        console.log("ODPOWIEDŹ Z BACKENDU:", data);

                        if (data.success) {
                            this.summaryResult.classList.remove("hidden");
                            const summaryText = document.getElementById("summaryText");
                            if (summaryText) {
                                summaryText.innerHTML = data.data.map(file => `
                                    <div class="summary-block">
                                        <h3>${file.material_name}</h3>
                                        <p>${file.summary}</p>
                                    </div>
                                `).join("");
                            }
                        } else {
                            alert(data.error || "Wystąpił błąd podczas generowania streszczenia.");
                        }
                    })
                    .catch(error => {
                        this.loading.classList.add("hidden");
                        console.error("Błąd:", error);
                        alert("Błąd podczas generowania streszczenia.");
                    });
            });
        }
    }

    setupGenerateSummary() {
        if (this.generateButton) {
            this.generateButton.addEventListener("click", () => {
                const selectedFiles = Array.from(this.materialCheckboxes)
                    .filter(cb => cb.checked)
                    .map(cb => cb.value);

                if (selectedFiles.length === 0) {
                    alert("Wybierz co najmniej jeden plik.");
                    return;
                }

                this.loading.classList.remove("hidden");

                fetch("/generate", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({ selectedFiles })
                })
                    .then(response => response.json())
                    .then(data => {
                        this.loading.classList.add("hidden");
                        console.log("ODPOWIEDŹ Z BACKENDU:", data);

                        if (data.success) {
                            this.summaryResult.classList.remove("hidden");
                            this.summaryText.innerHTML = data.data.map(file => `
                                <div class="summary-block">
                                    <h3>${file.material_name}</h3>
                                    <p>${file.summary}</p>
                                </div>
                            `).join("");
                        } else {
                            alert(data.error || "Wystąpił błąd.");
                        }
                    })
                    .catch(error => {
                        this.loading.classList.add("hidden");
                        console.error("Błąd:", error);
                        alert("Błąd podczas generowania streszczenia.");
                    });
            });
        }
    }

    setupMaterialDelete() {
        const deleteButton = document.querySelector(".remove-item-btn");
        const deleteIDInput = document.getElementById("deleteID");
        const deleteTypeInput = document.getElementById("deleteType");
        const materialCheckboxes = document.querySelectorAll(".material-checkbox");
        const folderCheckboxes = document.querySelectorAll(".folder-checkbox");

        if (deleteButton) {
            deleteButton.addEventListener("click", (e) => {
                e.preventDefault();

                // Check for selected materials first
                const selectedMaterial = Array.from(materialCheckboxes).find(cb => cb.checked);
                if (selectedMaterial) {
                    deleteIDInput.value = selectedMaterial.value;
                    deleteTypeInput.value = 'material';
                    if (confirm('Czy na pewno chcesz usunąć ten materiał?')) {
                        deleteButton.closest('form').submit();
                    }
                    return;
                }

                // Then check for selected folders
                const selectedFolder = Array.from(folderCheckboxes).find(cb => cb.checked);
                if (selectedFolder) {
                    deleteIDInput.value = selectedFolder.value;
                    deleteTypeInput.value = 'folder';
                    if (confirm('Czy na pewno chcesz usunąć ten folder i całą jego zawartość?')) {
                        deleteButton.closest('form').submit();
                    }
                    return;
                }

                alert('Wybierz materiał lub folder do usunięcia.');
            });
        }
    }
}
