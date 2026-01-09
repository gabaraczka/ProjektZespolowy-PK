<!DOCTYPE html>
<?php
    $isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    $email = $isLoggedIn ? $_SESSION['email'] ?? '' : '';
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - StudyBuddy</title>
    <link rel="stylesheet" href="/assets/css/navbar.css">
    <link rel="stylesheet" href="/assets/css/styles-log-reg.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="/assets/js/hamburger.js" defer></script>
</head>
<body>
<div class="navbar">
        <div class="nav-logo">
            <img src="/assets/images/logo.png" alt="Study Buddy">
        </div>
        <div class="nav-menu" id="nav-menu">
            <div class="nav-links">
                <a href="/" class="active">Home</a>
                <a href="/generate">Create Summary</a>
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


    <div class="container">
        <div class="login-box">
            <h1>StudyBuddy</h1>
            <h2>Login to Your Account</h2>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($_SESSION['error']); ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($_SESSION['success']); ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <form action="/login" method="POST">
                <div class="input-group">
                    <label for="email">Email</label>
                    <div class="ikony">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <div class="ikony">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>

                <button type="submit" class="button">Login</button>
            </form>

            <p>
                Don't have an account? <a href="/register" class="signup-btn">Sign up</a>
            </p>
        </div>
    </div>

    <script>
        function validateForm() {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            if (!email || !password) {
                alert('Wypełnij wszystkie pola!');
                return false;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Podaj prawidłowy adres email!');
                return false;
            }

            return true;
        }
    </script>
</body>
</html> 