<?php
session_start();
include '../../Database/database.php';

// Check if user is logged in and username is set
if (empty($_SESSION['username'])) {
    die("User not logged in.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'] ?? null;
    $vehicle_id = $_POST['vehicle_id'] ?? null;
    $rating = $_POST['rating'] ?? null;
    $feedback = $_POST['feedback'] ?? '';
    $username = $_SESSION['username'];

    if (!$booking_id || !$vehicle_id || !$rating || !$username) {
        die("Required fields missing.");
    }

    // Prepare and execute user query to get full name
    $user_sql = "SELECT firstname, middlename, lastname FROM user WHERE username = ?";
    $stmt_user = $conn->prepare($user_sql);
    if (!$stmt_user) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt_user->bind_param("s", $username);
    if (!$stmt_user->execute()) {
        die("Execute failed: " . $stmt_user->error);
    }
    $result_user = $stmt_user->get_result();

    if ($user = $result_user->fetch_assoc()) {
        // Collect name parts, ignore empty ones
        $name_parts = array_filter([
            $user['firstname'],
            $user['middlename'],  // may be empty
            $user['lastname']
        ], fn($part) => !empty(trim($part)));

        // Join with space
        $full_name = implode(' ', $name_parts);
    } else {
        die("User not found.");
    }

    // Check if user already rated this vehicle
    $check_sql = "SELECT id FROM ratings WHERE username = ? AND vehicle_id = ?";
    $stmt_check = $conn->prepare($check_sql);
    if (!$stmt_check) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt_check->bind_param("si", $username, $vehicle_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        // Update existing rating
        $update_sql = "UPDATE ratings SET rating = ?, feedback = ?, rated_at = NOW() 
                       WHERE username = ? AND vehicle_id = ?";
        $stmt_update = $conn->prepare($update_sql);
        if (!$stmt_update) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt_update->bind_param("issi", $rating, $feedback, $username, $vehicle_id);

        if ($stmt_update->execute()) {
            echo "<script>
                alert('Rating updated successfully!');
                window.location.href = 'Home.php';
            </script>";
            exit;
        } else {
            echo "Error updating rating: " . $stmt_update->error;
        }
    } else {
        // Insert new rating
        $insert_sql = "INSERT INTO ratings (username, full_name, booking_id, vehicle_id, rating, feedback, rated_at)
                       VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt_insert = $conn->prepare($insert_sql);
        if (!$stmt_insert) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt_insert->bind_param("ssiiis", $username, $full_name, $booking_id, $vehicle_id, $rating, $feedback);

        if ($stmt_insert->execute()) {
            echo "<script>
                alert('Rating submitted successfully!');
                window.location.href = 'Home.php';
            </script>";
            exit;
        } else {
            error_log("Insert failed: " . $stmt_insert->error);  // Log to server error log
            echo "Error inserting rating: " . $stmt_insert->error;
        }
    }
} else {
    die("Invalid request method.");
}
