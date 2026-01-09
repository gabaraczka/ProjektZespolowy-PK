<!DOCTYPE html>
<?php
    $isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    $email = $isLoggedIn ? $_SESSION['email'] ?? '' : '';
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - StudyBuddy</title>
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
                    <span>Welcome, <?php echo htmlspecialchars($email); ?></span>
                    <a href="/logout" class="login-btn">Logout</a>
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
            <h2>Create Your Account</h2>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($_SESSION['error']); ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="/register" method="POST" onsubmit="return validateForm()">
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
                        <input type="password" id="password" name="password" placeholder="Enter your password" required 
                               pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$" 
                               title="Password must be at least 8 characters long and contain at least one letter and one number">
                    </div>
                </div>

                <div class="input-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="ikony">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                    </div>
                </div>

                <button type="submit" class="button">Register</button>
            </form>

            <p>
                Already have an account? <a href="/login" class="login-btn">Login here</a>
            </p>
        </div>
    </div>

    <script>
        function validateForm() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const email = document.getElementById('email').value;

            if (password !== confirmPassword) {
                alert('Hasła nie pasują do siebie!');
                return false;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Podaj prawidłowy adres email!');
                return false;
            }
            const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/;
            if (!passwordRegex.test(password)) {
                alert('Hasło musi mieć co najmniej 8 znaków i zawierać przynajmniej jedną literę i jedną cyfrę!');
                return false;
            }

            return true;
        }
    </script>
</body>
</html> 