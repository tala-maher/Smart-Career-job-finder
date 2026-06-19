<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


include "db_connect.php";

$id = $_SESSION['user_id'];


$user_stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$user_stmt->execute(['id' => $id]);
$user = $user_stmt->fetch();

$user_role = strtolower(trim($user['role'] ?? ''));

if ($user_role === 'company') {
    header("Location: profile_company.php");
    exit();
}

// Statistics queries for job seekers
$apply_stmt = $conn->prepare("SELECT COUNT(*) as total_apply FROM applications WHERE user_id = :id");
$apply_stmt->execute(['id' => $id]);
$total_applied = $apply_stmt->fetch()['total_apply'];

$fav_stmt = $conn->prepare("SELECT COUNT(*) as total_fav FROM favorites WHERE user_id = :id");
$fav_stmt->execute(['id' => $id]);
$total_favorites = $fav_stmt->fetch()['total_fav'];


$ai_advice = "Keep building projects! Your portfolio is your best asset.";
if (!empty($user['skills'])) {
    $skills = strtolower($user['skills']);
    if (strpos($skills, 'php') !== false) {
        $ai_advice = "Your Backend skills are strong. To level up, explore Microservices, Docker, or AWS cloud deployment.";
    } elseif (strpos($skills, 'react') !== false || strpos($skills, 'js') !== false) {
        $ai_advice = "Great Frontend foundation! Advanced knowledge of Next.js or TypeScript will significantly boost your profile.";
    } elseif (strpos($skills, 'python') !== false) {
        $ai_advice = "Your data skills are in high demand. Focus on mastering Machine Learning libraries or Data Pipelines.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Smart Career</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include_once "navbar.php"; ?>

<main class="profile-page-wrapper" style="padding: 40px; max-width: 1000px; margin: auto;">
    <div class="profile-card" style="background: #fff; padding: 30px; border-radius: 12px; display: flex; gap: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <div class="avatar-section">
            <img src="<?php echo !empty($user['profile_pic']) ? $user['profile_pic'] : 'images/default-avatar.png'; ?>" alt="Profile" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #eee;">
            <br><a href="edit_profile.php" style="color: #2b70c9; font-size: 0.9rem; margin-top: 10px; display: block;">Edit Photo</a>
        </div>

        <div class="profile-info">
            <h1><?php echo htmlspecialchars($user['full_name']); ?></h1>
            <p style="color: #2b70c9; font-weight: bold; font-size: 1.1rem;"><?php echo htmlspecialchars($user['career_title'] ?? 'Professional'); ?></p>
            <div style="margin: 15px 0; color: #666;">
                <p><i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($user['location'] ?? 'Location not set'); ?></p>
            </div>
            <div class="skills-section">
                <strong>Technical Stack:</strong>
                <div class="skills-tags" style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px;">
                    <?php if(!empty($user['skills'])): foreach(explode(',', $user['skills']) as $skill): ?>
                        <span style="background: #eef2ff; color: #4f46e5; padding: 5px 12px; border-radius: 20px; font-size: 0.85rem;"><?php echo trim($skill); ?></span>
                    <?php endforeach; else: echo " No skills listed."; endif; ?>
                </div>
            </div>
            <a href="edit_profile.php" style="display:inline-block; margin-top:20px; padding: 10px 25px; background:#000; color:#fff; border-radius:8px; text-decoration:none; font-weight:bold;">Edit Profile</a>
        </div>
    </div>

    <section class="stats-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 30px;">
        <div class="stat-card" style="background:#fff; padding: 20px; text-align:center; border-radius:8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);"><h3><?php echo $total_applied; ?></h3><p>Applications Sent</p></div>
        <div class="stat-card" style="background:#fff; padding: 20px; text-align:center; border-radius:8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);"><h3><?php echo $total_favorites; ?></h3><p>Saved Favorites</p></div>
        <div class="stat-card" style="background:#fff; padding: 20px; text-align:center; border-radius:8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);"><h3>Active</h3><p>Account Status</p></div>
    </section>

    <section style="margin-top: 40px; padding: 30px; background: #0f172a; color: white; border-radius: 20px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);">
        <h2 style="margin-top: 0; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-wand-magic-sparkles"></i> AI Career Roadmap
        </h2>
        <p style="margin-bottom: 0; line-height: 1.6; font-size: 1.1rem; opacity: 0.9;">
            <?php echo $ai_advice; ?>
        </p>
    </section>
</main>
</body>
</html>