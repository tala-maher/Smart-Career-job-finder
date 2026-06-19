<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include_once "db_connect.php";

$id = $_SESSION['user_id'];

$user_stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$user_stmt->execute(['id' => $id]);
$company = $user_stmt->fetch();

$current_user_role = $company ? strtolower(trim($company['role'])) : '';

if ($current_user_role !== 'company' && $current_user_role !== 'recruiter') {
    header("Location: index.php");
    exit();
}

$stmt1 = $conn->prepare("SELECT COUNT(*) FROM jobs WHERE company_id = :id");
$stmt1->execute(['id' => $id]);
$jobs_posted = $stmt1->fetchColumn();

$stmt2 = $conn->prepare("SELECT COUNT(*) FROM applications WHERE job_id IN (SELECT id FROM jobs WHERE company_id = :id)");
$stmt2->execute(['id' => $id]);
$total_received = $stmt2->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Company Profile</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include_once "navbar.php"; ?>

<main style="padding: 40px; max-width: 1000px; margin: auto;">
    <div class="profile-card" style="background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h1><?php echo htmlspecialchars($company['full_name']); ?></h1>
        <p>Email: <?php echo htmlspecialchars($company['email']); ?></p>
        <a href="edit_profile.php" style="background: #000; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none;">Edit Profile</a>
    </div>

    <section style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-top: 30px;">
        <div style="background: #fff; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <h3><?php echo $jobs_posted; ?></h3>
            <p>Total Jobs Posted</p>
        </div>
        <div style="background: #fff; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <h3><?php echo $total_received; ?></h3>
            <p>Applications Received</p>
        </div>
    </section>
</main>
</body>
</html>