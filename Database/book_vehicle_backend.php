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
session_start(); // Required for $_SESSION to work
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
    $bstatus = "pending";


    // Combine pickup and return into datetime strings
    $new_pickup = $pickup_date . ' ' . $pickup_time;
    $new_return = $return_date . ' ' . $return_time;
    // Check if vehicle is already booked (Approved status only)
    $stmt = $conn->prepare("
    SELECT * FROM booking
    WHERE vehicle_id = ?
    AND bstatus = 'approved'
    AND CONCAT(pickup_date, ' ', pickup_time) < ?
    AND CONCAT(return_date, ' ', return_time) > ?
");
    $stmt->bind_param("sss", $vid, $return_date, $pickup_date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $conflicts = [];
        while ($row = $result->fetch_assoc()) {
            $conflicts[] = $row['pickup_date'] . " to " . $row['return_date'];
        }
        $conflict_dates = implode(", ", $conflicts);
        $_SESSION['booking_error'] = "Vehicle is unavailable on these dates: $conflict_dates. Please choose another vehicle or different dates.";
        header("Location: ../../User/Php/book.php?vehicle_id=" . urlencode($vid));
        exit;
    }

    // Convert to DateTime
    $pickup_dt = DateTime::createFromFormat('Y-m-d H:i', $pickup_date . ' ' . $pickup_time);
    $return_dt = DateTime::createFromFormat('Y-m-d H:i', $return_date . ' ' . $return_time);

    // Check validity
    if (!$pickup_dt || !$return_dt || $return_dt <= $pickup_dt) {
        echo "<script>alert('Invalid pickup or return date/time'); window.history.back();</script>";
        exit;
    }

    // Check if booking is at least 1 day ahead
    $today = new DateTime();
    $today->setTime(0, 0);
    $pickup_check = clone $pickup_dt;
    $pickup_check->setTime(0, 0);

    if ($pickup_check <= $today) {
        $_SESSION['booking_error'] = "Booking must be made at least 1 day in advance due to maintenance.";
        header("Location: ../../User/Php/book.php?vehicle_id=" . urlencode($vid));
        exit;
    }

    // Duration in hours
    $diff_hours = ($return_dt->getTimestamp() - $pickup_dt->getTimestamp()) / 3600;
    if ($diff_hours < 2) {
        echo "<script>alert('Minimum booking time is 2 hours'); window.history.back();</script>";
        exit;
    }

    // // Price Calculation
    // $full_days = floor($diff_hours / 24);
    // $remaining_hours = $diff_hours % 24;
    // $hourly_rate = $vehicle_price / 24;
    // $total_price = ($full_days * $vehicle_price) + ($remaining_hours * $hourly_rate);



    if ($diff_hours < 2) {
        $total_price = 0;
        $booking_type = "invalid";
    } elseif ($diff_hours <= 24) {
        $total_price = $vehicle_price; // minimum 1 day charge
        $booking_type = "hourly(min-1day)";
    } else {
        $full_days = floor($diff_hours / 24);
        $remaining_hours = $diff_hours % 24;
        $hourly_rate = $vehicle_price / 24;
        $total_price = ($full_days * $vehicle_price) + ($remaining_hours * $hourly_rate);
        $total_price = round($total_price / 10) * 10;
        $booking_type = ($remaining_hours > 0) ? "daily+hourly" : "daily";
    }




    // Insert booking
    $stmt = $conn->prepare("INSERT INTO booking
        (fullname, username, email, number, vehicle_id, pickup_date, pickup_time, return_date, return_time, pickup_location, bstatus, vehicle_price, total_price, vehicle_number)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssssssss", $fullname, $username, $email, $number, $vid, $pickup_date, $pickup_time, $return_date, $return_time, $pickup_location, $bstatus, $vehicle_price, $total_price, $vehicle_number);

    if ($stmt->execute()) {
        $_SESSION['booking_success'] = "Booking successful!";
        header("Location: ../../User/Php/Book.php?vehicle_id=" . urlencode($vid));
        exit;
    } else {
        $_SESSION['booking_error'] = "Database error: " . htmlspecialchars($stmt->error);
        header("Location: ../../User/Php/Book.php?vehicle_id=" . urlencode($vid));
        exit;
    }
} else {
    header("Location: ../../User/Php/Book.php");
    exit;
}