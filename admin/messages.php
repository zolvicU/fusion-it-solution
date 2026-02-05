<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

// Pagination
$messages_per_page = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $messages_per_page;

// Get statistics
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages");
    $total_messages = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0");
    $unread_count = $stmt->fetchColumn();
    
    $total_pages = ceil($total_messages / $messages_per_page);
} catch (Exception $e) {
    $error = "Failed to load statistics: " . $e->getMessage();
}

// Delete single message
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Message deleted successfully.";
        header("Location: messages.php?page=" . $page);
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = "Failed to delete message: " . $e->getMessage();
        header("Location: messages.php?page=" . $page);
        exit;
    }
}

// Mark all as read
if (isset($_POST['mark_all_read'])) {
    try {
        $pdo->query("UPDATE contact_messages SET is_read = 1");
        $_SESSION['success'] = "All messages marked as read.";
        header("Location: messages.php?page=" . $page);
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = "Failed to mark messages as read: " . $e->getMessage();
        header("Location: messages.php?page=" . $page);
        exit;
    }
}

// Mark single as read via AJAX
if (isset($_GET['ajax_read'])) {
    $id = (int)$_GET['ajax_read'];
    try {
        $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

// Search functionality
$search = $_GET['search'] ?? '';
$where = '';
$params = [];

if (!empty($search)) {
    $where = "WHERE name LIKE ? OR email LIKE ? OR message LIKE ?";
    $search_param = "%$search%";
    $params = [$search_param, $search_param, $search_param];
}

// Fetch messages with pagination
try {
    $count_sql = "SELECT COUNT(*) FROM contact_messages $where";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_filtered = $stmt->fetchColumn();
    $total_pages = ceil($total_filtered / $messages_per_page);
    
    $sql = "SELECT * FROM contact_messages $where ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $stmt = $pdo->prepare($sql);
    
    if (!empty($search)) {
        $stmt->execute([...$params, $messages_per_page, $offset]);
    } else {
        $stmt->bindValue(1, $messages_per_page, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Failed to load messages: " . $e->getMessage();
    $messages = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | Admin</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght=400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/messages.css">
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <h1>Messages</h1>
                <p>Manage customer inquiries</p>
            </div>
            <div class="header-right">
                <a href="dashboard.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i>
                    Dashboard
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-compact">
            <div class="stat-item">
                <div class="stat-value"><?= $total_messages ?? 0 ?></div>
                <div class="stat-label">Total</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= isset($total_messages) && isset($unread_count) ? $total_messages - $unread_count : 0 ?></div>
                <div class="stat-label">Read</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= $unread_count ?? 0 ?></div>
                <div class="stat-label">Unread</div>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Action Bar -->
        <div class="action-bar">
            <div class="search-bar">
                <form method="GET" action="" id="searchForm">
                    <i class="fas fa-search"></i>
                    <input type="text" 
                           name="search" 
                           placeholder="Search messages..." 
                           value="<?= htmlspecialchars($search) ?>"
                           id="searchInput">
                    <input type="hidden" name="page" value="1">
                    <?php if (!empty($search)): ?>
                        <a href="messages.php" class="clear-search">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="actions">
                <form method="POST" style="display: inline;">
                    <button type="submit" name="mark_all_read" class="btn btn-success">
                        <i class="fas fa-check-double"></i>
                        Mark All as Read
                    </button>
                </form>
            </div>
        </div>

        <!-- Messages Table -->
        <?php if (empty($messages)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No Messages Found</h3>
                <p><?= !empty($search) ? 'Try adjusting your search' : 'Customer inquiries will appear here' ?></p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="messages-table">
                    <thead>
                        <tr>
                            <th class="sender-col">Sender</th>
                            <th class="message-col">Message</th>
                            <th class="date-col">Date</th>
                            <th class="status-col">Status</th>
                            <th class="actions-col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $msg): 
                            $is_unread = isset($msg['is_read']) && $msg['is_read'] == 0;
                            $full_msg = htmlspecialchars($msg['message']);
                            $preview = strlen($full_msg) > 80 ? substr($full_msg, 0, 80) . '...' : $full_msg;
                        ?>
                            <tr class="message-row <?= $is_unread ? 'unread' : '' ?>" data-id="<?= $msg['id'] ?>">
                                <td class="sender-col">
                                    <div class="sender-info">
                                        <div class="sender-name"><?= htmlspecialchars($msg['name']) ?></div>
                                        <div class="sender-email"><?= htmlspecialchars($msg['email']) ?></div>
                                    </div>
                                </td>
                                <td class="message-col">
                                    <div class="message-preview"><?= nl2br($preview) ?></div>
                                </td>
                                <td class="date-col">
                                    <div class="message-date"><?= date('M j, Y', strtotime($msg['created_at'])) ?></div>
                                    <div class="message-time"><?= date('g:i A', strtotime($msg['created_at'])) ?></div>
                                </td>
                                <td class="status-col">
                                    <?php if($is_unread): ?>
                                        <span class="status-badge unread">Unread</span>
                                    <?php else: ?>
                                        <span class="status-badge read">Read</span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions-col">
                                    <div class="table-actions">
                                        <?php if($is_unread): ?>
                                            <button type="button" 
                                                    class="action-btn btn-read" 
                                                    onclick="markAsRead(<?= $msg['id'] ?>)"
                                                    title="Mark as Read">
                                                <i class="fas fa-envelope-open"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button type="button" 
                                                class="action-btn btn-view" 
                                                onclick="viewMessage(<?= $msg['id'] ?>)"
                                                title="View Message">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="mailto:<?= htmlspecialchars($msg['email']) ?>?subject=Re: Your Inquiry" 
                                           class="action-btn btn-reply" 
                                           title="Reply">
                                            <i class="fas fa-reply"></i>
                                        </a>
                                        <a href="messages.php?delete=<?= $msg['id'] ?>&page=<?= $page ?>" 
                                           class="action-btn btn-delete" 
                                           title="Delete"
                                           onclick="return confirm('Delete message from ' + '<?= addslashes($msg['name']) ?>' + '?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <div class="page-info">
                    Showing <?= count($messages) ?> of <?= $total_filtered ?> messages
                </div>
                <div class="page-controls">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="page-btn">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 1 && $i <= $page + 1)): ?>
                            <a href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                               class="page-btn <?= $i == $page ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="page-btn">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Message Modal -->
        <div class="modal" id="messageModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Message Details</h3>
                    <button type="button" class="close-modal" onclick="closeModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body" id="modalBody">
                    <!-- Message content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // Store all messages data for the modal
        const messagesData = <?= json_encode($messages) ?>;
        
        // Mark message as read
        function markAsRead(id) {
            const row = document.querySelector(`tr[data-id="${id}"]`);
            const statusBadge = row.querySelector('.status-badge');
            const readBtn = row.querySelector('.btn-read');
            
            // Update UI immediately
            row.classList.remove('unread');
            if (statusBadge) {
                statusBadge.textContent = 'Read';
                statusBadge.classList.remove('unread');
                statusBadge.classList.add('read');
            }
            if (readBtn) readBtn.remove();
            
            // Send AJAX request
            fetch('messages.php?ajax_read=' + id)
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        console.error('Failed to mark as read:', data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        // View full message in modal
        function viewMessage(id) {
            // Find the message data
            const message = messagesData.find(msg => msg.id == id);
            
            if (!message) {
                alert('Message not found');
                return;
            }
            
            const date = new Date(message.created_at);
            const formattedDate = date.toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric' 
            });
            const formattedTime = date.toLocaleTimeString('en-US', { 
                hour: 'numeric', 
                minute: '2-digit',
                hour12: true 
            });
            
            const modalBody = document.getElementById('modalBody');
            modalBody.innerHTML = `
                <div class="modal-sender">
                    <div class="modal-sender-name">${escapeHtml(message.name)}</div>
                    <div class="modal-sender-email">${escapeHtml(message.email)}</div>
                    <div class="modal-date">${formattedDate} at ${formattedTime}</div>
                </div>
                <div class="modal-message">
                    ${escapeHtml(message.message).replace(/\n/g, '<br>')}
                </div>
                <div class="modal-actions">
                    <a href="mailto:${escapeHtml(message.email)}?subject=Re: Your Inquiry" class="btn btn-primary">
                        <i class="fas fa-reply"></i>
                        Reply via Email
                    </a>
                </div>
            `;
            
            // Show modal
            document.getElementById('messageModal').style.display = 'flex';
            
            // Auto-mark as read if unread
            if (message.is_read == 0) {
                markAsRead(id);
            }
        }

        // Helper function to escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Close modal
        function closeModal() {
            document.getElementById('messageModal').style.display = 'none';
        }

        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        // Click outside modal to close
        document.getElementById('messageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Search form auto-submit
        const searchInput = document.getElementById('searchInput');
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.getElementById('searchForm').submit();
            }, 500);
        });

        // Enter key to submit search
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('searchForm').submit();
            }
        });

        // Row click to view message (excluding action buttons)
        document.querySelectorAll('.message-row').forEach(row => {
            row.addEventListener('click', function(e) {
                // Don't trigger if clicking on action buttons
                if (e.target.closest('.table-actions')) {
                    return;
                }
                
                const id = this.getAttribute('data-id');
                viewMessage(id);
            });
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + F to focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
            }
            
            // J/K for navigation
            if (e.key === 'j' || e.key === 'k') {
                e.preventDefault();
                const rows = document.querySelectorAll('.message-row');
                if (rows.length === 0) return;
                
                const currentRow = document.querySelector('.message-row.selected');
                let currentIndex = currentRow ? Array.from(rows).indexOf(currentRow) : -1;
                
                let newIndex;
                if (e.key === 'j') {
                    newIndex = currentIndex < rows.length - 1 ? currentIndex + 1 : 0;
                } else {
                    newIndex = currentIndex > 0 ? currentIndex - 1 : rows.length - 1;
                }
                
                // Remove previous selection
                if (currentRow) currentRow.classList.remove('selected');
                
                // Add new selection
                rows[newIndex].classList.add('selected');
                
                // Scroll into view
                rows[newIndex].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
            
            // Enter to view selected message
            if (e.key === 'Enter') {
                const selectedRow = document.querySelector('.message-row.selected');
                if (selectedRow) {
                    const id = selectedRow.getAttribute('data-id');
                    viewMessage(id);
                }
            }
        });
    </script>
</body>
</html>