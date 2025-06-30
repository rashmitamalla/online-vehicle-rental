<?php
session_start();
include '../../Database/database.php';

/** @var mysqli $conn */

$selectedCarType = $_POST['car_type'] ?? [];
$selectedFuelType = $_POST['fuel_type'] ?? [];

$minPrice = $_POST['min_price'] ?? 1000;
$maxPrice = $_POST['max_price'] ?? 50000;

$pickup_date = $_POST['pickup_date'] ?? '';
$pickup_time = $_POST['pickup_time'] ?? '';
$return_date = $_POST['return_date'] ?? '';
$return_time = $_POST['return_time'] ?? '';

$pickup_datetime = $return_datetime = '';
$availableVehicleIds = [];
$where = ["v.vehicle_price BETWEEN $minPrice AND $maxPrice"];

// Car type filter
if (!empty($selectedCarType) && !in_array("All", $selectedCarType)) {
    $types = array_map(fn($type) => "'" . mysqli_real_escape_string($conn, $type) . "'", $selectedCarType);
    $where[] = "v.vehicle_type IN (" . implode(",", $types) . ")";
}

// Fuel type filter
if (!empty($selectedFuelType) && !in_array("All", $selectedFuelType)) {
    $fuels = array_map(fn($fuel) => "'" . mysqli_real_escape_string($conn, $fuel) . "'", $selectedFuelType);
    $where[] = "v.vehicle_oil IN (" . implode(",", $fuels) . ")";
}


// Date validation only if all inputs exist
if ($pickup_date && $pickup_time && $return_date && $return_time) {
    $pickup_dt = DateTime::createFromFormat('Y-m-d H:i', "$pickup_date $pickup_time");
    $return_dt = DateTime::createFromFormat('Y-m-d H:i', "$return_date $return_time");

    if (!$pickup_dt || !$return_dt || $pickup_dt >= $return_dt) {
        echo "<p style='color:red;'>Invalid pickup and return date/time combination.</p>";
        exit;
    }

    // Check if pickup is in the past
    $now = new DateTime();
    if ($pickup_dt < $now) {
        echo "<p style='color:red;'>Pickup time cannot be in the past.</p>";
        exit;
    }

    // Check if duration is at least 2 hours
    $diffInHours = ($return_dt->getTimestamp() - $pickup_dt->getTimestamp()) / 3600;
    if ($diffInHours < 2) {
        echo "<p style='color:red;'>Booking duration must be at least 2 hours.</p>";
        exit;
    }

    // Check for available vehicles during that duration
    $pickup_datetime = $pickup_dt->format('Y-m-d H:i:s');
    $return_datetime = $return_dt->format('Y-m-d H:i:s');

    $stmt = $conn->prepare("
        SELECT vehicle_id FROM vehicle 
        WHERE vehicle_id NOT IN (
            SELECT vehicle_id FROM booking
            WHERE bstatus = 'approved' AND (
                (CONCAT(pickup_date, ' ', pickup_time) <= ? AND CONCAT(return_date, ' ', return_time) > ?) OR
                (CONCAT(pickup_date, ' ', pickup_time) < ? AND CONCAT(return_date, ' ', return_time) >= ?) OR
                (CONCAT(pickup_date, ' ', pickup_time) >= ? AND CONCAT(return_date, ' ', return_time) <= ?)
            )
        )
    ");
    $stmt->bind_param("ssssss", $return_datetime, $pickup_datetime, $return_datetime, $pickup_datetime, $pickup_datetime, $return_datetime);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $availableVehicleIds[] = $row['vehicle_id'];
    }
    $stmt->close();

    if (empty($availableVehicleIds)) {
        echo "<p style='color:red;'>No vehicles available for the selected time range.</p>";
        exit;
    }
}

// Final WHERE clause
$whereClause = "WHERE " . implode(" AND ", $where);

// Final SQL
$sql = "SELECT v.*, AVG(r.rating) AS avg_rating 
        FROM vehicle v 
        LEFT JOIN ratings r ON v.vehicle_id = r.vehicle_id 
        $whereClause 
        GROUP BY v.vehicle_id";

$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        if (!$pickup_datetime || in_array($row['vehicle_id'], $availableVehicleIds)) {
            include 'vehicle_card_template.php';
        }
    }
} else {
    echo "<p>No vehicles found.</p>";
}
?>
