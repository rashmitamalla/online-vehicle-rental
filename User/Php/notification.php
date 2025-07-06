<?php
session_start();
include_once(__DIR__ . "/../../Database/database.php");

// Function to send notification by username (optional use)
function sendNotificationByUsername($username, $message)
{
    global $conn;

    $userid = null;
    $stmtUser = $conn->prepare("SELECT userid FROM user WHERE username = ?");
    $stmtUser->bind_param("s", $username);
    $stmtUser->execute();
    $stmtUser->bind_result($userid);
    $stmtUser->fetch();
    $stmtUser->close();

    if (!$userid) return false;

    $stmt = $conn->prepare("INSERT INTO notifications (user_id, username, message, is_read, created_at) VALUES (?, ?, ?, 0, NOW())");
    $stmt->bind_param("iss", $userid, $username, $message);

    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Handle POST request to send notification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_notification'])) {
    if (!empty($_POST['username']) && !empty($_POST['message'])) {
        $user = trim($_POST['username']);
        $msg = trim($_POST['message']);
        $success = sendNotificationByUsername($user, $msg);
        echo $success ? "Notification sent successfully." : "Failed to send notification.";
    } else {
        echo "Missing username or message.";
    }
    exit;
}

// Fetch last 5 notifications for the logged-in user
$notifications = [];
if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $stmt = $conn->prepare("SELECT username AS user_name, message AS content, created_at AS timestamp, is_read FROM notifications WHERE username = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Notifications</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
 .flex-1 a{
  color:rgb(12, 21, 39);
 }
    </style>

</head>
<body class="bg-gray-100 p-4">


  <?php include_once(__DIR__ . "/Header.php"); ?>
  <div class="max-w-2xl mx-auto bg-white p-6 rounded shadow " style="margin-top: 20px;0px" >
    <h2 class="text-xl font-bold mb-4">Notifications</h2>

    <?php if (!empty($notifications)): ?>
      <?php foreach ($notifications as $row): ?>
        <div class="flex items-start gap-3 p-4 border-b hover:bg-gray-50">
          <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-xl font-bold text-blue-600">
            <?= strtoupper(substr($row['user_name'], 0, 1)) ?>
          </div>
          <div class="flex-1">
            <p class="text-sm text-gray-700">
              <span class="font-semibold"><?= htmlspecialchars($row['user_name']) ?></span>
              <?= ($row['content']) ?>
            </p>
            <p class="text-xs text-gray-500 mt-1"><?= date("d M Y, h:i A", strtotime($row['timestamp'])) ?></p>
          </div>
          <?php if (!$row['is_read']): ?>
            <div class="w-3 h-3 bg-blue-500 rounded-full mt-2"></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-sm text-gray-600">No notifications found.</p>
    <?php endif; ?>
  </div>
</body>
</html>
