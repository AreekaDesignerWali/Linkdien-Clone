<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo '<script>window.location.href = "login.php";</script>';
    exit;
}

$user_id = $_SESSION['user_id'];
$view_id = isset($_GET['id']) ? $_GET['id'] : $user_id; // View own or others

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$view_id]);
$user = $stmt->fetch();

// Education
$stmt = $pdo->prepare("SELECT * FROM education WHERE user_id = ?");
$stmt->execute([$view_id]);
$education = $stmt->fetchAll();

// Experience
$stmt = $pdo->prepare("SELECT * FROM experience WHERE user_id = ?");
$stmt->execute([$view_id]);
$experience = $stmt->fetchAll();

// Skills
$stmt = $pdo->prepare("SELECT * FROM skills WHERE user_id = ?");
$stmt->execute([$view_id]);
$skills = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f3f2ef; margin: 0; padding: 0; }
        .profile { max-width: 800px; margin: 20px auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        img { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; }
        .section { margin-top: 20px; }
        button { background: #0077b5; color: white; padding: 10px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #005582; }
        @media (max-width: 768px) { .profile { padding: 10px; } }
        /* Kamal CSS: card-like sections */
        .section { background: #fafafa; padding: 15px; border-radius: 6px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="profile">
        <h1><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></h1>
        <p><?php echo $user['headline']; ?></p>
        <img src="<?php echo $user['profile_picture'] ? $user['profile_picture'] : 'default.jpg'; ?>" alt="Profile Picture">
        <p><?php echo $user['summary']; ?></p>
        <?php if ($view_id == $user_id): ?>
            <a href="edit_profile.php"><button>Edit Profile</button></a>
        <?php else: ?>
            <form action="send_request.php" method="POST">
                <input type="hidden" name="connected_user_id" value="<?php echo $view_id; ?>">
                <button type="submit">Connect</button>
            </form>
        <?php endif; ?>
        <div class="section">
            <h2>Experience</h2>
            <?php foreach ($experience as $exp): ?>
                <p><strong><?php echo $exp['title']; ?></strong> at <?php echo $exp['company']; ?> (<?php echo $exp['start_date'] . ' - ' . $exp['end_date']; ?>)</p>
            <?php endforeach; ?>
            <?php if ($view_id == $user_id): ?><a href="add_experience.php"><button>Add Experience</button></a><?php endif; ?>
        </div>
        <div class="section">
            <h2>Education</h2>
            <?php foreach ($education as $edu): ?>
                <p><strong><?php echo $edu['degree']; ?></strong> from <?php echo $edu['school']; ?> (<?php echo $edu['start_date'] . ' - ' . $edu['end_date']; ?>)</p>
            <?php endforeach; ?>
            <?php if ($view_id == $user_id): ?><a href="add_education.php"><button>Add Education</button></a><?php endif; ?>
        </div>
        <div class="section">
            <h2>Skills</h2>
            <?php foreach ($skills as $skill): ?>
                <p><?php echo $skill['skill']; ?></p>
            <?php endforeach; ?>
            <?php if ($view_id == $user_id): ?><a href="add_skill.php"><button>Add Skill</button></a><?php endif; ?>
        </div>
    </div>
</body>
</html>
