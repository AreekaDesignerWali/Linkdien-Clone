<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo '<script>window.location.href = "login.php";</script>';
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $headline = $_POST['headline'];
    $summary = $_POST['summary'];
    $industry = $_POST['industry'];

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir);
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file);
        $profile_picture = $target_file;
    } else {
        $profile_picture = null;
    }

    $stmt = $pdo->prepare("UPDATE users SET headline = ?, summary = ?, industry = ?, profile_picture = COALESCE(?, profile_picture) WHERE id = ?");
    $stmt->execute([$headline, $summary, $industry, $profile_picture, $user_id]);
    echo '<script>window.location.href = "profile.php";</script>';
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
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
    <form method="POST" enctype="multipart/form-data">
        <h2>Edit Profile</h2>
        <input type="text" name="headline" value="<?php echo $user['headline']; ?>" placeholder="Headline">
        <textarea name="summary" placeholder="Summary"><?php echo $user['summary']; ?></textarea>
        <input type="text" name="industry" value="<?php echo $user['industry']; ?>" placeholder="Industry">
        <input type="file" name="profile_picture">
        <button type="submit">Save</button>
    </form>
</body>
</html>
