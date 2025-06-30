<?php

include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_reason']) && isset($_POST['booking_id'])) {
    $booking_id = mysqli_real_escape_string($conn, $_POST['booking_id']);
    $cancel_reason = mysqli_real_escape_string($conn, $_POST['cancel_reason']);

    // Update cancel_reason only (admin will handle status)
    $sql = "UPDATE booking SET cancel_reason = '$cancel_reason' WHERE booking_id = '$booking_id'";

    if (mysqli_query($conn, $sql)) {
        // Redirect to booking history with success message
        header("Location: ../../User/booking_history.php?message=" . urlencode("Cancellation request sent."));
        exit();
    } else {
        // Redirect with error message
        header("Location: ../../User/booking_history.php?message=" . urlencode("Failed to send cancellation request."));
        exit();
    }
}
?>


<?php
session_start();
include 'database.php';

// Log Setup
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_custom_error.log');
error_reporting(E_ALL);

// Log helper
function log_custom_error($msg)
{
    error_log(date("[Y-m-d H:i:s] ") . $msg . "\n", 3, __DIR__ . '/php_custom_error.log');
}

log_custom_error("✅ book.php started");

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // CSRF Token Check
    if (!isset($_POST['booking_token']) || $_POST['booking_token'] !== $_SESSION['booking_token']) {
        log_custom_error("❌ Invalid or duplicate booking submission.");
        $_SESSION['booking_error'] = "Invalid or duplicate booking submission.";
        header("Location: ../../User/Php/Book.php?vehicle_id=" . urlencode($_POST['vehicle_id'] ?? ''));
        exit;
    }
    unset($_SESSION['booking_token']);

    // Sanitize input
    $fullname = trim($_POST["fullname"]);
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $number = trim($_POST["number"]);
    $vid = (int) $_POST["vehicle_id"];
    $vehicle_number = trim($_POST["vehicle_number"]);
    $pickup_date = $_POST["pickup_date"];
    $pickup_time = $_POST["pickup_time"];
    $return_date = $_POST["return_date"];
    $return_time = $_POST["return_time"];
    $pickup_location = trim($_POST["pickup_location"]);
    $vehicle_price = floatval($_POST["vehicle_price"]);
    $bstatus = "pending";

    // Create datetime objects from inputs
    $pickup_dt = DateTime::createFromFormat('Y-m-d H:i', "$pickup_date $pickup_time");
    $return_dt = DateTime::createFromFormat('Y-m-d H:i', "$return_date $return_time");

    if (!$pickup_dt || !$return_dt) {
        log_custom_error("❌ Invalid date/time format: $pickup_date $pickup_time | $return_date $return_time");
        $_SESSION['booking_error'] = "Invalid pickup or return date/time format.";
        header("Location: ../../User/Php/Book.php?vehicle_id=$vid");
        exit;
    }

    if ($return_dt <= $pickup_dt) {
        log_custom_error("❌ Return datetime <= pickup datetime.");
        $_SESSION['booking_error'] = "Return date/time must be after pickup.";
        header("Location: ../../User/Php/Book.php?vehicle_id=$vid");
        exit;
    }

    // Conflict check query with 1-day buffer on existing bookings
    $new_pickup = $pickup_dt->format('Y-m-d H:i:s');
    $new_return = $return_dt->format('Y-m-d H:i:s');

    $stmt = $conn->prepare("
        SELECT * FROM booking
        WHERE vehicle_id = ?
        AND bstatus = 'approved'
        AND (
            DATE_SUB(CONCAT(pickup_date, ' ', pickup_time), INTERVAL 1 DAY) < ?
            AND DATE_ADD(CONCAT(return_date, ' ', return_time), INTERVAL 1 DAY) > ?
        )
    ");

    $stmt->bind_param("iss", $vid, $new_return, $new_pickup);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['booking_error'] = "Vehicle is unavailable during the selected dates (including 1-day buffer around existing bookings).";
        header("Location: ../../User/Php/Book.php?vehicle_id=" . $vid);
        exit;
    }

    // Minimum 2-hour booking duration check
    $diff_hours = ($return_dt->getTimestamp() - $pickup_dt->getTimestamp()) / 3600;
    if ($diff_hours < 2) {
        log_custom_error("❌ Booking duration < 2 hours: $diff_hours hours.");
        $_SESSION['booking_error'] = "Minimum booking duration is 2 hours.";
        header("Location: ../../User/Php/Book.php?vehicle_id=$vid");
        exit;
    }

    // Price calculation
    $full_days = floor($diff_hours / 24);
    $remaining_hours = $diff_hours % 24;
    $hourly_rate = $vehicle_price / 24;
    $total_price = round(($full_days * $vehicle_price) + ($remaining_hours * $hourly_rate), 2);

    // Insert booking
    $stmt = $conn->prepare("INSERT INTO booking 
        (fullname, username, email, number, vehicle_id, pickup_date, pickup_time, return_date, return_time, pickup_location, bstatus, vehicle_price, total_price, vehicle_number) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        log_custom_error("❌ Prepare failed: " . $conn->error);
        $_SESSION['booking_error'] = "Internal error.";
        header("Location: ../../User/Php/Book.php?vehicle_id=$vid");
        exit;
    }

    $stmt->bind_param("ssssissssssdds", $fullname, $username, $email, $number, $vid, $pickup_date, $pickup_time, $return_date, $return_time, $pickup_location, $bstatus, $vehicle_price, $total_price, $vehicle_number);

    if ($stmt->execute()) {
        log_custom_error("✅ Booking successful for vehicle_id=$vid, user=$username");
        $_SESSION['booking_success'] = "Booking successful!";
        header("Location: ../../User/Php/Book.php?vehicle_id=$vid");
        exit;
    } else {
        log_custom_error("❌ Booking insert failed: " . $stmt->error);
        $_SESSION['booking_error'] = "Database error: " . $stmt->error;
        header("Location: ../../User/Php/Book.php?vehicle_id=$vid");
        exit;
    }
} else {
    header("Location: ../../User/Php/Book.php");
    exit;
}
?>
