<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include '../../Database/database.php';

if (isset($_GET['vehicle_id'])) {
  $_SESSION['last_viewed_vehicle_id'] = intval($_GET['vehicle_id']);
}
// Adjust path as needed

if (!isset($_SESSION['username'])) {
  header("Location: ../../Auth/Php/login.php");
  exit();
}

$username = $_SESSION['username'];
$vehicle_id = isset($_GET['vehicle_id']) ? (int) $_GET['vehicle_id'] : 0;


// ✅ Show alert if there's a success or error message in session
if (isset($_SESSION['booking_success'])) {
  echo "<script>alert('" . $_SESSION['booking_success'] . "');</script>";
  unset($_SESSION['booking_success']);
}

if (isset($_SESSION['booking_error'])) {
  echo "<script>alert('" . $_SESSION['booking_error'] . "');</script>";
  unset($_SESSION['booking_error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (empty($_POST['vehicle_id']) || !$username) {
    header('Location: Listing.php');
    exit();
  }

  $vehicle_id = (int) $_POST['vehicle_id'];

  // Fetch vehicle_number for this vehicle_id
  $stmt = $conn->prepare("SELECT vehicle_number FROM vehicle WHERE vehicle_id = ?");
  $stmt->bind_param("i", $vehicle_id);
  $stmt->execute();
  $stmt->bind_result($vehicle_number);

  if (!$stmt->fetch()) {
    // Vehicle not found
    $stmt->close();
    header('Location: Listing.php');
    exit();
  }
  $stmt->close();

  // Check if the view record exists
  $stmt = $conn->prepare("SELECT view_id FROM vehicle_views WHERE username = ? AND vehicle_id = ? AND vehicle_number = ?");
  $stmt->bind_param("sis", $username, $vehicle_id, $vehicle_number);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    // Update timestamp if record exists
    $stmt->close();
    $stmt = $conn->prepare("UPDATE vehicle_views SET viewed_at = NOW() WHERE username = ? AND vehicle_id = ? AND vehicle_number = ?");
    $stmt->bind_param("sis", $username, $vehicle_id, $vehicle_number);
    $stmt->execute();
    $stmt->close();
  } else {
    // Insert new view record
    $stmt->close();
    $stmt = $conn->prepare("INSERT INTO vehicle_views (username, vehicle_id, vehicle_number, viewed_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sis", $username, $vehicle_id, $vehicle_number);
    $stmt->execute();
    $stmt->close();
  }

  // Keep only the latest 4 views per user
  $conn->query("
        DELETE FROM vehicle_views
        WHERE view_id NOT IN (
            SELECT view_id FROM (
                SELECT view_id FROM vehicle_views
                WHERE username = '" . $conn->real_escape_string($username) . "'
                ORDER BY viewed_at DESC
                LIMIT 4
            ) AS recent
        )
        AND username = '" . $conn->real_escape_string($username) . "'
    ");

  // Redirect to GET version of Book.php
  header('Location: Book.php?vehicle_id=' . urlencode($vehicle_id));
  exit();
}





// Fetch vehicle data from DB (important!)
$sql = "SELECT * FROM vehicle WHERE vehicle_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $vehicle_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
  die('No vehicle found with ID: ' . htmlspecialchars($vehicle_id));
}

$row = $result->fetch_assoc();
$stmt->close();

// end of fetching vehicle data

// Generate a booking token to protect from duplicate submissions
$token = bin2hex(random_bytes(32));
$_SESSION['booking_token'] = $token;

$is_favorite = false;

