<?php
include '../../Database/database.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$loggedIn = isset($_SESSION['username']);
$current_user = $loggedIn ? $_SESSION['username'] : null;

// Step 1: Get current user's booked vehicles
$current_vehicles = [];
if ($loggedIn) {
  $sql = "SELECT DISTINCT vehicle_id FROM booking WHERE username = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $current_user);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) {
    $current_vehicles[] = $row['vehicle_id'];
  }
}
$current_set = array_unique($current_vehicles);

// === Fallback if user not logged in or no bookings ===
if (!$loggedIn || empty($current_set)) {
  $sql = "
    SELECT v.*, COUNT(b.vehicle_id) AS booking_count,
           (SELECT AVG(rating) FROM ratings WHERE ratings.vehicle_id = v.vehicle_id) AS avg_rating
    FROM vehicle v
    LEFT JOIN booking b ON v.vehicle_id = b.vehicle_id
    GROUP BY v.vehicle_id
    ORDER BY booking_count DESC
    LIMIT 8";
  $result = $conn->query($sql);

  echo "<h2 style='padding:20px 100px;'>Recommended for You</h2>";
  echo "<div class='recommended-container' style='display:flex;gap:20px;flex-wrap:wrap;padding:0 100px;margin-bottom:30px;'>";

  while ($row = $result->fetch_assoc()) {
    // For fallback, totalUsers = booking_count minus 1 (or just booking_count)
    $totalUsers = isset($row['booking_count']) ? max(0, $row['booking_count'] - 1) : 0;

    ?>
    <div class="vehicle-card"
      style="background:#fff; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.1); overflow:hidden; display:flex; flex-wrap:wrap; width:calc(25% - 20px); margin-bottom:20px;">
      <div class="vehicle-img" style="width:100%; height:160px; overflow:hidden;">
        <img src="../../image/<?php echo htmlspecialchars($row['vehicle_image']); ?>" alt="Vehicle Image"
          style="width:100%; height:160px; object-fit: cover;">
      </div>
      <div class="vehicle-body" style="padding:16px; row-gap:4px; display:flex; flex-direction: column;">
        <p class="vehicle-title">
          <a href="Book.php?vehicle_id=<?php echo $row['vehicle_id']; ?>" style="color:#007bff; text-decoration:none;">
            <?php echo htmlspecialchars($row['vehicle_model']); ?>
          </a>
          <span class="vehicle-type"
            style="background:#eee; font-size:14px; color:#555; padding:2px 6px; border-radius:5px; margin-left:10px;">
            <?php echo htmlspecialchars($row['vehicle_type']); ?>
          </span>
        </p>

        <div class="vehicle-rating" style="margin:10px 0; font-size:0.9rem; color:#555; display:flex; align-items:center;">
          <?php
          $rating = isset($row['avg_rating']) && $row['avg_rating'] !== null ? round($row['avg_rating'], 1) : 0.0;
          $fullStars = floor($rating);
          $halfStar = ($rating - $fullStars) >= 0.5;
          $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);

          for ($i = 0; $i < $fullStars; $i++)
            echo '<i class="fa fa-star" style="color: gold;"></i>';
          if ($halfStar)
            echo '<i class="fa fa-star-half-alt" style="color: gold;"></i>';
          for ($i = 0; $i < $emptyStars; $i++)
            echo '<i class="fa-regular fa-star" style="color: gold;"></i>';
          ?>
          <span class="rating-score" style="margin-left:6px; font-weight:bold; color:#444;">
            <?php echo number_format($rating, 1); ?>
          </span>
        </div>

        <div class="vehicle-price">
          <p>Rs <?php echo number_format($row['vehicle_price']); ?>/day</p>
        </div>

        <div class="also-book">
          <span style="font-size:14px; color:#555;">
            Also booked by <?php echo $totalUsers; ?> other <?php echo ($totalUsers === 1) ? 'user' : 'users'; ?>
          </span>
        </div>

        <a href="Book.php?vehicle_id=<?php echo $row['vehicle_id']; ?>" class="recommend-btn"
          style="font-size:14px; text-align:center; background-color:#e7e7e7; color:black; padding:10px 0; border-radius:6px; text-decoration:none; font-weight:600; transition:background-color 0.3s ease;">
          Book Now
        </a>
      </div>
    </div>
    <?php
  }

  echo "</div>";
  return;
}

