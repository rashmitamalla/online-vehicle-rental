<?php
session_start();
include_once(__DIR__ . "/../../Database/database.php");

// Function to send notification by username
function sendNotificationByUsername($username, $message)
{
    global $conn;


    $userid = null;  // initialize

    // Get userid from username
    $stmtUser = $conn->prepare("SELECT userid FROM user WHERE username = ?");
    $stmtUser->bind_param("s", $username);
    $stmtUser->execute();
    $stmtUser->bind_result($userid);
    $stmtUser->fetch();
    $stmtUser->close();

    if (!$userid) {
        error_log("User not found for username: $username");
        return false;
    }

    $stmt = $conn->prepare("INSERT INTO notifications (user_id, username, message, is_read, created_at) VALUES (?, ?, ?, 0, NOW())");
    $stmt->bind_param("iss", $userid, $username, $message);

    if (!$stmt->execute()) {
        error_log("Failed to send notification: " . $stmt->error);
        $stmt->close();
        return false;
    }

    $stmt->close();
    return true;
}

// If you want to send a notification via POST (optional)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_notification'])) {
    if (isset($_POST['username']) && isset($_POST['message'])) {
        $user = trim($_POST['username']);
        $msg = trim($_POST['message']);
        $success = sendNotificationByUsername($user, $msg);
        echo $success ? "Notification sent successfully." : "Failed to send notification.";
    } else {
        echo "Missing username or message.";
    }
    exit; // Stop here if this is a send notification POST request
}

// Otherwise, display last 5 notifications for logged-in user
if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
    $username = $_SESSION['username'];

    // Use prepared statement to avoid SQL injection
    $stmt = $conn->prepare("SELECT message FROM notifications WHERE username = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
    echo "<div class='notification-item'>" . strip_tags($row['message'], '<a>') . "</div>";
        }
    } else {
        echo "<div class='notification-item'>No notifications found.</div>";
    }

    $stmt->close();
} else {
    echo "<div class='notification-item'>User not logged in.</div>";
}
