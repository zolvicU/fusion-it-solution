<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

// Delete single message
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: messages.php");
    exit;
}

if (isset($_POST['mark_all_read'])) {
    $pdo->query("UPDATE contact_messages SET is_read = 1");
    header("Location: messages.php");
    exit;
}

if (isset($_GET['ajax_read'])) {
    $id = (int)$_GET['ajax_read'];
    $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
    $stmt->execute([$id]);
    exit;
}

$stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Messages</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --success: #10b981;
            --danger: #ef4444;
            --gray-100: #f8fafc;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-600: #475569;
            --gray-800: #1e293b;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 20px 40px rgba(0, 0, 0, 0.08);
            --radius: 16px;
            --radius-sm: 10px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #e0f2fe 0%, #f8fbff 100%);
            color: var(--gray-800);
            min-height: 100vh;
            padding: 24px;
        }

        .container { max-width: 900px; margin: 0 auto; }

        /* Header preserved */
        .header {
            background: var(--primary);
            color: white;
            padding: 30px 32px;
            text-align: center;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            margin-bottom: 24px;
        }

        .header h1 { font-size: 24px; font-weight: 700; margin-bottom: 4px; }
        .header p { font-size: 14px; opacity: 0.9; }

        .button-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .back-btn {
            text-decoration: none;
            color: var(--primary);
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .mark-read-btn {
            background: white;
            color: var(--gray-800);
            border: 1px solid var(--gray-200);
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .mark-read-btn:hover { background: var(--gray-100); border-color: var(--gray-300); }

        /* SLIM MESSAGES LIST */
        .messages-list { display: flex; flex-direction: column; gap: 12px; }

        .message-card {
            background: white;
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow);
            padding: 16px 20px;
            border: 1px solid var(--gray-200);
            border-left: 4px solid transparent;
            position: relative;
            transition: all 0.2s ease;
        }

        .message-card.unread {
            border-left-color: var(--primary);
            background: #fdfdff;
        }

        .message-card.read { opacity: 0.85; }

        .message-card:hover { border-color: var(--primary-light); transform: translateX(2px); }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 6px;
        }

        .sender-meta { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        
        .sender { font-weight: 600; color: var(--gray-800); font-size: 15px; }
        .unread-dot { color: var(--primary); font-size: 12px; }
        .email { color: var(--gray-600); font-size: 13px; font-weight: 400; }
        
        .date { font-size: 12px; color: var(--gray-600); }

        .message-body {
            font-size: 14px;
            line-height: 1.6;
            color: var(--gray-800);
            max-width: 90%;
        }

        .message-actions {
            position: absolute;
            top: 16px;
            right: 20px;
        }

        .delete-btn {
            color: var(--gray-300);
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .delete-btn:hover { color: var(--danger); background: #fee2e2; }

        .toggle-text-btn {
            background: none;
            border: none;
            color: var(--primary);
            cursor: pointer;
            font-weight: 600;
            font-size: 12px;
            margin-top: 4px;
            padding: 0;
            text-decoration: underline;
        }

        .full-message { display: none; }

        .no-messages {
            background: white;
            padding: 40px;
            border-radius: var(--radius);
            text-align: center;
            color: var(--gray-600);
            border: 2px dashed var(--gray-200);
        }

        @media (max-width: 600px) {
            .sender-meta { flex-direction: column; align-items: flex-start; gap: 2px; }
            .message-actions { position: static; margin-top: 10px; text-align: right; }
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="header">
            <h1>Contact Messages</h1>
            <p>Admin Control Panel</p>
        </div>

        <div class="button-wrapper">
            <a href="dashboard.php" class="back-btn">« Back to Dashboard</a>
            <form method="POST">
                <button type="submit" name="mark_all_read" class="mark-read-btn">
                    Mark all as read
                </button>
            </form>
        </div>

        <?php if (empty($messages)): ?>
            <div class="no-messages">
                No messages have been received yet.
            </div>
        <?php else: ?>
            <div class="messages-list">
                <?php foreach ($messages as $msg):
                    $is_unread = isset($msg['is_read']) && $msg['is_read'] == 0;
                    $full_msg = htmlspecialchars($msg['message']);
                    $is_long = strlen($full_msg) > 160;
                    $preview = $is_long ? substr($full_msg, 0, 160) . "..." : $full_msg;
                ?>
                    <div class="message-card <?= $is_unread ? 'unread' : 'read' ?>" id="card-<?= $msg['id'] ?>">
                        <div class="message-header">
                            <div class="sender-meta">
                                <span class="sender"><?= htmlspecialchars($msg['name']) ?></span>
                                <?php if($is_unread): ?><span class="unread-dot">●</span><?php endif; ?>
                                <span class="email"><?= htmlspecialchars($msg['email']) ?></span>
                                <span class="date">• <?= date('M j, Y · g:i a', strtotime($msg['created_at'])) ?></span>
                            </div>
                            <div class="message-actions">
                                <a href="messages.php?delete=<?= $msg['id'] ?>" 
                                   class="delete-btn" 
                                   onclick="return confirm('Delete this message?')">Delete</a>
                            </div>
                        </div>

                        <div class="message-body">
                            <span class="preview-text" id="prev-<?= $msg['id'] ?>"><?= nl2br($preview) ?></span>
                            <?php if ($is_long): ?>
                                <span class="full-message" id="full-<?= $msg['id'] ?>"><?= nl2br($full_msg) ?></span>
                                <button type="button" class="toggle-text-btn" onclick="toggleMessage(<?= $msg['id'] ?>, <?= $is_unread ? 'true' : 'false' ?>)">Show more</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleMessage(id, wasUnread) {
            const preview = document.getElementById('prev-' + id);
            const full = document.getElementById('full-' + id);
            const card = document.getElementById('card-' + id);
            const btn = event.target;

            if (full.style.display === "inline") {
                full.style.display = "none";
                preview.style.display = "inline";
                btn.innerText = "Show more";
            } else {
                full.style.display = "inline";
                preview.style.display = "none";
                btn.innerText = "Show less";

                if (wasUnread && card.classList.contains('unread')) {
                    card.classList.remove('unread');
                    card.classList.add('read');
                    const dot = card.querySelector('.unread-dot');
                    if(dot) dot.style.display = 'none';
                    fetch('messages.php?ajax_read=' + id);
                }
            }
        }
    </script>
</body>
</html>