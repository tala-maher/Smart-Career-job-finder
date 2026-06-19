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

$stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = :user_id AND job_id = :job_id");
$stmt->execute(['user_id' => $user_id, 'job_id' => $job_id]);

header("Location: favorites.php");
exit();
?>