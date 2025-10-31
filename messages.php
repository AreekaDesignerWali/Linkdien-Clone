<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo '<script>window.location.href = "login.php";</script>';
    exit;
}

$user_id = $_SESSION['user_id'];
$receiver_id = isset($_GET['receiver_id']) ? $_GET['receiver_id'] : null;

// Fetch conversations (unique users with last message)
$stmt = $pdo->prepare("
    SELECT u.id, u.first_name, u.last_name, m.message, m.sent_at
    FROM (
        SELECT 
            CASE 
                WHEN sender_id = ? THEN receiver_id 
                ELSE sender_id 
            END as other_user_id,
            MAX(sent_at) as last_message_time
        FROM messages 
        WHERE sender_id = ? OR receiver_id = ?
        GROUP BY other_user_id
    ) latest
    JOIN users u ON u.id = latest.other_user_id
    LEFT JOIN messages m ON 
        (m.sender_id = ? OR m.receiver_id = ?) 
        AND m.sent_at = latest.last_message_time
    ORDER BY latest.last_message_time DESC
");
$stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id]);
$conversations = $stmt->fetchAll();

// Fetch messages if a receiver is selected
$messages = [];
$receiver_name = '';
if ($receiver_id) {
    $stmt = $pdo->prepare("
        SELECT m.*, 
               s.first_name as sender_first, 
               s.last_name as sender_last,
               r.first_name as receiver_first,
               r.last_name as receiver_last
        FROM messages m 
        JOIN users s ON m.sender_id = s.id 
        JOIN users r ON m.receiver_id = r.id
        WHERE (m.sender_id = ? AND m.receiver_id = ?) 
           OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.sent_at ASC
    ");
    $stmt->execute([$user_id, $receiver_id, $receiver_id, $user_id]);
    $messages = $stmt->fetchAll();
    
    // Get receiver's name
    $stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
    $stmt->execute([$receiver_id]);
    $receiver = $stmt->fetch();
    $receiver_name = $receiver ? $receiver['first_name'] . ' ' . $receiver['last_name'] : 'Unknown';
}

// Search users for new conversations
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$search_results = [];
if ($search_query) {
    $stmt = $pdo->prepare("
        SELECT id, first_name, last_name, headline 
        FROM users 
        WHERE (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?) 
        AND id != ?
        LIMIT 10
    ");
    $search_term = "%$search_query%";
    $stmt->execute([$search_term, $search_term, $search_term, $user_id]);
    $search_results = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <style>
        /* LinkedIn-inspired professional CSS with kamal look */
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f3f2ef;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: auto;
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .conv-list, .chat {
            background: white;
            border-radius: 8px;
            padding: 15px;
        }
        .conv-list h2, .chat h2 {
            color: #0077b5;
            font-size: 22px;
            margin-bottom: 15px;
            border-bottom: 2px solid #0077b5;
            padding-bottom: 5px;
        }
        .conv {
            padding: 10px;
            border-bottom: 1px solid #e8e8e8;
            cursor: pointer;
            transition: background 0.2s;
        }
        .conv:hover {
            background: #f8f9fa;
        }
        .conv p {
            margin: 5px 0;
            font-size: 14px;
        }
        .conv .name {
            font-weight: bold;
            color: #0077b5;
        }
        .conv .preview {
            color: #666;
            font-size: 12px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .msg {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 8px;
            max-width: 70%;
        }
        .msg.sent {
            background: #0077b5;
            color: white;
            margin-left: auto;
            border-bottom-right-radius: 0;
        }
        .msg.received {
            background: #f1f0f0;
            border: 1px solid #ddd;
            margin-right: auto;
            border-bottom-left-radius: 0;
        }
        .msg p {
            margin: 5px 0;
        }
        .msg small {
            font-size: 12px;
            color: #999;
        }
        .no-data {
            color: #666;
            font-style: italic;
            text-align: center;
            padding: 20px;
        }
        form {
            margin-top: 20px;
        }
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
        }
        button {
            background: linear-gradient(to bottom, #0077b5, #005582);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover {
            background: #005582;
        }
        .search-form {
            margin-bottom: 20px;
        }
        .search-form input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .search-results .item {
            padding: 10px;
            border-bottom: 1px solid #e8e8e8;
        }
        .search-results .item:hover {
            background: #f8f9fa;
        }
        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
                padding: 10px;
            }
            .msg {
                max-width: 90%;
            }
            .conv-list {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="conv-list">
            <h2>Conversations</h2>
            <form class="search-form" method="GET">
                <input type="text" name="search" placeholder="Search users to message" value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit">Search</button>
            </form>
            <?php if ($search_query && !empty($search_results)): ?>
                <h3>Search Results</h3>
                <div class="search-results">
                    <?php foreach ($search_results as $user): ?>
                        <div class="item">
                            <p><a href="messages.php?receiver_id=<?php echo $user['id']; ?>" class="name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></a></p>
                            <p><?php echo htmlspecialchars($user['headline'] ?: 'No headline'); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($search_query): ?>
                <p class="no-data">No users found.</p>
            <?php endif; ?>
            <?php if (empty($conversations)): ?>
                <p class="no-data">No conversations yet. Start one by searching for a user!</p>
            <?php else: ?>
                <?php foreach ($conversations as $conv): ?>
                    <div class="conv" onclick="window.location.href='messages.php?receiver_id=<?php echo $conv['id']; ?>'">
                        <p class="name"><?php echo htmlspecialchars($conv['first_name'] . ' ' . $conv['last_name']); ?></p>
                        <p class="preview"><?php echo htmlspecialchars($conv['message'] ? substr($conv['message'], 0, 50) : 'No messages yet'); ?></p>
                        <small><?php echo $conv['sent_at'] ? date('M d, Y H:i', strtotime($conv['sent_at'])) : ''; ?></small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="chat">
            <?php if ($receiver_id): ?>
                <h2>Chat with <?php echo htmlspecialchars($receiver_name); ?></h2>
                <?php if (empty($messages)): ?>
                    <p class="no-data">No messages yet. Start the conversation!</p>
                <?php else: ?>
                    <div class="messages">
                        <?php foreach ($messages as $msg): ?>
                            <div class="msg <?php echo $msg['sender_id'] == $user_id ? 'sent' : 'received'; ?>">
                                <p><strong><?php echo htmlspecialchars($msg['sender_first'] . ' ' . $msg['sender_last']); ?>:</strong> <?php echo htmlspecialchars($msg['message']); ?></p>
                                <small><?php echo date('M d, Y H:i', strtotime($msg['sent_at'])); ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <form action="send_message.php" method="POST">
                    <input type="hidden" name="receiver_id" value="<?php echo $receiver_id; ?>">
                    <textarea name="message" placeholder="Type your message" required></textarea>
                    <button type="submit">Send</button>
                </form>
            <?php else: ?>
                <h2>Messages</h2>
                <p class="no-data">Select a conversation or search for a user to start messaging.</p>
            <?php endif; ?>
            <a href="index.php"><button>Back to Home</button></a>
        </div>
    </div>
</body>
</html>
