<?php
// test.php
include '../../Database/database.php';

// Fetch all users for the dropdown
$users = [];
$result = $conn->query("SELECT userid, username FROM user ORDER BY username ASC");
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $users[] = $row;
  }
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Send Notification Test</title>
</head>

<body>
  <h2>Send Notification</h2>
  include 'Header.php'
  <form method="post" action="get_notifications.php">
    <input type="hidden" name="send_notification" value="1">

    <label for="username">Select User:</label>
    <select name="username" id="username" required>
      <option value="">-- Select a user --</option>
      <?php foreach ($users as $user): ?>
        <option value="<?php echo htmlspecialchars($user['username']); ?>">
          <?php echo htmlspecialchars($user['username']) . " (ID: " . $user['userid'] . ")"; ?>
        </option>
      <?php endforeach; ?>
    </select>
    <br><br>

    <label for="message">Message:</label>
    <input type="text" name="message" id="message" placeholder="Enter message" required>
    <br><br>

    <button type="submit">Send Notification</button>
  </form>
</body>

</html>