if (isset($_SESSION['username']) && isset($_GET['vehicle_id'])) {
  $username = $_SESSION['username'];
  $vehicle_id = intval($_GET['vehicle_id']); // or from DB directly

  $stmt = $conn->prepare("SELECT userid FROM user WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows > 0) {
    $userid = $result->fetch_assoc()['userid'];

    $stmt = $conn->prepare("SELECT 1 FROM wishlist WHERE userid = ? AND vehicle_id = ?");
    $stmt->bind_param("ii", $userid, $vehicle_id);
    $stmt->execute();
    $stmt->store_result();

    $is_favorite = $stmt->num_rows > 0;
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Book Vehicle</title>
  <link rel="stylesheet" href="../../User/Css/style.css" />

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


</head>

<body>
  <?php include "Header.php"; ?>

  <div class="booking-container">
    <!-- Left side: Image & Car info -->
    <div class="left-panel">
      <img src="../../Image/<?php echo htmlspecialchars($row['vehicle_image']); ?>" alt="Vehicle Image" />

      <div class="details">
        <div class="rating">
          <?php
          // Fetch reviews for this vehicle
          $reviews_sql = "SELECT * FROM ratings WHERE vehicle_id = ? ORDER BY rated_at DESC";
          $reviews_stmt = $conn->prepare($reviews_sql);
          $reviews_stmt->bind_param("s", $vehicle_id);
          $reviews_stmt->execute();
          $reviews_result = $reviews_stmt->get_result();
          $reviews = [];

          while ($review_row = $reviews_result->fetch_assoc()) {
            $reviews[] = $review_row;
          }
          $reviews_stmt->close();
          ?>

          <span style="color: #f5b301;">
            <?php
            if (count($reviews) > 0) {
              $total_rating = array_sum(array_column($reviews, 'rating'));
              $avg = $total_rating / count($reviews);

              $full_stars = floor($avg);
              $has_half_star = ($avg - $full_stars) >= 0.25 && ($avg - $full_stars) < 0.75;
              $empty_stars = 5 - $full_stars - ($has_half_star ? 1 : 0);

              echo str_repeat('<i class="fas fa-star"></i>', $full_stars);
              if ($has_half_star) {
                echo '<i class="fas fa-star-half-alt"></i>';
              }
              echo str_repeat('<i class="far fa-star"></i>', $empty_stars);
            } else {
              echo "No reviews yet";
            }
            ?>
          </span>
          <h4><?php echo htmlspecialchars($row['vehicle_model']); ?></h4>
          <div class="price-tag">Rs <?php echo htmlspecialchars($row['vehicle_price']); ?> / Day</div>

          <!-- HTML button -->
          <div class="fevorite" style="margin-bottom: 8px;">
            <button class="blue-btn" id="add-to-favorite" style="<?php echo $is_favorite ? 'display:none;' : ''; ?>"
              data-vehicle-id="<?php echo htmlspecialchars($vehicle_id); ?>">
              <i class="fas fa-heart"></i> Add to Favorites
            </button>

            <button class="red-btn" id="remove-from-favorite" style="<?php echo $is_favorite ? '' : 'display:none;'; ?>"
              data-vehicle-id="<?php echo htmlspecialchars($vehicle_id); ?>">
              <i class="fas fa-heart"></i> Remove from Favorites
            </button>
          </div>
          <hr>

        </div>


        <div class="vehicle-details">
          <div class="detail-item">
            <span class="detail-icon">🚗</span>
            <span><span class="detail-label">Condition:</span>
              <?php echo htmlspecialchars($row['vehicle_condition']); ?></span>
          </div>
          <div class="detail-item">
            <span class="detail-icon">⛽</span>
            <span><span class="detail-label">Fuel:</span> <?php echo htmlspecialchars($row['vehicle_oil']); ?></span>
          </div>
          <div class="detail-item">
            <span class="detail-icon">🛠️</span>
            <span><span class="detail-label">Type:</span> <?php echo htmlspecialchars($row['vehicle_type']); ?></span>
          </div>
          <div class="detail-item">
            <span class="detail-icon">📅</span>
            <span><span class="detail-label">Number:</span>
              <?php echo htmlspecialchars($row['vehicle_number']); ?></span>
          </div>
          <div class="detail-item">
            <span class="detail-icon">🎨</span>
            <span><span class="detail-label">Color:</span> <?php echo htmlspecialchars($row['vehicle_color']); ?></span>
          </div>
          <div class="detail-item">
            <span class="detail-icon">👥</span>
            <span><span class="detail-label">Seats:</span>
              <?php echo htmlspecialchars($row['vehicle_people']); ?></span>
          </div>
        </div>

      </div>
    </div>


    <!-- Right side: Booking Form -->
    <div class="right-panel">
      <form action="../../Database/book_vehicle_backend.php" onsubmit="return validateForm()" method="post">
        <input type="hidden" name="vehicle_number" value="<?php echo htmlspecialchars($row['vehicle_number']); ?>">
        <input type="hidden" name="username" value="<?php echo htmlspecialchars($_SESSION['username']); ?>">
        <input type="hidden" name="booking_token" value="<?php echo $token; ?>">
        <input type="hidden" name="vehicle_id" value="<?php echo htmlspecialchars($vehicle_id); ?>">
        <input type="text" name="fullname" id="fullname" placeholder="Full Name" required
          value="<?php echo htmlspecialchars($_SESSION['fullname'] ?? ''); ?>" pattern="[A-Z][a-z]+(?: [A-Z][a-z]+)*"
          title="Each name should start with a capital letter (e.g., John Doe)">
        <input type="email" name="email" placeholder="Email" required
          value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>">
        <input type="tel" name="number" id="number" placeholder="Phone Number" required
          value="<?php echo htmlspecialchars($_SESSION['number'] ?? ''); ?>" pattern="^(97|98)\d{8}$"
          title="Phone number must start with '97' or '98' and be exactly 10 digits long">

        <input type="date" name="pickup_date" id="pickup_date" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
          required onchange="calculateTotal(); updateReturnDateMin()" />
        <input type="time" name="pickup_time" id="pickup_time" required onchange="calculateTotal()" />

        <input type="date" name="return_date" id="return_date" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
          required onchange="calculateTotal()" />
        <input type="time" name="return_time" id="return_time" required onchange="calculateTotal()" />
        <input type="text" name="pickup_location" id="pickup_location" placeholder="Pickup Location" required
          value="<?php echo htmlspecialchars($_SESSION['pickup_location'] ?? ''); ?>">

        <input type="text" id="vehicle_price" name="vehicle_price"
          value="<?php echo htmlspecialchars($row['vehicle_price']); ?>" readonly />
        <!-- Change name so it doesn't conflict with actual booking_type select -->
        <input type="hidden" name="booking_type_hidden" id="hidden_booking_type">
        <input type="text" id="total_price" name="total_price" value="0" readonly />






        <button type="submit" class="blue-btn" onclick="return selectPaymentMethod(method)">Book Now</button>
      </form>
    </div>

  </div>
  <!-- Recommended Vehicles Section -->
  <div class="recommended-section">


    <?php
    include '../../Database/recommend_vehicles_backend.php';
    ?>
  </div>



  <hr>

  <div class="review-comment">
    <h4>Customer Reviews</h4>

    <!-- Reviews Display -->
    <div style="align-items: center; gap: 10px; ">
      <?php if (empty($reviews)): ?>
        <p style="color: #aaa;">No reviews yet.</p>
      <?php else: ?>
        <?php foreach ($reviews as $review): ?>
          <div
            style="background-color: white; border-radius: 10px; margin-bottom: 15px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); padding: 15px; color: black;">
            <strong><?php echo htmlspecialchars($review['username']); ?></strong>
            <span style="color: #f5b301;">
              <?php echo str_repeat("★", $review['rating']) . str_repeat("☆", 5 - $review['rating']); ?></span>
            <p style="margin: 5px 0;"><?php echo nl2br(htmlspecialchars($review['feedback'])); ?></p>
            <small style="color: #888;"><?php echo date("F j, Y, g:i a", strtotime($review['rated_at'])); ?></small>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
  <hr>

  <div class="policy-section">
    <h4>🚗Rental Policies</h4>
    

    <h5>✅ Cancellation Policy</h5>
    <p >
      <b>Instant Confirmation:</b> Cancellations are processed immediately. <b>Rs500</b> cancellation fee applies to all
      cancellations.
      Cancellations must be done via the <b>cancel button from booking history.</b>
    </p>
    <br>

    <h5>📅 Booking Policy</h5>
    <p >
      <b>Valid ID Required:</b> Renter must provide a valid government-issued ID at the time of pickup.
      Bookings are subject to <b>vehicle availability</b> and must follow the <b>minimum booking duration of 2
        hours</b>.
    </p>
    <br>

    <h5>⛽ Fuel Policy</h5>
    <p >
      <b>Same Level Return:</b> The vehicle must be returned with the same fuel level as at pickup. <b>Fuel charges</b>
      will apply if returned with less fuel.
    </p>
    <br>

    <h5>⏰ Late Return Policy</h5>
    <p >
      <b>Hourly Charges:</b> If the vehicle is returned late, hourly rates will be applied. In case of a delay beyond
      the return time, additional <b>fines</b> may apply.
    </p>
    <br>

    <h5>🔧 Damage Policy</h5>
    <p >
      <b>Renter Responsibility:</b> Any damage caused to the vehicle during the rental period is the responsibility of
      the renter. <b>Repair costs</b> will be charged accordingly.
    </p>

  </div>








  <script src="../../User/Javascript/book_vehicle.js"></script>


</html>