// === Step 2: Get other users' bookings ===
$sql = "SELECT username, vehicle_id FROM booking WHERE username != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $current_user);
$stmt->execute();
$res = $stmt->get_result();

$user_bookings = [];
while ($row = $res->fetch_assoc()) {
  $user_bookings[$row['username']][] = $row['vehicle_id'];
}

// === Step 3: Jaccard Similarity ===
$similar_users = [];
foreach ($user_bookings as $other_user => $vehicles) {
  $other_set = array_unique($vehicles);
  $intersection = array_intersect($current_set, $other_set);
  $union = array_unique(array_merge($current_set, $other_set));
  if (count($union) === 0) continue;
  $similarity = count($intersection) / count($union);
  if ($similarity > 0) {
    $similar_users[$other_user] = $similarity;
  }
}
arsort($similar_users);

// === Step 4: Recommended Vehicle Scoring ===
$recommended_vehicles = [];
foreach ($current_set as $vid) {
  $recommended_vehicles[$vid] = 1;
}
foreach ($similar_users as $other_user => $similarity) {
  foreach ($user_bookings[$other_user] as $vid) {
    if (!isset($recommended_vehicles[$vid])) {
      $recommended_vehicles[$vid] = 0;
    }
    $recommended_vehicles[$vid] += $similarity;
  }
}
arsort($recommended_vehicles);
$recommended_ids = array_keys($recommended_vehicles); // ✅ No limit now

if (empty($recommended_ids)) {
  echo "<p>No personalized recommendations found.</p>";
  return;
}

$log_file = __DIR__ . '/../../Database/php_custom_error.log';
$timestamp = date("Y-m-d H:i:s");
$recommended_count = count($recommended_ids);
$recommended_list = implode(', ', $recommended_ids);

$log_message = "[$timestamp] Recommended vehicles for user '{$current_user}' (count: {$recommended_count}): {$recommended_list}\n";
file_put_contents($log_file, $log_message, FILE_APPEND);

// === Step 5: Fetch vehicle details ===
$placeholders = implode(',', array_fill(0, count($recommended_ids), '?'));
$types = str_repeat('i', count($recommended_ids));

$sql = "SELECT *, 
           (SELECT AVG(rating) FROM ratings WHERE ratings.vehicle_id = vehicle.vehicle_id) AS avg_rating 
        FROM vehicle
        WHERE vehicle_id IN ($placeholders)";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$recommended_ids);
$stmt->execute();
$result = $stmt->get_result();

// === Step 5.1: Get also-booked counts for recommended vehicles in one query ===
if ($loggedIn) {
  $placeholders2 = implode(',', array_fill(0, count($recommended_ids), '?'));
  $types2 = str_repeat('i', count($recommended_ids)) . 's';
  $params2 = $recommended_ids;
  $params2[] = $current_user;

  $sql2 = "SELECT vehicle_id, COUNT(DISTINCT username) AS total_users
           FROM booking 
           WHERE vehicle_id IN ($placeholders2) AND username != ?
           GROUP BY vehicle_id";
  $stmt2 = $conn->prepare($sql2);
  $stmt2->bind_param($types2, ...$params2);
  $stmt2->execute();
  $res2 = $stmt2->get_result();

  $bookingCounts = [];
  while ($row2 = $res2->fetch_assoc()) {
    $bookingCounts[$row2['vehicle_id']] = $row2['total_users'];
  }
  $stmt2->close();
} else {
  $bookingCounts = [];
}

// === Step 6: Output vehicles ===
echo "<h2 style='padding:20px 100px;'>Recommended for You</h2>";
echo "<div class='recommended-container' style='display:flex;gap:20px;flex-wrap:wrap;padding:0 100px;margin-bottom:30px;'>";

