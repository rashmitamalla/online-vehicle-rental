<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

include 'database.php';

$vehicles = [];

// Get vehicle_id from URL or set to 0 if missing
$vehicle_id = isset($_GET['vehicle_id']) ? intval($_GET['vehicle_id']) : 0;

if ($vehicle_id > 0) {
  $sql = "SELECT * FROM vehicle WHERE vehicle_id = $vehicle_id";
} else {
  $sql = "SELECT * FROM vehicle";
}

$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
  while ($row = mysqli_fetch_assoc($result)) {
    $vehicles[] = $row;
  }
}

$recommendedVehicles = [];

if (isset($_SESSION['username'])) {
  $username = $_SESSION['username'];

  // Step 1: Get vehicle details for viewed and booked vehicles
  $history_sql = "SELECT DISTINCT v.* 
                  FROM vehicle v
                  LEFT JOIN vehicle_views vv ON vv.vehicle_id = v.vehicle_id AND vv.username = ?
                  LEFT JOIN booking b ON b.vehicle_id = v.vehicle_id AND b.username = ?
                  WHERE vv.username IS NOT NULL OR b.username IS NOT NULL";
  $stmt = $conn->prepare($history_sql);
  $stmt->bind_param("ss", $username, $username);
  $stmt->execute();
  $history_result = $stmt->get_result();

  $user_history = [];
  while ($row = $history_result->fetch_assoc()) {
    $user_history[] = $row;
  }
  $stmt->close();

  // Step 2: Get all candidate vehicles excluding current vehicle_id
  $exclude_id_clause = ($vehicle_id > 0) ? "WHERE vehicle_id != $vehicle_id" : "";
  $all_sql = "SELECT * FROM vehicle $exclude_id_clause";
  $all_result = $conn->query($all_sql);
  $candidates = [];
  while ($row = $all_result->fetch_assoc()) {
    $candidates[] = $row;
  }

  // Step 3: Jaccard similarity between each candidate and user history
  $scored = [];
  foreach ($candidates as $candidate) {
    $best_similarity = 0;

    foreach ($user_history as $history_vehicle) {
      $candidate_features = [
        strtolower(trim($candidate['vehicle_type'])),
        strtolower(trim($candidate['vehicle_model'])),
        strtolower(trim($candidate['vehicle_color'])),
      ];

      $history_features = [
        strtolower(trim($history_vehicle['vehicle_type'])),
        strtolower(trim($history_vehicle['vehicle_model'])),
        strtolower(trim($history_vehicle['vehicle_color'])),
      ];

      $weights = ['vehicle_type' => 0.3, 'vehicle_model' => 0.3, 'vehicle_color' => 0.4];

      $similarity = 0;
      $similarity += ($candidate_features[0] === $history_features[0]) ? $weights['vehicle_type'] : 0;
      $similarity += ($candidate_features[1] === $history_features[1]) ? $weights['vehicle_model'] : 0;
      $similarity += ($candidate_features[2] === $history_features[2]) ? $weights['vehicle_color'] : 0;



      $intersection = array_intersect($candidate_features, $history_features);
      $union = array_unique(array_merge($candidate_features, $history_features));

      $similarity = count($intersection) / count($union);
      $best_similarity = max($best_similarity, $similarity);
    }

    $scored[] = ['vehicle' => $candidate, 'score' => $best_similarity];
  }

  // Step 4: Sort by similarity score and pick top 4
  usort($scored, function ($a, $b) {
    return $b['score'] <=> $a['score'];
  });

  $top = array_slice($scored, 0, 4);
  foreach ($top as $item) {
    $recommendedVehicles[] = $item['vehicle'];
  }

  // Step 5: Fallback if less than 4
  if (count($recommendedVehicles) < 4) {
    $excludeIds = array_column($recommendedVehicles, 'vehicle_id');
    $excludeStr = implode(',', array_map('intval', $excludeIds));
    $remaining = 4 - count($recommendedVehicles);

    $random_sql = "SELECT * FROM vehicle" .
      (count($excludeIds) > 0 ? " WHERE vehicle_id NOT IN ($excludeStr)" : "") .
      " ORDER BY RAND() LIMIT $remaining";
    $random_result = $conn->query($random_sql);
    while ($row = $random_result->fetch_assoc()) {
      $recommendedVehicles[] = $row;
    }
  }
}

?>

<style>
  .vehicle-card {
    border: 1px solid #ccc;
    padding: 10px;
    box-sizing: border-box;
    transition: transform 0.3s, box-shadow 0.3s;
  }

  .vehicle-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }
</style>

<h1 style="padding:0px 60px">Recommended For You</h1>

<div class="recommended-vehicles" style="display: flex; gap: 20px; flex-wrap: nowrap; margin-top: 20px; overflow-x: auto; padding:0px 60px">
  <?php if (!empty($recommendedVehicles)): ?>
    <?php
    // Limit to 4 vehicles max
    $vehiclesToShow = array_slice($recommendedVehicles, 0, 4);
    ?>

    <?php foreach ($vehiclesToShow as $vehicle): ?>
      <a href="Book.php?vehicle_id=<?php echo $vehicle['vehicle_id']; ?>"
        style="text-decoration: none; color: inherit; width: 23%; box-sizing: border-box;">

        <div class="vehicle-card">
          <img src="../../Admin/<?php echo htmlspecialchars($vehicle['vehicle_image']); ?>" style="width: 100%; height: auto;">
          <h3><?php echo htmlspecialchars($vehicle['vehicle_number']); ?></h3>
          <p>Type: <?php echo htmlspecialchars($vehicle['vehicle_type']); ?></p>
          <p>Model: <?php echo htmlspecialchars($vehicle['vehicle_model']); ?></p>
          <p>Price: Rs <?php echo htmlspecialchars($vehicle['vehicle_price']); ?>/day</p>
        </div>

      </a>
    <?php endforeach; ?>

  <?php else: ?>
    <p>No recommendations available at this time.</p>
  <?php endif; ?>
</div>