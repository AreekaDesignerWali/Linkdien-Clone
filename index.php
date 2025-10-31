<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    // Use JS for redirection
    echo '<script>window.location.href = "login.php";</script>';
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch posts for feed (user updates, trending)
$stmt = $pdo->prepare("SELECT p.*, u.first_name, u.last_name FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 10");
$stmt->execute();
$posts = $stmt->fetchAll();

// Fetch jobs
$stmt = $pdo->prepare("SELECT j.*, u.first_name, u.last_name FROM jobs j JOIN users u ON j.poster_id = u.id ORDER BY j.created_at DESC LIMIT 5");
$stmt->execute();
$jobs = $stmt->fetchAll();

// Trending: simple recent with likes count
$stmt = $pdo->prepare("SELECT p.*, COUNT(l.id) as like_count, u.first_name, u.last_name FROM posts p LEFT JOIN likes l ON p.id = l.post_id JOIN users u ON p.user_id = u.id GROUP BY p.id ORDER BY like_count DESC LIMIT 5");
$stmt->execute();
$trending = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LinkedIn Clone - Homepage</title>
    <style>
        /* Amazing, real-looking CSS: LinkedIn-inspired blue theme, clean, professional, responsive */
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f3f2ef; color: #333; }
        header { background-color: #0077b5; color: white; padding: 10px; display: flex; justify-content: space-between; align-items: center; }
        nav a { color: white; margin: 0 15px; text-decoration: none; }
        .container { max-width: 1200px; margin: 20px auto; display: grid; grid-template-columns: 1fr 2fr 1fr; gap: 20px; }
        .feed, .jobs, .trending { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .post { margin-bottom: 20px; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
        button { background: #0077b5; color: white; border: none; padding: 10px; border-radius: 4px; cursor: pointer; }
        button:hover { background: #005582; }
        @media (max-width: 768px) { .container { grid-template-columns: 1fr; } }
        /* More pro CSS: gradients, shadows for kamal look */
        header { background: linear-gradient(to right, #0077b5, #00a0dc); }
        .feed h2, .jobs h2, .trending h2 { color: #0077b5; font-weight: bold; }
    </style>
</head>
<body>
    <header>
        <h1>LinkedIn Clone</h1>
        <nav>
            <a href="profile.php">Profile</a>
            <a href="connections.php">Connections</a>
            <a href="jobs_search.php">Jobs</a>
            <a href="messages.php">Messages</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <div class="container">
        <section class="feed">
            <h2>User Updates</h2>
            <a href="create_post.php"><button>Create Post</button></a>
            <?php foreach ($posts as $post): ?>
                <div class="post">
                    <p><strong><?php echo $post['first_name'] . ' ' . $post['last_name']; ?></strong></p>
                    <p><?php echo $post['content']; ?></p>
                    <form action="like.php" method="POST">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <button type="submit">Like</button>
                    </form>
                    <form action="comment.php" method="POST">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <input type="text" name="comment" placeholder="Comment">
                        <button type="submit">Comment</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </section>
        <section class="jobs">
            <h2>Job Postings</h2>
            <a href="post_job.php"><button>Post Job</button></a>
            <?php foreach ($jobs as $job): ?>
                <div class="post">
                    <p><strong><?php echo $job['title']; ?></strong> by <?php echo $job['first_name'] . ' ' . $job['last_name']; ?></p>
                    <p><?php echo $job['description']; ?></p>
                    <form action="apply_job.php" method="POST">
                        <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                        <button type="submit">Apply</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </section>
        <section class="trending">
            <h2>Trending Content</h2>
            <?php foreach ($trending as $trend): ?>
                <div class="post">
                    <p><strong><?php echo $trend['first_name'] . ' ' . $trend['last_name']; ?></strong></p>
                    <p><?php echo $trend['content']; ?> (Likes: <?php echo $trend['like_count']; ?>)</p>
                </div>
            <?php endforeach; ?>
        </section>
    </div>
</body>
</html>
