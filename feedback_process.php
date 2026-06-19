<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_POST['job_id'])) {
    header("Location: jobs.php");
    exit();
}

$host = "localhost"; $dbname = "smart_career_db"; $username = "root"; $password = "";
$conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

$user_id = $_SESSION['user_id'];
$job_id  = $_POST['job_id'];
// 'rejected' or 'interested'
$status  = $_POST['status']; 


$stmt = $conn->prepare("INSERT INTO user_feedback (user_id, job_id, status) VALUES (:uid, :jid, :stat)");
$stmt->execute(['uid' => $user_id, 'jid' => $job_id, 'stat' => $status]);

header("Location: jobs.php?msg=feedback_recorded");
exit();
?>