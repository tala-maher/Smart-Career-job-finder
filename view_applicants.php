<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'company') { 
    header("Location: login.php"); 
    exit(); 
}

if (isset($_POST['update_status'])) {
    $stmt = $conn->prepare("UPDATE applications SET status = :status WHERE id = :app_id AND job_id IN (SELECT id FROM jobs WHERE company_id = :cid)");
    $stmt->execute(['status' => $_POST['status'], 'app_id' => $_POST['app_id'], 'cid' => $_SESSION['user_id']]);
}

$job_id = $_GET['job_id'] ?? 0;

$stmt = $conn->prepare("SELECT a.*, u.full_name, u.email, u.cv_path 
                        FROM applications a 
                        JOIN users u ON a.user_id = u.id 
                        JOIN jobs j ON a.job_id = j.id 
                        WHERE a.job_id = :jid AND j.company_id = :cid
                        ORDER BY a.match_score DESC");
$stmt->execute(['jid' => $job_id, 'cid' => $_SESSION['user_id']]);
$applicants = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Applicants | Smart Career</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include_once "navbar.php"; ?>

<main style="max-width: 1000px; margin: 40px auto; padding: 20px;">
    <div style="margin-bottom: 20px;">
        <a href="manage_jobs.php" style="color: #2b70c9; text-decoration: none; font-weight: bold;">
            <i class="fa-solid fa-arrow-left"></i> Back to Active Jobs
        </a>
    </div>

    <h2 style="margin-bottom: 20px;"><i class="fa-solid fa-user-tie"></i> Applicants for this Position</h2>
    <p style="color: #64748b; margin-bottom: 30px;">Review candidates sorted by their AI profile compatibility score.</p>
    
    <?php if (empty($applicants)): ?>
        <div style="text-align: center; padding: 40px; background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <p style="color: #64748b;">No applications received for this specific job yet.</p>
        </div>
    <?php else: ?>
        <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <thead>
                <tr style="background: #f8fafc; text-align: left; border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 15px;">Candidate Info</th>
                    <th style="padding: 15px;">AI Match Score</th>
                    <th style="padding: 15px;">Documents</th>
                    <th style="padding: 15px; text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applicants as $app): ?>
                <tr style="border-bottom: 1px solid #edf2f7;">
                    <td style="padding: 15px;">
                        <strong><?php echo htmlspecialchars($app['full_name']); ?></strong><br>
                        <small style="color: #64748b;"><?php echo htmlspecialchars($app['email']); ?></small><br>
                        <span style="font-size: 0.85rem; display: inline-block; margin-top: 5px; color: #475569; background: #f1f5f9; padding: 2px 8px; border-radius: 4px;">
                            Current Status: <?php echo ucfirst($app['status']); ?>
                        </span>
                    </td>
                    <td style="padding: 15px;">
                        <span style="background: <?php echo $app['match_score'] >= 75 ? '#dcfce7' : '#fef9c3'; ?>; color: <?php echo $app['match_score'] >= 75 ? '#15803d' : '#a16207'; ?>; padding: 4px 12px; border-radius: 20px; font-weight: bold; font-size: 0.85rem;">
                            <?php echo $app['match_score']; ?>% Match
                        </span>
                    </td>
                    <td style="padding: 15px;">
                        <?php if (!empty($app['cv_path'])): ?>
                            <a href="<?php echo htmlspecialchars($app['cv_path']); ?>" target="_blank" style="color: #10b981; text-decoration: none; font-weight: 500;">
                                <i class="fa-solid fa-file-pdf"></i> View CV
                            </a>
                        <?php else: ?>
                            <span style="color: #94a3b8;">No CV uploaded</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 15px; text-align: right;">
                        <?php if ($app['status'] == 'pending'): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="app_id" value="<?php echo $app['id']; ?>">
                                <button name="status" value="accepted" type="submit" style="background: #10b981; color: white; border: none; padding: 6px 14px; border-radius: 6px; cursor: pointer; font-weight: bold; margin-right: 5px;">Accept</button>
                                <button name="status" value="rejected" type="submit" style="background: #ef4444; color: white; border: none; padding: 6px 14px; border-radius: 6px; cursor: pointer; font-weight: bold;">Reject</button>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                        <?php else: ?>
                            <span style="font-weight: bold; color: <?php echo ($app['status'] == 'accepted') ? '#10b981' : '#ef4444'; ?>; padding: 6px 12px; border-radius: 6px; border: 1px solid;">
                                <?php echo strtoupper($app['status']); ?>
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
</body>
</html>