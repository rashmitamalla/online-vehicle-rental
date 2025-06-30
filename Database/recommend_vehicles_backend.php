<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
include_once 'database.php';

$recommendedVehicles = [];

if (isset($_SESSION['username'])) {
  $username = $_SESSION['username'];

  // Get latest viewed vehicle by this user
  $stmt = $conn->prepare("SELECT vehicle_id FROM vehicle_views WHERE username = ? ORDER BY viewed_at DESC LIMIT 1");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $res = $stmt->get_result();

  if ($res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $vehicle_id = intval($row['vehicle_id']);

    // Get details of the viewed vehicle
    $stmt = $conn->prepare("SELECT * FROM vehicle WHERE vehicle_id = ?");
    $stmt->bind_param("i", $vehicle_id);
    $stmt->execute();
    $vehicleResult = $stmt->get_result();
    $stmt->close();

    if ($vehicleResult->num_rows > 0) {
      $current_vehicle = $vehicleResult->fetch_assoc();

      function getFeaturesSet($v)
      {
        return [
          strtolower($v['brand']),
          strtolower($v['vehicle_type']),
          strtolower($v['vehicle_color']),
          strtolower($v['vehicle_oil']),
          getPriceRangeCategory((int) $v['vehicle_price'])
        ];
      }

      function getPriceRangeCategory($price)
      {
        if ($price < 10000)
          return 'low';
        elseif ($price < 30000)
          return 'mid';
        else
          return 'high';
      }

      function jaccardSimilarity($a, $b)
      {
        $inter = array_intersect($a, $b);
        $union = array_unique(array_merge($a, $b));
        return (count($union) > 0) ? count($inter) / count($union) : 0;
      }

      // Fetch other vehicles
      $stmt = $conn->prepare("SELECT * FROM vehicle WHERE vehicle_id != ?");
      $stmt->bind_param("i", $vehicle_id);
      $stmt->execute();
      $allResult = $stmt->get_result();
      $stmt->close();

      $scored = [];
      $current_features = getFeaturesSet($current_vehicle);

      while ($v = $allResult->fetch_assoc()) {
        $features = getFeaturesSet($v);
        $similarity = jaccardSimilarity($current_features, $features);
        $scored[] = ['vehicle' => $v, 'score' => $similarity];
      }

      usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

      // Top 8 similar
      foreach ($scored as $entry) {
        if ($entry['score'] > 0) {
          $recommendedVehicles[] = $entry['vehicle'];
          if (count($recommendedVehicles) >= 8)
            break;
        }
      }

      // Fallback: random if needed
      if (count($recommendedVehicles) < 8) {
        $excluded = array_column($recommendedVehicles, 'vehicle_id');
        $excluded[] = $vehicle_id;
        $excludeStr = implode(',', $excluded);

        $left = 8 - count($recommendedVehicles);


        $user_id = null;
$stmt = $conn->prepare("SELECT userid FROM user WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $user_id = $row['userid'];
}
$stmt->close();

        // 1. Try to add vehicles from wishlist
        $queryWishlist = "SELECT v.* 
                      FROM vehicle v
                      INNER JOIN wishlist w ON v.vehicle_id = w.vehicle_id
                      WHERE w.userid = $user_id
                      AND v.vehicle_id NOT IN ($excludeStr)
                      LIMIT $left";

        $wishlistResult = $conn->query($queryWishlist);
        while ($row = $wishlistResult->fetch_assoc()) {
          $recommendedVehicles[] = $row;
          $excluded[] = $row['vehicle_id']; // track added ones
        }

        // 2. If still less than 8, add random vehicles
        if (count($recommendedVehicles) < 8) {
          $excludeStr = implode(',', $excluded);
          $left = 8 - count($recommendedVehicles);

          $queryRandom = "SELECT * FROM vehicle 
                        WHERE vehicle_id NOT IN ($excludeStr) 
                        ORDER BY RAND() 
                        LIMIT $left";

          $randomResult = $conn->query($queryRandom);
          while ($row = $randomResult->fetch_assoc()) {
            $recommendedVehicles[] = $row;
          }
        }
      }

    }
  }
}

