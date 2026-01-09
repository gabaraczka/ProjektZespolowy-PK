document.addEventListener("DOMContentLoaded", function () {
    const hamburger = document.getElementById("hamburger");
    const navMenu = document.getElementById("nav-menu");

    if (hamburger && navMenu) {
        hamburger.addEventListener("click", function () {
            navMenu.classList.toggle("active");
        });
    }

    const registerForm = document.querySelector(".register-form");
    const loginForm = document.querySelector(".login-form");

    const serverUrl = "./auth.php";

    if (registerForm) {
        registerForm.addEventListener("submit", function (e) {
            e.preventDefault();

            const formData = new FormData(registerForm);
            formData.append("action", "register");

            fetch(serverUrl, {
                method: "POST",
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => {
                console.log("HTTP Status:", response.status);
                console.log("Response headers:", Object.fromEntries(response.headers.entries()));
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.text().then(text => {
                    console.log("Raw response:", text);
                    return text;
                });
            })
            .then(text => {
                console.log("Processing response:", text);
                try {
                    const data = JSON.parse(text);
                    console.log("Parsed data:", data);
                    if (data.error) {
                        alert(data.error);
                    } else {
                        alert(data.success);
                        window.location.href = "login.html";
                    }
                } catch (e) {
                    console.error("JSON Parsing Error:", e, "Response text:", text);
                    alert("Błąd serwera: Niepoprawny JSON. Sprawdź konsolę.");
                }
            })
            .catch(error => {
                console.error("Network or parsing error:", error);
                console.error("Error details:", error.stack);
                alert("Błąd sieci. Spróbuj ponownie. Szczegóły w konsoli.");
            });
        });
    }

    if (loginForm) {
        loginForm.addEventListener("submit", function (e) {
            e.preventDefault();

            const formData = new FormData(loginForm);
            formData.append("action", "login");

            fetch(serverUrl, {
                method: "POST",
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => {
                console.log("HTTP Status:", response.status);
                console.log("Response headers:", Object.fromEntries(response.headers.entries()));
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.text().then(text => {
                    console.log("Raw response:", text);
                    return text;
                });
            })
            .then(text => {
                console.log("Processing response:", text);
                try {
                    const data = JSON.parse(text);
                    console.log("Parsed data:", data);
                    if (data.error) {
                        alert(data.error);
                    } else {
                        alert(data.success);
                        window.location.href = "home-page.php";
                    }
                } catch (e) {
                    console.error("JSON Parsing Error:", e, "Response text:", text);
                    alert("Błąd serwera: Niepoprawny JSON. Sprawdź konsolę.");
                }
            })
            .catch(error => {
                console.error("Network or parsing error:", error);
                console.error("Error details:", error.stack);
                alert("Błąd sieci. Spróbuj ponownie. Szczegóły w konsoli.");
            });
        });
    }
});
