<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$host     = "localhost";
$dbname   = "smart_career_db";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die("Connection failed.");
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT jobs.* FROM jobs 
        INNER JOIN favorites ON jobs.id = favorites.job_id
        WHERE favorites.user_id = :user_id 
        ORDER BY favorites.id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$fav_jobs = $stmt->fetchAll();


$fav_count = count($fav_jobs);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorites - Smart Career</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include_once "navbar.php"; ?>

<section class="favorites-header">
    <h1>Your Favorite Jobs</h1>
    <p>Saved opportunities matched to your career goals.</p>
    <div class="fav-count">
        <i class="fa-solid fa-heart"></i>
        <?php echo $fav_count; ?> Saved Jobs
    </div>
</section>

<section class="favorites-container">
<?php foreach($fav_jobs as $job): ?>
    <div class="favorite-card">
        <div class="fav-top">
            <span class="match-score">Saved ❤️</span>
            <form action="remove_favorite.php" method="POST">
                <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                <button class="remove-btn">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </form>
        </div>

        <h2><?php echo htmlspecialchars($job['title']); ?></h2>
        <h4><?php echo htmlspecialchars($job['company']); ?></h4>
        <p><?php echo htmlspecialchars($job['required_skills']); ?></p>

        <div class="fav-info">
            <span><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($job['city']); ?></span>
            <span><i class="fa-solid fa-briefcase"></i> <?php echo htmlspecialchars($job['job_type']); ?></span>
        </div>

        <form action="apply_process.php" method="POST" style="margin-top: 15px;">
            <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
            <button type="submit" class="apply-btn" style="width: 100%; background-color: #28a745; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; font-weight: bold;">
                <i class="fa-solid fa-paper-plane"></i> Apply Now
            </button>
        </form>
    </div>
<?php endforeach; ?>
</section>

</body>
</html>