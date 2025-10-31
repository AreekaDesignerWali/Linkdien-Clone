<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo '<script>window.location.href = "login.php";</script>';
    exit;
}

$location = isset($_GET['location']) ? $_GET['location'] : '';
$industry = isset($_GET['industry']) ? $_GET['industry'] : '';
$experience_level = isset($_GET['experience_level']) ? $_GET['experience_level'] : '';

$query = "SELECT j.*, u.first_name, u.last_name FROM jobs j JOIN users u ON j.poster_id = u.id WHERE 1=1";
$params = [];

if ($location) {
    $query .= " AND location LIKE ?";
    $params[] = "%$location%";
}
if ($industry) {
    $query .= " AND industry LIKE ?";
    $params[] = "%$industry%";
}
if ($experience_level) {
    $query .= " AND experience_level LIKE ?";
    $params[] = "%$experience_level%";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$jobs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Search</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f3f2ef; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: auto; }
        form { margin-bottom: 20px; }
        input { padding: 10px; margin-right: 10px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #0077b5; color: white; padding: 10px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #005582; }
        .job { background: white; padding: 15px; margin-bottom: 10px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        @media (max-width: 768px) { input { width: 100%; margin-bottom: 10px; } }
        button { background: linear-gradient(to bottom, #0077b5, #005582); }
    </style>
</head>
<body>
    <div class="container">
        <h1>Job Search</h1>
        <form method="GET">
            <input type="text" name="location" placeholder="Location" value="<?php echo $location; ?>">
            <input type="text" name="industry" placeholder="Industry" value="<?php echo $industry; ?>">
            <input type="text" name="experience_level" placeholder="Experience Level" value="<?php echo $experience_level; ?>">
            <button type="submit">Search</button>
        </form>
        <?php foreach ($jobs as $job): ?>
            <div class="job">
                <h3><?php echo $job['title']; ?></h3>
                <p>Posted by: <?php echo $job['first_name'] . ' ' . $job['last_name']; ?></p>
                <p><?php echo $job['description']; ?></p>
                <p>Location: <?php echo $job['location']; ?> | Industry: <?php echo $job['industry']; ?> | Experience: <?php echo $job['experience_level']; ?></p>
                <form action="apply_job.php" method="POST">
                    <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                    <button type="submit">Apply</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
