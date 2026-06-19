<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Career</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include_once "navbar.php"; ?>
    

<section class="login-page">
    <div class="login-card">
        <!-- LEFT SIDE -->
        <div class="login-left">
            <h2>Welcome Back</h2>
            <p>Login to continue your journey and discover the best jobs that match your future.</p>
            <img src="images/person.png" alt="illustration">
        </div>

        <!-- RIGHT SIDE -->
        <div class="login-right">
            <h3>Login To Account</h3>

            <form action="login_process.php" method="POST">
                <div class="input-wrap">
                    <i class="fa-regular fa-envelope"></i>
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>

                <div class="input-wrap">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="login-btn">Login Now</button>
            </form>

            <p class="bottom-text">
                Don't have an account? <a href="signup.php">Create One</a>
            </p>
        </div>
    </div>
</section>

</body>
</html>