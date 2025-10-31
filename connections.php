<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo '<script>window.location.href = "login.php";</script>';
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch accepted connections
$stmt = $pdo->prepare("SELECT u.*, c.status FROM connections c JOIN users u ON c.connected_user_id = u.id WHERE c.user_id = ? AND c.status = 'accepted'");
$stmt->execute([$user_id]);
$connections = $stmt->fetchAll();

// Fetch pending requests (sent to the user)
$stmt = $pdo->prepare("SELECT u.*, c.id as conn_id FROM connections c JOIN users u ON c.user_id = u.id WHERE c.connected_user_id = ? AND c.status = 'pending'");
$stmt->execute([$user_id]);
$requests = $stmt->fetchAll();

// Fetch suggested connections: users in the same industry, not already connected
$stmt = $pdo->prepare("SELECT u.* FROM users u WHERE u.industry = (SELECT industry FROM users WHERE id = ?) AND u.id != ? AND u.id NOT IN (SELECT connected_user_id FROM connections WHERE user_id = ? AND status = 'accepted') LIMIT 5");
$stmt->execute([$user_id, $user_id, $user_id]);
$suggested = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connections</title>
    <style>
        /* Professional LinkedIn-inspired CSS with enhanced visuals */
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f3f2ef;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .list {
            margin-bottom: 30px;
        }
        .list h2 {
            color: #0077b5;
            font-size: 24px;
            margin-bottom: 15px;
            border-bottom: 2px solid #0077b5;
            padding-bottom: 5px;
        }
        .item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            border-bottom: 1px solid #e8e8e8;
            transition: background 0.2s;
        }
        .item:hover {
            background: #f8f9fa;
        }
        .item p {
            margin: 0;
            font-size: 16px;
        }
        button {
            background: linear-gradient(to bottom, #0077b5, #005582);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover {
            background: #005582;
        }
        .no-data {
            color: #666;
            font-style: italic;
            text-align: center;
            padding: 20px;
        }
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            .item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            button {
                width: 100%;
                text-align: center;
            }
        }
        /* Additional styling for profile links */
        .profile-link {
            color: #0077b5;
            text-decoration: none;
            font-weight: bold;
        }
        .profile-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Connections</h1>
        
        <!-- Your Connections -->
        <div class="list">
            <h2>Your Connections</h2>
            <?php if (empty($connections)): ?>
                <p class="no-data">You have no connections yet.</p>
            <?php else: ?>
                <?php foreach ($connections as $conn): ?>
                    <div class="item">
                        <p><a href="profile.php?id=<?php echo $conn['id']; ?>" class="profile-link"><?php echo htmlspecialchars($conn['first_name'] . ' ' . $conn['last_name']); ?></a></p>
                        <p><?php echo htmlspecialchars($conn['headline'] ?: 'No headline'); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pending Requests -->
        <div class="list">
            <h2>Pending Requests</h2>
            <?php if (empty($requests)): ?>
                <p class="no-data">No pending connection requests.</p>
            <?php else: ?>
                <?php foreach ($requests as $req): ?>
                    <div class="item">
                        <p><a href="profile.php?id=<?php echo $req['id']; ?>" class="profile-link"><?php echo htmlspecialchars($req['first_name'] . ' ' . $req['last_name']); ?></a></p>
                        <form action="accept_request.php" method="POST">
                            <input type="hidden" name="conn_id" value="<?php echo $req['conn_id']; ?>">
                            <button type="submit">Accept</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Suggested Connections -->
        <div class="list">
            <h2>Suggested Connections</h2>
            <?php if (empty($suggested)): ?>
                <p class="no-data">No suggested connections at this time.</p>
            <?php else: ?>
                <?php foreach ($suggested as $sug): ?>
                    <div class="item">
                        <p><a href="profile.php?id=<?php echo $sug['id']; ?>" class="profile-link"><?php echo htmlspecialchars($sug['first_name'] . ' ' . $sug['last_name']); ?></a></p>
                        <p><?php echo htmlspecialchars($sug['headline'] ?: 'No headline'); ?></p>
                        <form action="send_request.php" method="POST">
                            <input type="hidden" name="connected_user_id" value="<?php echo $sug['id']; ?>">
                            <button type="submit">Connect</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <a href="index.php"><button>Back to Home</button></a>
    </div>
</body>
</html>