if (empty($recommendedVehicles)) {
  // Show vehicles based on most favorited (wishlist count)
  $popular = $conn->query("SELECT v.*, COUNT(w.id) as fav_count
                           FROM vehicle v
                           LEFT JOIN wishlist w ON v.vehicle_id = w.vehicle_id
                           GROUP BY v.vehicle_id
                           ORDER BY fav_count DESC
                           LIMIT 8");
  while ($row = $popular->fetch_assoc()) {
    $recommendedVehicles[] = $row;
  }
}

?>



<style>
  .carousel-container {
    overflow: hidden;
    width: 100%;
  }

  .carousel-track {
    display: flex;
    transition: transform 0.4s ease;
    gap: 20px;
    width: fit-content;
    /* Let content decide width */
  }

  .vehicle-card-wrapper {
    text-decoration: none;
    color: inherit;
    box-sizing: border-box;
  }

  .vehicle-card {
    border: 1px solid #ccc;
    padding: 10px;
    transition: transform 0.3s, box-shadow 0.3s;
  }

  .vehicle-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }

  h2 {
    font-size: 0.9em;
    font-weight: 600;
    margin: 10px 0 5px 0;
    color: #333;
    min-height: 2.5em;
  }

  p {
    margin: 5px 0;
    font-size: 0.9em;
    color: #555;
  }

  button#leftArrow,
  button#rightArrow {
    background-color: white;
    border: 1px solid #ccc;
    font-size: 1.5rem;
    padding: 5px 10px;
    cursor: pointer;
  }
</style>


<h1 style="padding: 0 60px">Recommended For You</h1>

<div style="position: relative; padding: 0 60px;">
  <!-- Left Arrow -->
  <button id="leftArrow" style="position: absolute; left: 10px; top: 40%; z-index: 10;">&#10094;</button>

  <!-- Right Arrow -->
  <button id="rightArrow" style="position: absolute; right: 10px; top: 40%; z-index: 10;">&#10095;</button>

  <div class="carousel-container">
    <div class="carousel-track">
      <?php if (!empty($recommendedVehicles)): ?>
        <?php foreach ($recommendedVehicles as $vehicle): ?>
          <?php
          // Count how many users have favorited this vehicle
          $vid = $vehicle['vehicle_id'];
          $favQuery = "SELECT COUNT(*) AS total FROM wishlist WHERE vehicle_id = $vid";
          $favResult = $conn->query($favQuery);
          $favRow = $favResult->fetch_assoc();
          $favoriteCount = $favRow['total'];
          ?>

          <a href="Book.php?vehicle_id=<?php echo $vehicle['vehicle_id']; ?>" class="vehicle-card-wrapper">
            <div class="vehicle-card">
              <img src="../../Admin/<?php echo htmlspecialchars($vehicle['vehicle_image']); ?>"
                style="width: 100%; height: auto;">
              <h2><?php echo htmlspecialchars($vehicle['vehicle_model']); ?></h2>
              <p>Brand: <?php echo $vehicle['brand']; ?></p>
              <p>Rs <?php echo intval($vehicle['vehicle_price']); ?>/day</p>
                            <p>❤️ <?php echo $favoriteCount; ?></p>

            </div>
            
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No recommendations available at this time.</p>
      <?php endif; ?>
    </div>
  </div>

</div>
<script>
  const track = document.querySelector('.carousel-track');
  const leftArrow = document.getElementById('leftArrow');
  const rightArrow = document.getElementById('rightArrow');
  const items = document.querySelectorAll('.vehicle-card-wrapper');

  const visibleItems = 4;
  const totalItems = items.length;
  const maxIndex = Math.ceil(totalItems / visibleItems) - 1;
  let currentIndex = 0;

  // Ensure each item has fixed width in pixels based on container
  const container = document.querySelector('.carousel-container');
  const containerWidth = container.offsetWidth;
  const itemWidth = containerWidth / visibleItems;

  items.forEach(item => {
    item.style.minWidth = `${itemWidth}px`;
    item.style.maxWidth = `${itemWidth}px`;
    item.style.flex = `0 0 ${itemWidth}px`;
  });

  const gap = 20; // Must match CSS gap

  function updateCarousel() {
    const offset = currentIndex * (itemWidth * visibleItems + gap * visibleItems);
    track.style.transform = `translateX(-${offset}px)`;
  }

  leftArrow.addEventListener('click', () => {
    if (currentIndex > 0) {
      currentIndex--;
      updateCarousel();
    }
  });

  rightArrow.addEventListener('click', () => {
    if (currentIndex < maxIndex) {
      currentIndex++;
      updateCarousel();
    }
  });

  // Optional: update layout if window resizes
  window.addEventListener('resize', () => {
    location.reload(); // or recalculate itemWidth, etc. for dynamic resizing
  });
</script>