<?php

$host = "localhost";
$dbname = "smart_career_db";
$username = "root";
$password = "";

try {
   
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    
    die("Database Connection Failed: " . $e->getMessage());
}
?>