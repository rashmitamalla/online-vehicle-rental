<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode([]);
    exit;
}

require '../../Database/database.php'; // adjust path as needed
$username = $_SESSION['username'];

$query = "
  SELECT vehicle.vehicle_id,vehicle_image, vehicle.vehicle_model, vehicle.vehicle_price
  FROM wishlist
  INNER JOIN vehicle ON wishlist.vehicle_id = vehicle.vehicle_id
  INNER JOIN user ON wishlist.userid = user.userid
  WHERE user.username = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username); // 's' = string
$stmt->execute();
$result = $stmt->get_result();

$favorites = [];

while ($row = $result->fetch_assoc()) {
    $favorites[] = $row;
}

echo json_encode($favorites);
?>