$totalShown = 0;
$maxToShow = 8;

while ($row = $result->fetch_assoc()) {
  if ($totalShown >= $maxToShow) break;

  $totalUsers = $bookingCounts[$row['vehicle_id']] ?? 0;
  ?>
  <div class="vehicle-card"
    style="background:#fff; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.1); overflow:hidden; display:flex; flex-wrap:wrap; width:calc(25% - 20px); margin-bottom:20px;">
    <div class="vehicle-img" style="width:100%; height:160px; overflow:hidden;">
      <img src="../../image/<?php echo htmlspecialchars($row['vehicle_image']); ?>" alt="Vehicle Image"
        style="width:100%; height:160px; object-fit: cover;">
    </div>
    <div class="vehicle-body" style="padding:16px; row-gap:4px; display:flex; flex-direction: column;">
      <p class="vehicle-title">
        <a href="Book.php?vehicle_id=<?php echo $row['vehicle_id']; ?>" style="color:#007bff; text-decoration:none;">
          <?php echo htmlspecialchars($row['vehicle_model']); ?>
        </a>
        <span class="vehicle-type"
          style="background:#eee; font-size:14px; color:#555; padding:2px 6px; border-radius:5px; margin-left:10px;">
          <?php echo htmlspecialchars($row['vehicle_type']); ?>
        </span>
      </p>

      <div class="vehicle-rating" style="margin:10px 0; font-size:0.9rem; color:#555; display:flex; align-items:center;">
        <?php
        $rating = isset($row['avg_rating']) && $row['avg_rating'] !== null ? round($row['avg_rating'], 1) : 0.0;
        $fullStars = floor($rating);
        $halfStar = ($rating - $fullStars) >= 0.5;
        $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);

        for ($i = 0; $i < $fullStars; $i++)
          echo '<i class="fa fa-star" style="color: gold;"></i>';
        if ($halfStar)
          echo '<i class="fa fa-star-half-alt" style="color: gold;"></i>';
        for ($i = 0; $i < $emptyStars; $i++)
          echo '<i class="fa-regular fa-star" style="color: gold;"></i>';
        ?>
        <span class="rating-score" style="margin-left:6px; font-weight:bold; color:#444;">
          <?php echo number_format($rating, 1); ?>
        </span>
      </div>

      <div class="vehicle-price">
        <p>Rs <?php echo number_format($row['vehicle_price']); ?>/day</p>
      </div>

      <div class="also-book">
        <span style="font-size:14px; color:#555;">
          Also booked by <?php echo $totalUsers; ?> other <?php echo ($totalUsers === 1) ? 'user' : 'users'; ?>
        </span>
      </div>

      <a href="Book.php?vehicle_id=<?php echo $row['vehicle_id']; ?>" class="recommend-btn"
        style="font-size:14px; text-align:center; background-color:#e7e7e7; color:black; padding:10px 0; border-radius:6px; text-decoration:none; font-weight:600; transition:background-color 0.3s ease;">
        Book Now
      </a>
    </div>
  </div>
  <?php
  $totalShown++;
}

echo "</div>";
?>


<?php
include '../../Database/database.php';

$log_file = __DIR__ . '/../../Database/php_custom_error.log'; // ✅ Corrected path



$sql = "SELECT vehicle_id, GROUP_CONCAT(username ORDER BY username SEPARATOR ', ') AS booked_by_users
        FROM booking
        GROUP BY vehicle_id
        ORDER BY vehicle_id";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $log = "[" . date("Y-m-d H:i:s") . "] Booking Summary:\n";
    while ($row = $result->fetch_assoc()) {
        $log .= "Vehicle ID " . $row['vehicle_id'] . " booked by: " . $row['booked_by_users'] . "\n";
    }

    file_put_contents($log_file, $log . "\n", FILE_APPEND);
    // echo "Booking summary written to log.";
} else {
    echo "No booking data found.";
}
?>
