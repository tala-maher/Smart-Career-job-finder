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
$job_id  = (int)$_POST['job_id'];

// 1. Check if the job is already in favorites
$check_stmt = $conn->prepare("SELECT id FROM favorites WHERE user_id = :user_id AND job_id = :job_id");
$check_stmt->execute(['user_id' => $user_id, 'job_id' => $job_id]);

if (!$check_stmt->fetch()) {
    // 2. Insert into favorites if not exists
    $insert_stmt = $conn->prepare("INSERT INTO favorites (user_id, job_id) VALUES (:user_id, :job_id)");
    $insert_stmt->execute(['user_id' => $user_id, 'job_id' => $job_id]);
}

header("Location: jobs.php");
exit();
?>