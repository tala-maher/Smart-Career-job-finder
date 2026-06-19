<?php
session_start();

require_once 'db_connect.php';
$user = null;
$user_role = 'job_seeker'; 

if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();
    if ($user) {
        $user_role = strtolower(trim($user['role']));
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Career - Next-Gen AI Recruitment Ecosystem</title>

    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include_once "navbar.php"; ?>

<section class="hero-page-wrapper">
    <div class="hero-card">
        <span style="background: #e0f2fe; color: #0369a1; padding: 6px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; display: inline-block;">
            <i class="fa-solid fa-microchip"></i> Powered by OpenAI GPT-4o Core
        </span>

        <h2>Intelligent Semantic <br><span style="color: #2b70c9;">Recruitment Architecture</span></h2>

        <p>
            Smart Career is an enterprise-grade job acquisition platform driven by advanced AI. By evaluating localized talent nodes against dynamic requirement matrices, our system eliminates screening biases, automatically maps skill gaps, and matches engineers with their optimal technical environments.
        </p>

        <div class="btn-group">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php" class="btn-landing btn-landing-primary">
                    Enter Control Dashboard <i class="fa-solid fa-arrow-right"></i>
                </a>
            <?php else: ?>
                <a href="signup.php" class="btn-landing btn-landing-primary">
                    Get Started <i class="fa-solid fa-rocket"></i>
                </a>
                <a href="login.php" class="btn-landing btn-landing-secondary">
                    Portal Login <i class="fa-solid fa-right-to-bracket"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="features">
    <h2>Platform Core Capabilities</h2>

    <div class="features-container">
        <div class="feature-box" style="border-top: 4px solid #2b70c9;">
            <i class="fa-solid fa-file-lines" style="color: #2b70c9;"></i>
            <h3>Semantic Parsing</h3>
            <p>Automated vector extraction of technical proficiencies directly from structured candidate CV nodes.</p>
        </div>

        <div class="feature-box" style="border-top: 4px solid #10b981;">
            <i class="fa-solid fa-chart-line" style="color: #10b981;"></i>
            <h3>AI Gap Analysis</h3>
            <p>Leverages large language models to evaluate profiles and isolate specific educational and skill discrepancies.</p>
        </div>

        <div class="feature-box" style="border-top: 4px solid #f43f5e;">
            <i class="fa-solid fa-shield-halved" style="color: #f43f5e;"></i>
            <h3>Secured Framework</h3>
            <p>Built with parameterized PDO boundaries and segregated environment logic adhering to OWASP protection rules.</p>
        </div>
    </div>
</section>

<footer>
    <p>&copy; 2026 Smart Career Platform. Engineered for Intelligent Corporate Matching.</p>
</footer>

</body>
</html>