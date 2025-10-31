<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo '<script>window.location.href = "login.php";</script>';
    exit;
}

$conn_id = $_POST['conn_id'];

$stmt = $pdo->prepare("UPDATE connections SET status = 'accepted' WHERE id = ?");
$stmt->execute([$conn_id]);

// Add reverse connection
$stmt = $pdo->prepare("SELECT user_id, connected_user_id FROM connections WHERE id = ?");
$stmt->execute([$conn_id]);
$conn = $stmt->fetch();

$stmt = $pdo->prepare("INSERT INTO connections (user_id, connected_user_id, status) VALUES (?, ?, 'accepted')");
$stmt->execute([$conn['connected_user_id'], $conn['user_id']]);

echo '<script>window.location.href = "connections.php";</script>';
exit;
?>
