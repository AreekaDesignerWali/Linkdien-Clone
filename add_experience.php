<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo '<script>window.location.href = "login.php";</script>';
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $company = $_POST['company'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $stmt = $pdo->prepare("INSERT INTO experience (user_id, company, title, description, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $company, $title, $description, $start_date, $end_date]);
    echo '<script>window.location.href = "profile.php";</script>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Experience</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f3f2ef; display: flex; justify-content: center; align-items: center; height: 100vh; }
        form { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 300px; }
        input, textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #0077b5; color: white; width: 100%; padding: 10px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #005582; }
        button { background: linear-gradient(to bottom, #0077b5, #005582); }
    </style>
</head>
<body>
    <form method="POST">
        <h2>Add Experience</h2>
        <input type="text" name="company" placeholder="Company" required>
        <input type="text" name="title" placeholder="Title" required>
        <textarea name="description" placeholder="Description"></textarea>
        <input type="date" name="start_date" placeholder="Start Date">
        <input type="date" name="end_date" placeholder="End Date">
        <button type="submit">Add</button>
    </form>
</body>
</html>
