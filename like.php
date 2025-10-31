<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo '<script>window.location.href = "login.php";</script>';
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'];

$stmt = $pdo->prepare("INSERT IGNORE INTO likes (post_id, user_id) VALUES (?, ?)");
$stmt->execute([$post_id, $user_id]);

echo '<script>window.location.href = "index.php";</script>';
exit;
?>
