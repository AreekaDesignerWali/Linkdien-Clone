<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo '<script>window.location.href = "login.php";</script>';
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $industry = $_POST['industry'];
    $experience_level = $_POST['experience_level'];

    $stmt = $pdo->prepare("INSERT INTO jobs (poster_id, title, description, location, industry, experience_level) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $title, $description, $location, $industry, $experience_level]);
    echo '<script>window.location.href = "index.php";</script>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Job</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f3f2ef; display: flex; justify-content: center; align-items: center; height: 100vh; }
        form { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 400px; }
        input, textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #0077b5; color: white; width: 100%; padding: 10px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #005582; }
        button { background: linear-gradient(to bottom, #0077b5, #005582); }
    </style>
</head>
<body>
    <form method="POST">
        <h2>Post Job</h2>
        <input type="text" name="title" placeholder="Job Title" required>
        <textarea name="description" placeholder="Description" required></textarea>
        <input type="text" name="location" placeholder="Location">
        <input type="text" name="industry" placeholder="Industry">
        <input type="text" name="experience_level" placeholder="Experience Level">
        <button type="submit">Post Job</button>
    </form>
</body>
</html>
