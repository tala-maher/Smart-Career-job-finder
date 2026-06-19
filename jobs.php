<?php
session_start();

if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

$user_role = $_SESSION['user_role'] ?? 'job_seeker';

if ($user_role === 'company') {
    header("Location: manage_jobs.php"); 
    exit();
}

include "env_loader.php";

$host = getenv('DB_HOST') ?: "localhost";
$dbname = getenv('DB_NAME') ?: "smart_career_db";
$username = getenv('DB_USER') ?: "root";
$password = getenv('DB_PASS') ?: "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) { die("Connection failed."); }


$rejected_stmt = $conn->prepare("SELECT job_id FROM user_feedback WHERE user_id = :uid AND status = 'rejected'");
$rejected_stmt->execute(['uid' => $_SESSION['user_id']]);
$rejected_job_ids = $rejected_stmt->fetchAll(PDO::FETCH_COLUMN);

// 2. Fetch opportunities
$searchTerm = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = trim($_GET['search']);
    $sql = "SELECT * FROM jobs WHERE (title LIKE :search OR company LIKE :search OR category LIKE :search OR city LIKE :search) ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['search' => "%$searchTerm%"]);
} else {
    $sql = "SELECT * FROM jobs ORDER BY id DESC";
    $stmt = $conn->query($sql);
}
$jobs = $stmt->fetchAll();

// 3. Load User Profile
$user_stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$user_stmt->execute(['id' => $_SESSION['user_id']]);
$user = $user_stmt->fetch();

// 4. Logic: Learning System (Rejection count)
$feedback_stmt = $conn->prepare("SELECT COUNT(*) FROM user_feedback WHERE user_id = :uid AND status = 'rejected'");
$feedback_stmt->execute(['uid' => $_SESSION['user_id']]);
$reject_count = $feedback_stmt->fetchColumn();

$all_jobs_with_scores = [];
foreach ($jobs as $job) {
   
    if (in_array($job['id'], $rejected_job_ids)) continue;

    $user_skills = array_map('trim', explode(",", strtolower($user['skills'] ?? '')));
    $job_skills  = array_map('trim', explode(",", strtolower($job['required_skills'])));
    $intersect = count(array_intersect($user_skills, $job_skills));
    $total_skills = count($job_skills);
    
    $skills_score = ($total_skills > 0) ? ($intersect / $total_skills) * 80 : 0;
    $location_score = (!empty(trim($user['location'])) && trim($user['location']) == trim($job['city'])) ? 20 : 0;
    
    $job['match_score'] = min(round($skills_score + $location_score), 100);
    $all_jobs_with_scores[] = $job;
}
usort($all_jobs_with_scores, function($a, $b) { return $b['match_score'] <=> $a['match_score']; });
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Jobs - Smart Career</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include_once "navbar.php"; ?>

<section class="jobs-header">
    <h1>Recommended Jobs</h1>
    <p>AI-powered matching based on your profile.</p>
</section>

<?php if ($reject_count >= 3): ?>
    <div class='ai-alert' style='background: #fee2e2; padding: 15px; border-radius: 10px; color: #991b1b; text-align: center; margin: 20px auto; max-width: 800px;'>
        <i class='fa-solid fa-lightbulb'></i> <strong>Pattern Detected:</strong> We've identified a persistent rejection trend. Would you like to recalibrate your career preferences?
    </div>
<?php endif; ?>

<form action="jobs.php" method="GET" class="search-box">
    <input type="text" name="search" placeholder="Search jobs..." value="<?php echo htmlspecialchars($searchTerm); ?>">
    <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
</form>

<section class="jobs-container">
<?php if(empty($all_jobs_with_scores)): ?>
    <p style="text-align: center; margin-top: 50px; color: #64748b;">No matching jobs found at the moment.</p>
<?php else: ?>
    <?php foreach($all_jobs_with_scores as $job): ?>
        <div class="job-card">
            <div class="top-line"><span class="match"><?php echo $job['match_score']; ?>% Match</span></div>
            <h2><?php echo htmlspecialchars($job['title']); ?></h2>
            <h4><?php echo htmlspecialchars($job['company']); ?></h4>
            
            <div class="ai-insight-box">
                <p><i class="fa-solid fa-chart-line"></i> <strong>AI Strategic Insight:</strong> 
                <?php 
                    if ($job['match_score'] >= 80) echo "Optimal Alignment: Your profile shows high synergy. Apply now.";
                    elseif ($job['match_score'] >= 50) echo "Strategic Recommendation: Prioritize developing " . htmlspecialchars($job['required_skills']) . ".";
                    else echo "Market Intelligence: Focus on " . htmlspecialchars($job['category']) . " to align with high-demand trends.";
                ?>
                </p>
            </div>

            <div class="job-info">
                <span><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($job['city']); ?></span>
                <span><i class="fa-solid fa-briefcase"></i> <?php echo htmlspecialchars($job['job_type']); ?></span>
            </div>
            
            <?php if (isset($_SESSION['user_role']) && strtolower(trim($_SESSION['user_role'])) !== 'company'): ?>
                <div class="actions-wrapper" style="margin-top: 20px; border-top: 1px solid #f1f5f9; padding-top: 15px;">
                    <div class="action-buttons" style="display: flex; gap: 10px; margin-bottom: 10px;">
                        <form action="add_favorite.php" method="POST">
                            <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                            <button type="submit" class="save-btn">❤️ Save</button>
                        </form>
                        <form action="apply_process.php" method="POST" style="flex: 1;">
                            <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                            <button type="submit" class="apply-now-btn">Apply Now</button>
                        </form>
                    </div>
                    <form action="feedback_process.php" method="POST" style="text-align: center;">
                        <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                        <input type="hidden" name="status" value="rejected">
                        <button type="submit" style="background:none; border:none; color:#ef4444; font-size: 0.8rem; cursor:pointer; text-decoration: underline;">Not Interested</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
</section>

</body>
</html>