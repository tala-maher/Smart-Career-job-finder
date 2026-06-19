<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include "env_loader.php";
$host     = getenv('DB_HOST') ?: "localhost";
$dbname   = getenv('DB_NAME') ?: "smart_career_db";
$username = getenv('DB_USER') ?: "root";
$password = getenv('DB_PASS') ?: "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Connection failed.");
}

$id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile - Smart Career</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include_once "navbar.php"; ?>

<section class="signup-page"> 
    <div class="signup-card">
        <h2>Edit Your Information</h2>
        <p>Update your profile, skills, and location.</p>

        <form action="update_profile.php" method="POST" enctype="multipart/form-data">
            <div class="grid-form">
                <div class="input-box">
                    <label>Full Name</label>
                    <div class="input-wrap">
                        <i class="fa-regular fa-user"></i>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="input-box">
                    <label>Skills (Comma separated)</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-code"></i>
                        <input type="text" name="skills" value="<?php echo htmlspecialchars($user['skills'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="input-box">
                    <label>Update Profile Picture</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-image"></i>
                        <input type="file" name="profile_pic" accept="image/*">
                    </div>
                </div>

                <div class="input-box">
                    <label>Update CV (PDF/Doc)</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-file-pdf"></i>
                        <input type="file" name="cv" accept=".pdf,.doc,.docx">
                    </div>
                </div>

                <div class="input-box full-width">
                    <label>Location</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-location-dot"></i>
                        <input type="text" name="location" value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>" required>
                    </div>
                </div>
            </div>

            <div class="button-container">
                <button type="submit" class="find-btn">
                    <span>SAVE CHANGES</span>
                </button>
            </div>
        </form>
    </div>
</section>

</body>
</html>