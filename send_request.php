<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo '<script>window.location.href = "login.php";</script>';
    exit;
}

$user_id = $_SESSION['user_id'];
$connected_user_id = $_POST['connected_user_id'];

$stmt = $pdo->prepare("INSERT INTO connections (user_id, connected_user_id) VALUES (?, ?)");
$stmt->execute([$user_id, $connected_user_id]);

echo '<script>window.location.href = "connections.php";</script>';
exit;
?>
