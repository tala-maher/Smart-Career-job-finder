<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_role = 'job_seeker'; 
if (isset($_SESSION['user_id'])) {
    include_once "db_connect.php";
    
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user_data = $stmt->fetch();
    if ($user_data) {
        $user_role = strtolower(trim($user_data['role']));
    }
}
?>
<header class="navbar">
    <div class="brand">
        <div class="logo-circle"><img src="images/logo.png" alt="Logo" style="width:40px;"></div>
        <div class="brand-text">
            <h1>Smart Career</h1>
            <span>Next-Gen AI Platform</span>
        </div>
    </div>

    <nav class="nav-links" style="display: flex; gap: 15px; align-items: center;">
    <a href="index.php" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; pointer-events: auto;"><i class="fa-solid fa-house" style="pointer-events: none;"></i> Home</a>
    
    <?php if (isset($_SESSION['user_id'])): ?>
        <?php if ($user_role === 'company' || $user_role === 'recruiter'): ?>
            <a href="manage_jobs.php" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; pointer-events: auto;">
                <i class="fa-solid fa-briefcase" style="pointer-events: none;"></i> Manage Jobs
            </a>
            <a href="applicants.php" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; pointer-events: auto;">
                <i class="fa-solid fa-users" style="pointer-events: none;"></i> Applicants
            </a>
            <a href="profile_company.php" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; pointer-events: auto;">
                <i class="fa-solid fa-user" style="pointer-events: none;"></i> My Profile
            </a>
        <?php else: ?>
            <a href="jobs.php" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; pointer-events: auto;">
                <i class="fa-solid fa-magnifying-glass" style="pointer-events: none;"></i> Find Jobs
            </a>
            <a href="favorites.php" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; pointer-events: auto;">
                <i class="fa-solid fa-heart" style="pointer-events: none;"></i> Favorites
            </a>
            <a href="profile.php" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; pointer-events: auto;">
                <i class="fa-solid fa-user" style="pointer-events: none;"></i> My Profile
            </a>
        <?php endif; ?>
        
        <a href="logout.php" class="logout-btn" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; pointer-events: auto;">
            <i class="fa-solid fa-arrow-right-from-bracket" style="pointer-events: none;"></i> Logout
        </a>
    <?php else: ?>
        <a href="login.php" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; pointer-events: auto;">
            <i class="fa-solid fa-right-to-bracket" style="pointer-events: none;"></i> Login
        </a>
        <a href="signup.php" style="background: #10b981; color: white !important; padding: 10px 22px; border-radius: 30px; font-weight: bold; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; pointer-events: auto;">
            <i class="fa-solid fa-user-plus" style="pointer-events: none;"></i> Join Now
        </a>
    <?php endif; ?>
</nav>
</header>