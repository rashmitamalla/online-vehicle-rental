<?php
session_start();
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize and validate inputs
    $fullname = trim($_POST["fullname"]);
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $number = trim($_POST["number"]);
    $vid = trim($_POST["vehicle_id"]);
    $vehicle_number = trim($_POST["vehicle_number"]);
    $pickup_date = $_POST["pickup_date"];
    $pickup_time = $_POST["pickup_time"];
    $return_date = $_POST["return_date"];
    $return_time = $_POST["return_time"];
    $pickup_location = trim($_POST["pickup_location"]);
    $vehicle_price = floatval($_POST["vehicle_price"]);
    $bstatus = "Approved";


    // if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //     include '../../Database/database.php';
    //     session_start();

    //     $bid = $_POST['booking_id'];
    //     $cancel_reason = trim($_POST['cancel_reason']);
    //     $bstatus = 'Cancelled';  // you can also use 'cancellation_requested' if preferred

    //     // Validate inputs
    //     if (empty($bid) || empty($cancel_reason)) {
    //         $_SESSION['cancel_error'] = "Invalid cancellation request.";
    //         header("Location: ../../User/Php/display_booking.php");
    //         exit;
    //     }

    //     // Fine charge amount
    //     $fine_charge = 500;

    //     // Prepare update statement
    //     $stmt = $conn->prepare("UPDATE booking SET bstatus=?, cancel_reason=?, fine_charge=? WHERE booking_id=?");
    //     if (!$stmt) {
    //         $_SESSION['cancel_error'] = "Database error: " . $conn->error;
    //         header("Location: ../../User/Php/display_booking.php");
    //         exit;
    //     }

    //     $stmt->bind_param("ssii", $bstatus, $cancel_reason, $fine_charge, $bid);
    //     $stmt->execute();

    //     if ($stmt->affected_rows > 0) {
    //         $_SESSION['cancel_success'] = "Booking cancellation is successful. A fine of ₹500 has been applied.";
    //     } else {
    //         $_SESSION['cancel_error'] = "Cancellation failed. Please try again with a valid reason.";
    //     }

    //     $stmt->close();
    //     $conn->close();

    //     // Redirect back to booking details page
    //     header("Location: ../../User/Php/display_booking.php");
    //     exit;
    // }
    // Check for buffer conflict
    $stmt = $conn->prepare("
        SELECT * FROM booking
        WHERE vehicle_id = ?
        AND (
            DATE_SUB(pickup_date, INTERVAL 1 DAY) <= ?
            AND DATE_ADD(return_date, INTERVAL 1 DAY) >= ?
        )
        AND bstatus = 'Approved'
    ");
    $stmt->bind_param("sss", $vid, $return_date, $pickup_date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Collect all conflicting bookings
        $conflicts = [];
        while ($row = $result->fetch_assoc()) {
            $conflicts[] = $row['pickup_date'] . " to " . $row['return_date'];
        }
        $conflict_dates = implode(", ", $conflicts);

        $_SESSION['booking_error'] = "Vehicle is unavailable on these dates: $conflict_dates. Please choose another vehicle or different dates.";
        header("Location: ../../User/Php/book.php?vehicle_id=" . urlencode($vid));
        exit;
    }
    // Convert pickup and return datetime strings to PHP DateTime objects
    $pickup_dt = DateTime::createFromFormat('Y-m-d H:i', $pickup_date . ' ' . $pickup_time);
    $return_dt = DateTime::createFromFormat('Y-m-d H:i', $return_date . ' ' . $return_time);

    // Validate dates
    if (!$pickup_dt || !$return_dt || $return_dt <= $pickup_dt) {
        echo "<script>alert('Invalid pickup or return date/time'); window.history.back();</script>";
        exit;
    }

    // Block booking for today or earlier (due to maintenance)
    $today = new DateTime();
    $today->setTime(0, 0);
    $pickup_check = clone $pickup_dt;
    $pickup_check->setTime(0, 0);

    if ($pickup_check <= $today) {
        $_SESSION['booking_error'] = "Booking must be made at least 1 day in advance due to maintenance.";
        header("Location: ../../User/Php/book.php?vehicle_id=" . urlencode($vid));
        exit;
    }

    // Calculate duration in hours
    $diff_seconds = $return_dt->getTimestamp() - $pickup_dt->getTimestamp();
    $diff_hours = $diff_seconds / 3600;

    if ($diff_hours < 2) {
        echo "<script>alert('Minimum booking time is 2 hours'); window.history.back();</script>";
        exit;
    }

    // Calculate price
    $full_days = floor($diff_hours / 24);
    $remaining_hours = $diff_hours % 24;
    $hourly_rate = $vehicle_price / 24;
    $total_price = ($full_days * $vehicle_price) + ($remaining_hours * $hourly_rate);

    $booking_type = $full_days > 0 ? "daily+hourly" : "hourly";

    // Insert booking
    $stmt = $conn->prepare("INSERT INTO booking
        (fullname, username, email, number, vehicle_id, pickup_date, pickup_time, return_date, return_time, pickup_location, bstatus, vehicle_price, total_price, vehicle_number)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssssssss", $fullname, $username, $email, $number, $vid, $pickup_date, $pickup_time, $return_date, $return_time, $pickup_location, $bstatus, $vehicle_price, $total_price, $vehicle_number);

    if ($stmt->execute()) {
        $_SESSION['booking_success'] = "Booking successful!";
        header("Location: ../../User/Php/book.php?vehicle_id=" . urlencode($vid));
        exit;
    } else {
        $_SESSION['booking_error'] = "Database error: " . htmlspecialchars($stmt->error);
        header("Location: ../../User/Php/book.php?vehicle_id=" . urlencode($vid));
        exit;
    }
} else {
    // Invalid request method
    header("Location: ../../User/Php/book.php");
    exit;
}
