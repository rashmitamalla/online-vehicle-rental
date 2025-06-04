<?php

session_start();
include 'database.php';

var_dump($_POST); 

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_reason']) && isset($_POST['booking_id'])) {
    $booking_id = intval($_POST['booking_id']);
    $cancel_reason = $_POST['cancel_reason'];

    $stmt = $conn->prepare("UPDATE booking SET cancel_reason = ? WHERE booking_id = ?");
    $stmt->bind_param("si", $cancel_reason, $booking_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: ../../User/Php/booking_history.php?message=" . urlencode("Cancellation request sent."));
    } else {
        header("Location: ../../User/Php/booking_history.php?message=" . urlencode("Failed to send cancellation request."));
    }
    exit();
} else {
    echo "Invalid request. Required fields missing.";
    exit;
}
?>
