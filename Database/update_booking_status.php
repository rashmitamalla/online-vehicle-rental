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
        $stmt = $conn->prepare("UPDATE booking SET bstatus = ? WHERE booking_id = ?");
        $stmt->bind_param("si", $bstatus, $bid);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $stmt_user = $conn->prepare("SELECT username FROM booking WHERE booking_id = ?");
            $stmt_user->bind_param("i", $bid);
            $stmt_user->execute();
            $stmt_user->bind_result($username);
            $stmt_user->fetch();
            $stmt_user->close();

            error_log("👤 Username fetched: $username");

            $msg = match ($bstatus) {
                'approved' => "Your booking #$bid has been approved.",
                'denied' => "Your booking #$bid has been denied.",
                'cancelled' => "Your booking #$bid was cancelled.",
                'completed' => "Your booking #$bid is completed. Please <a href='rate_vehicle.php?booking_id=$bid'>rate vehicle</a>",
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
    } else {
        error_log("❌ Missing booking_id or bstatus in POST");
        $_SESSION['status_error'] = "Invalid booking ID or status.";
    }

    header("Location: ../../Dashboard/Php/display_booking_detail.php");
    exit;
} else {
    error_log("❌ Invalid request method: " . $_SERVER["REQUEST_METHOD"]);
    http_response_code(405);
    echo "Method Not Allowed";
}
