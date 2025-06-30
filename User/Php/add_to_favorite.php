<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
session_start();
include '../../Database/database.php';

if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$username = $_SESSION['username'];
$vehicle_id = isset($_POST['vehicle_id']) ? intval($_POST['vehicle_id']) : 0;

if ($vehicle_id === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Vehicle ID is missing']);
    exit;
}

// ✅ Get userid from username
$stmt = $conn->prepare("SELECT userid FROM user WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit;
}

$row = $result->fetch_assoc();
$userid = $row['userid'];

// ✅ Insert into wishlist
$stmt = $conn->prepare("INSERT INTO wishlist (userid, vehicle_id,username) VALUES (?, ?,?)");
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("iis", $userid, $vehicle_id, $username);

if (!$stmt->execute()) {
    if ($conn->errno === 1062) {
        echo json_encode(['status' => 'info', 'message' => 'Already in favorites']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Insert failed: ' . $stmt->error]);
    }
    exit;
}

echo json_encode(['status' => 'success', 'message' => 'Added to favorites']);
$stmt->close();
$conn->close();
