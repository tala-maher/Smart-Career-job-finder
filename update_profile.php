<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    
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

    $id       = $_SESSION['user_id'];
    $name     = $_POST['name'];
    $skills   = strtolower($_POST['skills']);
    $location = $_POST['location'];

    // 1. Array to hold update fields dynamically
    $update_fields = [
        'full_name' => $name,
        'skills'    => $skills,
        'location'  => $location
    ];

    // 2. Handle profile picture upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $img_dir = "uploads/profile_pics/";
        if (!is_dir($img_dir)) mkdir($img_dir, 0777, true);
        
        $img_ext  = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $img_name = time() . "_user_" . $id . "." . $img_ext;
        $img_path = $img_dir . $img_name;

       if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $img_path)) {
         $update_fields['profile_pic'] = $img_path;
        } else {
        die("Error: Could not move the uploaded file. Check folder permissions.");
        }
    }

    // 3. Handle CV document upload
    if (isset($_FILES['cv']) && $_FILES['cv']['error'] == 0) {
        $cv_dir = "uploads/cvs/";
        if (!is_dir($cv_dir)) mkdir($cv_dir, 0777, true);
        
        $cv_ext  = pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION);
        $cv_name = time() . "_cv_" . $id . "." . $cv_ext;
        $cv_path = $cv_dir . $cv_name;

        if (move_uploaded_file($_FILES['cv']['tmp_name'], $cv_path)) {
            $update_fields['cv_path'] = $cv_path;
        }
    }

    // 4. Construct secure dynamic PDO assignment query
    $query_parts = [];
    foreach ($update_fields as $column => $value) {
        $query_parts[] = "$column = :$column";
    }
    
    $sql = "UPDATE users SET " . implode(', ', $query_parts) . " WHERE id = :id";
    $update_fields['id'] = $id;

    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute($update_fields)) {
        echo "<script>alert('Profile updated successfully!'); window.location.href='profile.php';</script>";
        exit();
    } else {
        echo "Update Error occurred.";
    }
} else {
    header("Location: profile.php");
    exit();
}
?>