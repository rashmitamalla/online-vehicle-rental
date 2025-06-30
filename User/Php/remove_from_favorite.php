<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
session_start();
include '../../Database/database.php'; // adjust if needed

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

// Get userid from username
$stmt = $conn->prepare("SELECT userid FROM user WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit;
}

$userid = $result->fetch_assoc()['userid'];

// Delete from wishlist
$stmt = $conn->prepare("DELETE FROM wishlist WHERE userid = ? AND vehicle_id = ?");
$stmt->bind_param("ii", $userid, $vehicle_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['status' => 'success', 'message' => 'Removed from favorites']);
} else {
    echo json_encode(['status' => 'info', 'message' => 'Not found in favorites']);
}

$stmt->close();
$conn->close();
