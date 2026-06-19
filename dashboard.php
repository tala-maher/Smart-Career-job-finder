<?php
session_start();
include_once "db_connect.php";

if (!isset($_SESSION['user_role'])) {
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user_row = $stmt->fetch();
    $_SESSION['user_role'] = $user_row['role'] ?? 'job_seeker';
}
$user_role = strtolower(trim($_SESSION['user_role']));


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
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die("Database Connection Failed.");
}

$user_id   = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'job_seeker';


$nav_stmt = $conn->prepare("SELECT full_name FROM users WHERE id = :id");
$nav_stmt->execute(['id' => $user_id]);
$nav_user = $nav_stmt->fetch();


$stats = [];
$labels = [];
$data = [];

if ($user_role !== 'company') {
    $stats_stmt = $conn->prepare("
        SELECT j.category, COUNT(*) as rejection_count
        FROM user_feedback uf
        JOIN jobs j ON uf.job_id = j.id
        WHERE uf.user_id = :user_id AND uf.status = 'rejected'
        GROUP BY j.category
        ORDER BY rejection_count DESC
        LIMIT 5
    ");
    $stats_stmt->execute(['user_id' => $user_id]);
    $stats = $stats_stmt->fetchAll();
    $labels = array_column($stats, 'category');
    $data = array_column($stats, 'rejection_count');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Smart Career</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php include_once "navbar.php"; ?>

<main style="padding: 30px; max-width: 1200px; margin: 0 auto;">
    <h2>Welcome back, <?php echo htmlspecialchars($nav_user['full_name'] ?? 'User'); ?>!</h2>
    <p style="color: #666; margin-bottom: 30px;">Role: <strong style="color: #2b70c9;"><?php echo ucfirst($user_role); ?></strong></p>

    <?php if ($user_role === 'company'): ?>
        <section>
            <h3 style="margin-bottom: 20px;"><i class="fa-solid fa-layer-group"></i> Incoming Candidate Applications</h3>
            <?php
            $rec_stmt = $conn->prepare("SELECT a.match_explanation, u.full_name, j.title FROM applications a 
                                       JOIN users u ON a.user_id = u.id 
                                       JOIN jobs j ON a.job_id = j.id 
                                       WHERE j.company_id = :cid ORDER BY a.match_score DESC");
            $rec_stmt->execute(['cid' => $user_id]);
            $applications = $rec_stmt->fetchAll();
            ?>
            <?php if (count($applications) > 0): foreach ($applications as $app): ?>
                <div style="background: white; padding: 20px; margin-bottom: 15px; border-radius: 8px; border-left: 5px solid #10b981; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h4><?php echo htmlspecialchars($app['full_name']); ?></h4>
                    <p>Applied for: <strong><?php echo htmlspecialchars($app['title']); ?></strong></p>
                    <p style="font-size: 0.9rem; color: #444;"><em>AI Assessment: <?php echo htmlspecialchars($app['match_explanation']); ?></em></p>
                </div>
            <?php endforeach; else: ?>
                <p>No applications received yet.</p>
            <?php endif; ?>
        </section>

    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
            <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <h3><i class="fa-solid fa-chart-pie"></i> Your Rejection Analytics</h3>
                <?php if (!empty($stats)): ?>
                    <canvas id="rejectionChart" style="max-height: 250px;"></canvas>
                <?php else: ?>
                    <p style="color: #777;">Start applying and rejecting jobs to see your trends.</p>
                <?php endif; ?>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <h3>Activity Stats</h3>
                <?php
                $total_jobs = $conn->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
                $my_apps = $conn->prepare("SELECT COUNT(*) FROM applications WHERE user_id = :uid");
                $my_apps->execute(['uid' => $user_id]);
                ?>
                <p>Total Available Jobs: <?php echo $total_jobs; ?></p>
                <p>Your Total Applications: <?php echo $my_apps->fetchColumn(); ?></p>
            </div>
        </div>
    <?php endif; ?>
</main>

<script>
<?php if (!empty($stats)): ?>
new Chart(document.getElementById('rejectionChart').getContext('2d'), {
    type: 'pie',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            data: <?php echo json_encode($data); ?>,
            backgroundColor: ['#ef4444', '#f87171', '#fca5a5', '#fecaca', '#fee2e2']
        }]
    }
});
<?php endif; ?>
</script>

</body>
</html>