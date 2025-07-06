<?php
session_start();
include '../../Database/database.php';

if (!isset($_SESSION['username'])) {
    echo "<div class='notification-item'>User not logged in.</div>";
    exit;
}

$username = $_SESSION['username'];

$stmt = $conn->prepare("SELECT message FROM notifications WHERE username = ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

$approvalNotificationFound = false;
$approvalMessagePattern = "/booking has been approved/i";

echo "<div id='notifications-container'>";
while ($row = $result->fetch_assoc()) {
    $msg = strip_tags($row['message'], '<a>');

    echo "<div class='notification-item'>$msg</div>";

    if (preg_match($approvalMessagePattern, $msg)) {
        $approvalNotificationFound = true;
    }
}
echo "</div>";
?>

<?php if ($approvalNotificationFound): ?>
    <form action="select_payment.php" method="get">
        <button type="submit" style="margin-top:15px; padding:10px 20px; font-weight:bold; cursor:pointer;">
            Select Payment Method
        </button>
    </form>
<?php endif; ?>
