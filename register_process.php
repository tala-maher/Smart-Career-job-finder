<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

$host     = "localhost";
$dbname   = "smart_career_db";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false
    ]);
} catch (PDOException $e) {
    die("Connection failed.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $full_name = trim($_POST['name']);
    $career    = trim($_POST['career']);
    $email     = trim($_POST['email']);
    $pass      = $_POST['password'];
    $confirm_p = $_POST['confirm_password'];
    $skills    = strtolower(trim($_POST['skills']));
    $location  = trim($_POST['location']);
    
    
    $role      = isset($_POST['role']) ? strtolower(trim($_POST['role'])) : 'job_seeker';

    if ($pass !== $confirm_p) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit();
    }

    
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $check_stmt->execute(['email' => $email]);
    if ($check_stmt->fetch()) {
        echo "<script>alert('Email already registered!'); window.history.back();</script>";
        exit();
    }

    $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

   
    $cv_path = null;
    if (isset($_FILES['cv']) && $_FILES['cv']['error'] == 0) {
        $cv_name = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['cv']['name']);
        $cv_path = "uploads/cvs/" . $cv_name;
        move_uploaded_file($_FILES['cv']['tmp_name'], $cv_path);
    }


    $profile_pic_path = "uploads/profile_pics/default-user.png";
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $pic_name = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['profile_pic']['name']);
        $profile_pic_path = "uploads/profile_pics/" . $pic_name;
        move_uploaded_file($_FILES['profile_pic']['tmp_name'], $profile_pic_path);
    }


    $sql = "INSERT INTO users (full_name, email, password, role, career_title, skills, location, cv_path, profile_pic)
            VALUES (:full_name, :email, :password, :role, :career_title, :skills, :location, :cv_path, :profile_pic)";

    $stmt = $conn->prepare($sql);
    $execution = $stmt->execute([
        'full_name'    => $full_name,
        'email'        => $email,
        'password'     => $hashed_password,
        'role'         => $role, 
        'career_title' => $career,
        'skills'       => $skills,
        'location'     => $location,
        'cv_path'      => $cv_path,
        'profile_pic'  => $profile_pic_path
    ]);


    if ($role === 'recruiter') { $role = 'company'; }


    if ($execution) {
        header("Location: login.php?registration=success");
        exit();
    } else {
        echo "Registration failed.";
    }
}
?>