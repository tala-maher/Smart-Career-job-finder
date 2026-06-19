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

$company_id = $_SESSION['user_id'];

$query = "SELECT a.id AS app_id, a.status, a.match_score, u.full_name, u.email, u.cv_path, j.title AS job_title 
          FROM applications a 
          JOIN users u ON a.user_id = u.id 
          JOIN jobs j ON a.job_id = j.id 
          WHERE j.company_id = :cid 
          ORDER BY a.match_score DESC";

$stmt = $conn->prepare($query);
$stmt->execute(['cid' => $company_id]);
$applicants = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Applicants | Smart Career</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include_once "navbar.php"; ?>

<main style="max-width: 1200px; margin: 40px auto; padding: 20px;">
    <h2 style="margin-bottom: 20px;"><i class="fa-solid fa-users-viewfinder"></i> Incoming Candidates Applications</h2>
    <p style="color: #64748b; margin-bottom: 30px;">Review and manage all candidate profiles matching your active job posts.</p>

    <?php if (empty($applicants)): ?>
        <div style="text-align: center; padding: 40px; background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <p style="color: #64748b; font-size: 1.1rem;">No applications received yet for any of your job postings.</p>
        </div>
    <?php else: ?>
        <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <thead>
                <tr style="background: #f8fafc; text-align: left; border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 15px;">Candidate Name</th>
                    <th style="padding: 15px;">Target Job</th>
                    <th style="padding: 15px;">AI Match Score</th>
                    <th style="padding: 15px;">Documents</th>
                    <th style="padding: 15px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applicants as $app): ?>
                <tr style="border-bottom: 1px solid #edf2f7; transition: background 0.2s; hover:background: #f8fafc;">
                    <td style="padding: 15px;">
                        <strong><?php echo htmlspecialchars($app['full_name']); ?></strong><br>
                        <span style="font-size: 0.85rem; color: #64748b;"><?php echo htmlspecialchars($app['email']); ?></span>
                    </td>
                    <td style="padding: 15px; color: #334155; font-weight: 500;">
                        <?php echo htmlspecialchars($app['job_title']); ?>
                    </td>
                    <td style="padding: 15px;">
                        <span style="background: <?php echo $app['match_score'] >= 75 ? '#dcfce7' : '#fef9c3'; ?>; color: <?php echo $app['match_score'] >= 75 ? '#15803d' : '#a16207'; ?>; padding: 4px 10px; border-radius: 20px; font-weight: bold; font-size: 0.85rem;">
                            <?php echo $app['match_score']; ?>% Match
                        </span>
                    </td>
                    <td style="padding: 15px;">
                        <?php if (!empty($app['cv_path'])): ?>
                            <a href="<?php echo htmlspecialchars($app['cv_path']); ?>" target="_blank" style="color: #2b70c9; text-decoration: none; font-weight: 500;"><i class="fa-solid fa-file-pdf"></i> View CV</a>
                        <?php else: ?>
                            <span style="color: #94a3b8;">No CV Uploaded</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 15px;">
                        <a href="view_applicants.php?job_id=<?php echo $app['app_id']; ?>" style="background: #0f172a; color: white; padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: bold;"><i class="fa-solid fa-eye"></i> Process</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>

</body>
</html>