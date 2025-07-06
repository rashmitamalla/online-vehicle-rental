<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 0); // change to 1 temporarily for screen error view
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_custom_error.log');
error_reporting(E_ALL);

error_log("✅ update_booking_status.php started");

include 'database.php';

// Load notification handler
$notificationPath = realpath(__DIR__ . '/../User/Php/get_notifications.php');
if ($notificationPath && file_exists($notificationPath)) {
    include_once($notificationPath);
    error_log("✅ Notification file loaded");
} else {
    error_log("❌ Notification file not found at: $notificationPath");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $bid = $_POST["booking_id"] ?? null;
    $bstatus = $_POST["bstatus"] ?? null;

    error_log("📨 POST Received: booking_id = $bid, bstatus = $bstatus");

    if ($bid && $bstatus) {
        // Get current status
        $stmt_curr = $conn->prepare("SELECT bstatus, username FROM booking WHERE booking_id = ?");
        $stmt_curr->bind_param("i", $bid);
        $stmt_curr->execute();
        $stmt_curr->bind_result($currentStatus, $username);
        if (!$stmt_curr->fetch()) {
            $_SESSION['status_error'] = "Booking not found.";
            error_log("❌ Booking ID $bid not found.");
            header("Location: ../../Dashboard/Php/display_booking_detail.php");
            exit;
        }
        $stmt_curr->close();

        error_log("🔄 Attempting to change status from '$currentStatus' to '$bstatus'");

        // Define allowed transitions
        $allowedTransitions = [
            'pending' => ['approved', 'denied', 'cancelled'],
            'approved' => ['cancelled', 'denied', 'completed'],
            'denied' => [],
            'cancelled' => [],
            'completed' => [],
        ];

        $currentStatusLower = strtolower($currentStatus);
        $newStatusLower = strtolower($bstatus);

        if (!in_array($newStatusLower, $allowedTransitions[$currentStatusLower])) {
            $_SESSION['status_error'] = "Invalid status transition from '$currentStatus' to '$bstatus'.";
            error_log("🚫 Invalid transition: $currentStatus ➝ $bstatus");
            header("Location: ../../Dashboard/Php/display_booking_detail.php");
            exit;
        }

        // Proceed with update
        $stmt = $conn->prepare("UPDATE booking SET bstatus = ? WHERE booking_id = ?");
        $stmt->bind_param("si", $bstatus, $bid);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            error_log("✅ Booking status updated to $bstatus");

            // Send notification if function exists
            $msg = match ($newStatusLower) {
                'approved' => "Your booking has been approved.",
                'denied' => "Your booking  has been denied.",
                'cancelled' => "Your booking was cancelled.",
                'completed' => "Your booking  is completed. Please <a  href='rate_vehicle.php?booking_id=$bid'>rate vehicle</a>",
                default => "Your booking #$bid status changed to $bstatus.",
            };

            if (function_exists('sendNotificationByUsername')) {
                sendNotificationByUsername($username, $msg);
                error_log("📬 Notification sent to $username");
            } else {
                error_log("❗ Notification function not found");
            }

            $_SESSION['status_success'] = "Booking status updated & user notified.";
        } else {
            error_log("⚠️ No rows updated for booking_id: $bid");
            $_SESSION['status_error'] = "No changes made to booking status.";
        }

        header("Location: ../../Dashboard/Php/display_booking_detail.php");
        exit;
    }
} else {
    error_log("❌ Invalid request method: " . $_SERVER["REQUEST_METHOD"]);
    http_response_code(405);
    echo "Method Not Allowed";
}
?>

<Style>
a{
    color:black !important;
}
</Style>
