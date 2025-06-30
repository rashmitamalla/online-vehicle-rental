<?php
include '../../Database/database.php';

if (!isset($_GET['vehicle_id'])) {
    echo "<p>Vehicle not specified.</p>";
    return;
}

$current_vehicle_id = intval($_GET['vehicle_id']);
$log = [];

$log[] = "========== Also Booked This [" . date("Y-m-d H:i:s") . "] ==========";

// Step 1: Get all users who booked the current vehicle
$sqlA = "SELECT DISTINCT username FROM booking WHERE vehicle_id = ?";
$stmtA = $conn->prepare($sqlA);
$stmtA->bind_param("i", $current_vehicle_id);
$stmtA->execute();
$resultA = $stmtA->get_result();

$currentUsers = [];
while ($row = $resultA->fetch_assoc()) {
    $currentUsers[] = $row['username'];
}
$stmtA->close();

$log[] = "Current vehicle users: " . implode(", ", $currentUsers);

if (empty($currentUsers)) {
    echo "<p>No users found who booked this vehicle.</p>";
    $log[] = "❌ No users found who booked this vehicle.";
    file_put_contents(__DIR__ . '/custom_error_log.txt', implode("\n", $log) . "\n\n", FILE_APPEND);
    return;
}

// Step 2: Get all vehicles booked by those users (excluding the current vehicle)
$placeholders = implode(",", array_fill(0, count($currentUsers), "?"));
$types = str_repeat("s", count($currentUsers));

$sqlB = "SELECT DISTINCT vehicle_id, username FROM booking WHERE username IN ($placeholders)";
$stmtB = $conn->prepare($sqlB);

// bind params dynamically - you might need helper for this in older PHP versions
$stmtB->bind_param($types, ...$currentUsers);
$stmtB->execute();
$resultB = $stmtB->get_result();

$vehicleUserMap = [];
while ($row = $resultB->fetch_assoc()) {
    $vid = $row['vehicle_id'];
    $uname = $row['username'];
    if ($vid == $current_vehicle_id) continue;

    if (!isset($vehicleUserMap[$vid])) {
        $vehicleUserMap[$vid] = [];
    }
    $vehicleUserMap[$vid][] = $uname;
}
$stmtB->close();

if (empty($vehicleUserMap)) {
    echo "<p>No similar bookings found.</p>";
    $log[] = "❌ No other vehicles booked by the same users.";
    file_put_contents(__DIR__ . '/custom_error_log.txt', implode("\n", $log) . "\n\n", FILE_APPEND);
    return;
}

// Step 3: Compute Jaccard similarity
$similarity = [];

foreach ($vehicleUserMap as $vid => $userList) {
    // Calculate intersection and union
    $intersection = array_intersect($currentUsers, $userList);
    $union = array_unique(array_merge($currentUsers, $userList));

    // Jaccard similarity formula = |intersection| / |union|
    $score = count($intersection) / count($union);

    $similarity[$vid] = $score;

    $log[] = "Vehicle $vid: intersection(" . count($intersection) . ") / union(" . count($union) . ") = " . round($score, 3);
}

// Step 4: Sort vehicles by similarity score descending
arsort($similarity);

// Get top 4 similar vehicles to recommend
$topVehicleIds = array_slice(array_keys($similarity), 0, 4);

$log[] = "Top recommended vehicle IDs: " . implode(", ", $topVehicleIds);

// Step 5: Fetch vehicle details for recommendations
if (!empty($topVehicleIds)) {
    $placeholders = implode(",", array_fill(0, count($topVehicleIds), "?"));
    $types = str_repeat("i", count($topVehicleIds));

    $sqlC = "SELECT v.*, AVG(r.rating) AS avg_rating
             FROM vehicle v
             LEFT JOIN ratings r ON v.vehicle_id = r.vehicle_id
             WHERE v.vehicle_id IN ($placeholders)
             GROUP BY v.vehicle_id";

    $stmtC = $conn->prepare($sqlC);
    $stmtC->bind_param($types, ...$topVehicleIds);
    $stmtC->execute();
    $resultC = $stmtC->get_result();

    while ($row = $resultC->fetch_assoc()) {
        include 'recommend_template.php';
    }

    $stmtC->close();
} else {
    echo "<p>No recommendations found.</p>";
    $log[] = "❌ No vehicles met the similarity criteria.";
}

// Save logs to file
$logFile = __DIR__ . '/../../database/php_custom_error.log';

file_put_contents($logFile, implode("\n", $log) . "\n\n", FILE_APPEND | LOCK_EX);

?>
