<?php
session_start();
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

require_once 'db_connect.php';
$check_stmt = $conn->prepare("SELECT role FROM users WHERE id = :id");
$check_stmt->execute(['id' => $_SESSION['user_id']]);
$current_user_role = strtolower(trim($check_stmt->fetchColumn() ?: ''));

if ($current_user_role !== 'company' && $current_user_role !== 'recruiter') {
    header("Location: index.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM jobs WHERE company_id = :cid ORDER BY id DESC");
$stmt->execute(['cid' => $_SESSION['user_id']]);
$my_jobs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Jobs | Smart Career</title>
    <link rel="stylesheet" href="style.css">
    </head>
<body>
   <?php include_once "navbar.php"; ?>

    <main style="max-width: 1000px; margin: 40px auto; padding: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2>My Active Job Postings</h2>
            <a href="post_job.php" style="background: #2b70c9; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">+ Post New Job</a>
        </div>

        <table style="width: 100%; margin-top: 20px; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8fafc; text-align: left;">
                    <th style="padding: 12px;">Job Title</th>
                    <th style="padding: 12px;">Applicants</th>
                    <th style="padding: 12px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($my_jobs as $job): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px;"><?php echo htmlspecialchars($job['title']); ?></td>
                    <td style="padding: 12px;">
                        <?php 
                            $count = $conn->prepare("SELECT COUNT(*) FROM applications WHERE job_id = ?");
                            $count->execute([$job['id']]);
                            echo $count->fetchColumn();
                        ?>
                    </td>
                    <td style="padding: 12px;">
                        <a href="view_applicants.php?job_id=<?php echo $job['id']; ?>" style="color: #2b70c9;">View Applicants</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>