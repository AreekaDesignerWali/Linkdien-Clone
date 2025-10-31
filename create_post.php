<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo '<script>window.location.href = "login.php";</script>';
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $content = $_POST['content'];
    $type = $_POST['type'];

    $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, type) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $content, $type]);
    echo '<script>window.location.href = "index.php";</script>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f3f2ef; display: flex; justify-content: center; align-items: center; height: 100vh; }
        form { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 400px; }
        textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; height: 150px; }
        select { width: 100%; padding: 10px; margin: 10px 0; }
        button { background: #0077b5; color: white; width: 100%; padding: 10px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #005582; }
        button { background: linear-gradient(to bottom, #0077b5, #005582); }
    </style>
</head>
<body>
    <form method="POST">
        <h2>Create Post</h2>
        <textarea name="content" placeholder="What's on your mind?" required></textarea>
        <select name="type">
            <option value="update">Update</option>
            <option value="article">Article</option>
        </select>
        <button type="submit">Post</button>
    </form>
</body>
</html>
