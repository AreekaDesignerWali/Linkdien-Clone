<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo '<script>window.location.href = "login.php";</script>';
    exit;
}

$user_id = $_SESSION['user_id'];
$job_id = $_POST['job_id'];

$stmt = $pdo->prepare("INSERT IGNORE INTO job_applications (job_id, applicant_id) VALUES (?, ?)");
$stmt->execute([$job_id, $user_id]);

echo '<script>window.location.href = "jobs_search.php";</script>';
exit;
?>
