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
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 20px 40px rgba(0, 0, 0, 0.08);
            --radius: 16px;
            --radius-sm: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #e0f2fe 0%, #f8fbff 100%);
            color: var(--gray-800);
            min-height: 100vh;
            padding: 24px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            background: var(--primary);
            color: white;
            padding: 40px 32px;
            text-align: center;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            margin-bottom: 32px;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .header p {
            font-size: 16px;
            opacity: 0.95;
        }

        /* Back Button */
        /* Container to handle the alignment */
        .button-wrapper {
            display: flex;
            justify-content: flex-end;
            /* Pushes the button to the right */
            width: 100%;
            padding: 0 20px;
            /* Optional: adds some breathing room from the screen edge */
        }

        .back-btn {
            display: inline-block;
            position: relative;
            border-radius: 5px;
            background-color: #2151ffff;
            border: none;
            color: #fff;
            text-align: center;
            font-size: 15px;
            padding: 10px 20px;
            /* Even padding for better look */
            width: auto;
            /* Changed to auto so it fits the text nicely */
            min-width: 180px;
            /* Ensures it's not too small */
            transition: all 0.5s;
            cursor: pointer;
            margin: 20px 0;
            /* Adjusted margins to avoid layout gaps */
            box-shadow: 0 10px 20px -8px rgba(0, 0, 0, 0.7);
            text-decoration: none;
            font-weight: 400;
            /* Slightly heavier weight for better readability */
        }

        .back-btn:after {
            content: '«';
            position: absolute;
            opacity: 0;
            top: 50%;
            transform: translateY(-50%);
            /* Perfectly centers the arrow vertically */
            left: -20px;
            transition: 0.5s;
        }

        .back-btn:hover {
            padding-left: 45px;
            padding-right: 15px;
        }

        .back-btn:hover:after {
            opacity: 1;
            left: 20px;
        }

        /* Messages */
        .messages-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .message-card {
            background: white;
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow);
            padding: 20px 24px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .message-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 8px;
        }

        .sender {
            font-weight: 600;
            color: var(--primary);
            font-size: 16px;
        }

        .email {
            color: var(--gray-600);
            font-size: 14px;
        }

        .date {
            font-size: 13px;
            color: var(--gray-600);
        }

        .message-body {
            margin-top: 10px;
            font-size: 15px;
            line-height: 1.6;
            color: var(--gray-800);
        }

        /* Empty State */
        .no-messages {
            background: white;
            padding: 60px 20px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            text-align: center;
            color: var(--gray-600);
            font-size: 16px;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .header h1 {
                font-size: 22px;
            }

            .message-card {
                padding: 18px;
            }

            .sender {
                font-size: 15px;
            }
        }

        .mark-read-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 22px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: var(--shadow-sm);
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .mark-read-btn:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
        }

        .message-actions {
            margin-top: 12px;
            text-align: right;
        }

        .delete-btn {
            background: var(--danger);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .delete-btn:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }

        .message-card.read {
            opacity: 0.75;
        }
    </style>
</head>

<body>

    <div class="container">

        <div class="header">
            <h1>Contact Messages</h1>
            <p>All inquiries sent from the website</p>
        </div>

        <div class="button-wrapper">
            <a href="dashboard.php" class="back-btn">Back to Dashboard</a>
        </div>

        <form method="POST" style="margin-bottom: 24px;">
            <button type="submit" name="mark_all_read" class="mark-read-btn">
                Mark all as read
            </button>
        </form>


        <?php if (empty($messages)): ?>
            <div class="no-messages">
                No messages have been received yet.
            </div>
        <?php else: ?>
            <div class="messages-list">
                <?php foreach ($messages as $msg): ?>
                    <div class="message-card">
                        <div class="message-header">
                            <div>
                                <div class="sender"><?= htmlspecialchars($msg['name']) ?></div>
                                <div class="email"><?= htmlspecialchars($msg['email']) ?></div>
                            </div>
                            <div class="date">
                                <?= date('F j, Y · g:i a', strtotime($msg['created_at'])) ?>
                            </div>
                        </div>

                        <div class="message-body">
                            <?= nl2br(htmlspecialchars($msg['message'])) ?>
                        </div>
                        <div class="message-actions">
                            <a href="messages.php?delete=<?= $msg['id'] ?>"
                                class="delete-btn"
                                onclick="return confirm('Are you sure you want to delete this message?')">
                                Delete
                            </a>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

</body>

</html>