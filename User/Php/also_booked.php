<?php
include '../../Database/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username'])) {
    echo "<p>Login required.</p>";
    return;
}

$current_user = $_SESSION['username'];

// Step 1: Get current user's booked vehicles
$sql = "SELECT DISTINCT vehicle_id FROM booking WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $current_user);
$stmt->execute();
$res = $stmt->get_result();

$current_vehicles = [];
while ($row = $res->fetch_assoc()) {
    $current_vehicles[] = $row['vehicle_id'];
}
$current_set = array_unique($current_vehicles);

if (empty($current_set)) {
    echo "<p>You have no bookings yet to generate recommendations.</p>";
    return;
}

// Step 2: Get all other users' bookings
$sql = "SELECT username, vehicle_id FROM booking WHERE username != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $current_user);
$stmt->execute();
$res = $stmt->get_result();

$user_bookings = [];
while ($row = $res->fetch_assoc()) {
    $user_bookings[$row['username']][] = $row['vehicle_id'];
}

// Step 3: Calculate Jaccard similarity
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


$recommended_vehicles = [];

// Add already booked vehicles with a base score (e.g., 1)
foreach ($current_set as $vid) {
    $recommended_vehicles[$vid] = 1; // fixed score for booked vehicles
}

// Add other recommended vehicles weighted by similarity
foreach ($similar_users as $other_user => $similarity) {
    foreach ($user_bookings[$other_user] as $vid) {
        // Add similarity score, even if it's booked already
        if (!isset($recommended_vehicles[$vid])) {
            $recommended_vehicles[$vid] = 0;
        }
        $recommended_vehicles[$vid] += $similarity;
    }
}

arsort($recommended_vehicles);

// Step 5: Limit to top 4 recommendations (includes booked + similar)
$recommended_ids = array_keys($recommended_vehicles);
$recommended_ids = array_slice($recommended_ids, 0, 4);


if (empty($recommended_ids)) {
    echo "<p>No personalized recommendations found.</p>";
    return;
}

// Prepare SQL for fetching recommended vehicles with avg rating
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

// Output recommended vehicles with inline HTML
echo "<h1 style='padding:20px ; '>Recommended for You</h1>";
echo "<div class='recommended-container' style='display:flex;gap:20px;flex-wrap:wrap; padding:20px;'>";

while ($row = $result->fetch_assoc()) {
    // Handle missing avg_rating gracefully
    $rating = isset($row['avg_rating']) && $row['avg_rating'] !== null ? round($row['avg_rating'], 1) : 0.0;
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);

    ?>
    <div class="vehicle-card" style="background:#fff; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.1); width:280px; overflow:hidden; display:flex; flex-direction: column;">
      <div class="vehicle-img" style="width:100%; height:160px; overflow:hidden;">
        <img src="../../image/<?php echo htmlspecialchars($row['vehicle_image']); ?>" alt="Vehicle Image" style="width:100%; height:160px; object-fit: cover;">
      </div>
      <div class="vehicle-body" style="padding:16px; flex-grow:1; display:flex; flex-direction: column; justify-content: space-between;">
        <h3 class="vehicle-title" style="font-size:1.1rem; font-weight:600; color:#333; margin-bottom:8px;">
          <a href="Book.php?vehicle_id=<?php echo $row['vehicle_id']; ?>" style="color:#007bff; text-decoration:none;">
            <?php echo htmlspecialchars($row['vehicle_model']); ?>
          </a>
          <span class="vehicle-type" style="background:#eee; color:#555; padding:2px 6px; font-size:0.75rem; border-radius:5px; margin-left:10px;">
            <?php echo htmlspecialchars($row['vehicle_type']); ?>
          </span>
        </h3>
        <div class="vehicle-meta" style="font-size:0.9rem; color:#666; display:flex; gap:10px; flex-wrap:wrap; margin-bottom:10px;">
          <span><i class="fa fa-users"></i> <?php echo htmlspecialchars($row['vehicle_people']); ?> Seats</span>
          <span><i class="fa fa-cog"></i> <?php echo htmlspecialchars($row['vehicle_oil']); ?></span>
          <span><i class="fa fa-snowflake"></i> A/C</span>
        </div>
        <div class="vehicle-rating" style="margin:10px 0; font-size:0.9rem; color:#555; display:flex; align-items:center;">
          <?php
          for ($i = 0; $i < $fullStars; $i++) echo '<i class="fa fa-star" style="color: gold;"></i>';
          if ($halfStar) echo '<i class="fa fa-star-half-alt" style="color: gold;"></i>';
          for ($i = 0; $i < $emptyStars; $i++) echo '<i class="fa-regular fa-star" style="color: gold;"></i>';
          ?>
          <span class="rating-score" style="margin-left:6px; font-weight:bold; color:#444;">
            <?php echo number_format($rating, 1); ?>
          </span>
        </div>
        <div class="vehicle-price" style="font-size:1rem; font-weight:bold; color:#000; margin-bottom:12px;">
          <strong>Rs <?php echo number_format($row['vehicle_price']); ?></strong>/day
        </div>

        <a href="Book.php?vehicle_id=<?php echo $row['vehicle_id']; ?>" class="recommend-btn" style="text-align:center; background-color:#e7e7e7; color:black; padding:10px 0; border-radius:6px; text-decoration:none; font-weight:600; transition:background-color 0.3s ease;">
          Book Now
        </a>
      </div>
    </div>
    <?php
}

echo "</div>";
?>
