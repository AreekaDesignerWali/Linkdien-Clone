<?php
// Database connection file using PDO for security and pro-level handling
$host = "localhost"; // Change to your actual host if not localhost (e.g., for ClearDB, it might be something like us-cdbr-east-06.cleardb.net)
$dbname = "db7pbw3rgmlc8g";
$username = "ugvwdledjfa2b";
$password = "6e94icxzn2tg";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
