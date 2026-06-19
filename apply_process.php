<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
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

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['job_id'])) {
    $user_id = $_SESSION['user_id'];
    $job_id  = (int)$_POST['job_id'];

    try {
        // 1. Prevent duplicate job applications
        $check_stmt = $conn->prepare("SELECT id FROM applications WHERE user_id = :user_id AND job_id = :job_id");
        $check_stmt->execute(['user_id' => $user_id, 'job_id' => $job_id]);
        
        if ($check_stmt->fetch()) {
            echo "<script>alert('You have already applied for this job!'); window.location.href='jobs.php';</script>";
            exit();
        } else {
            // 2. Insert application record
            $insert_sql  = "INSERT INTO applications (user_id, job_id) VALUES (:user_id, :job_id)";
            $insert_stmt = $conn->prepare($insert_sql);
            $execution   = $insert_stmt->execute(['user_id' => $user_id, 'job_id' => $job_id]);

            if ($execution) {
                $new_application_id = $conn->lastInsertId();

                // 3. Trigger local match engine asynchronous call via cURL
                $protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
                $host_path = $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
                $ai_trigger_url = $protocol . $host_path . "/match_engine.php?trigger_app_id=" . $new_application_id;

                $ch = curl_init($ai_trigger_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
                $ai_engine_response = curl_exec($ch);
                curl_close($ch);

                echo "<script>alert('Application submitted successfully! AI analysis is being processed.'); window.location.href='jobs.php';</script>";
                exit();
            } else {
                echo "Database insertion failed.";
            }
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    header("Location: jobs.php");
    exit();
}

ob_end_flush();
?>