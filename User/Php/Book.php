<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
// Include your database connection here:
include '../../Database/database.php';  // Adjust path as needed

if (!isset($_SESSION['username'])) {
  header("Location: ../../Auth/Php/login.php");
  exit();
}

$username = $_SESSION['username'];
$vehicle_id = isset($_GET['vehicle_id']) ? (int)$_GET['vehicle_id'] : 0;

if ($vehicle_id > 0) {
  $stmt = $conn->prepare("INSERT INTO vehicle_views (username, vehicle_id, viewed_at) VALUES (?, ?, NOW())");
  $stmt->bind_param("si", $username, $vehicle_id);
  $stmt->execute();
  $stmt->close();
}
// ✅ Show alert if there's a success or error message in session
if (isset($_SESSION['booking_success'])) {
  echo "<script>alert('" . $_SESSION['booking_success'] . "');</script>";
  unset($_SESSION['booking_success']);
}

if (isset($_SESSION['booking_error'])) {
  echo "<script>alert('" . $_SESSION['booking_error'] . "');</script>";
  unset($_SESSION['booking_error']);
}

include '../../Database/database.php';

// Handle POST from Listing.php (Book Now button)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (empty($_POST['vehicle_id'])) {
    header('Location: Listing.php'); // no vehicle chosen, back to listing
    exit();
  }
  $vehicle_id = $_POST['vehicle_id'];
  // Redirect to GET version of Book.php to prevent resubmission on refresh
  header('Location: Home.php?vehicle_id=' . urlencode($vehicle_id));
  exit();
}

// Handle GET - show booking form
if (!isset($_GET['vehicle_id'])) {
  header('Location: Listing.php'); // no vehicle selected, redirect back
  exit();
}

$vid = $_GET['vehicle_id'];

// Fetch vehicle data from DB (important!)
$sql = "SELECT * FROM vehicle WHERE vehicle_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $vid);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
  die('No vehicle found with ID: ' . htmlspecialchars($vid));
}

$row = $result->fetch_assoc();
$stmt->close();

// Generate a booking token to protect from duplicate submissions
$token = bin2hex(random_bytes(32));
$_SESSION['booking_token'] = $token;

$vehicle_id = $_GET['vehicle_id'] ?? null;
if ($vehicle_id) {
  if (!isset($_SESSION['recent_views'])) {
    $_SESSION['recent_views'] = [];
  }

  // Remove if already exists to reinsert at front
  $_SESSION['recent_views'] = array_diff($_SESSION['recent_views'], [$vehicle_id]);
  array_unshift($_SESSION['recent_views'], $vehicle_id);
  $_SESSION['recent_views'] = array_slice($_SESSION['recent_views'], 0, 5);
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

  <style>
    .container {
      display: flex;
      justify-content: space-between;
      gap: 30px;
      padding: 40px;
    }

    .left-panel {
      flex: 1;
    }

    .left-panel img {
      width: 100%;
      border-radius: 10px;
    }

    .details {
      margin-top: 20px;
    }

    .details h2 {
      margin: 10px 0;
    }

    .features {
      display: flex;
      gap: 20px;
      margin-top: 10px;
      color: #ccc;
    }

    .right-panel {
      background-color: #1c1f26;
      padding: 30px;
      border-radius: 12px;
      flex: 1;
    }

    .right-panel form {
      display: flex;
      flex-direction: column;

    }

    .right-panel input {

      padding: 10px;
      border: 1px solid #444;
      border-radius: 8px;
      background-color: #2c2f38;
      color: #fff;
    }

    .price-tag {
      color: #00c2c2;
      font-size: 20px;
      font-weight: bold;
    }
  </style>
</head>

<body>
  <?php include "Header.php"; ?>

  <div class="container">
    <!-- Left side: Image & Car info -->
    <div class="left-panel">
      <img src="../../Admin/<?php echo htmlspecialchars($row['vehicle_image']); ?>" alt="<?php echo htmlspecialchars($row['vehicle_number']); ?>" />

      <div class="details">
        <div class="rating">
          <?php
          // Fetch reviews for this vehicle
          $reviews_sql = "SELECT * FROM ratings WHERE vehicle_id = ? ORDER BY rated_at DESC";
          $reviews_stmt = $conn->prepare($reviews_sql);
          $reviews_stmt->bind_param("s", $vid);
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
          <h2><?php echo htmlspecialchars($row['vehicle_model']); ?></h2>
          <div class="price-tag">$<?php echo htmlspecialchars($row['vehicle_price']); ?> / Day</div>
          <hr style="margin: 15px 0; border-color: #444;" />

        </div>

        <style>
          .vehicle-details {
            max-width: 450px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            padding: 25px 30px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 18px 30px;
            transition: box-shadow 0.3s ease;
          }

          .vehicle-details:hover {
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
          }

          .detail-item {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 16px;
          }

          .detail-icon {
            font-size: 20px;
            color: #007BFF;
            /* bright blue accent */
            min-width: 24px;
            text-align: center;
          }

          .detail-label {
            font-weight: 600;
          }
        </style>

        <div class="vehicle-details">
          <div class="detail-item">
            <span class="detail-icon">🚗</span>
            <span><span class="detail-label">Condition:</span> <?php echo htmlspecialchars($row['vehicle_condition']); ?></span>
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
            <span><span class="detail-label">Number:</span> <?php echo htmlspecialchars($row['vehicle_number']); ?></span>
          </div>
          <div class="detail-item">
            <span class="detail-icon">🎨</span>
            <span><span class="detail-label">Color:</span> <?php echo htmlspecialchars($row['vehicle_color']); ?></span>
          </div>
          <div class="detail-item">
            <span class="detail-icon">👥</span>
            <span><span class="detail-label">Seats:</span> <?php echo htmlspecialchars($row['vehicle_people']); ?></span>
          </div>
        </div>

      </div>
    </div>


    <!-- Right side: Booking Form -->
    <div class="right-panel">
      <form action="../../Database/book_vehicle_backend.php" onsubmit="return validateForm()" method="post">
        <input type="hidden" name="booking_token" value="<?php echo $token; ?>">
        <input type="hidden" name="vehicle_id" value="<?php echo htmlspecialchars($vid); ?>">

        <input type="text" name="fullname" id="fullname" placeholder="Full Name" required value="<?php echo htmlspecialchars($_SESSION['fullname'] ?? ''); ?>" pattern="[A-Z][a-z]+(?: [A-Z][a-z]+)*" title="Each name should start with a capital letter (e.g., John Doe)">
        <input type="email" name="email" placeholder="Email" required value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>">
        <input type="tel" name="number" id="number" placeholder="Phone Number" required value="<?php echo htmlspecialchars($_SESSION['number'] ?? ''); ?>" pattern="^(97|98)\d{8}$" title="Phone number must start with '97' or '98' and be exactly 10 digits long">

        <input type="date" name="pickup_date" id="pickup_date"
          min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required
          onchange="calculateTotal(); updateReturnDateMin()" />
        <input type="time" name="pickup_time" id="pickup_time" required onchange="calculateTotal()" />

        <input type="date" name="return_date" id="return_date"
          min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required
          onchange="calculateTotal()" />
        <input type="time" name="return_time" id="return_time" required onchange="calculateTotal()" />
        <input type="text" name="pickup_location" id="pickup_location" placeholder="Pickup Location" required value="<?php echo htmlspecialchars($_SESSION['pickup_location'] ?? ''); ?>">

        <input type="text" id="vehicle_price" name="vehicle_price" 
          value="<?php echo htmlspecialchars($row['vehicle_price']); ?>" readonly />
        <!-- Change name so it doesn't conflict with actual booking_type select -->
        <input type="hidden" name="booking_type_hidden" id="hidden_booking_type">
        <input type="text" id="total_price" name="total_price" value="0" readonly />


        <button type="submit" class="blue-btn">Book Now</button>
      </form>
    </div>

  </div>



  <hr style="margin: 30px 0; border: 0.5px solid #333;" />

  <h3 style="align-items: center; gap: 10px;  padding: 0px 40px;">Customer Reviews</h3>

  <!-- Reviews Display -->
  <div
    style="align-items: center; gap: 10px;  padding: 0px 40px;">
    <?php if (empty($reviews)): ?>
      <p style="color: #aaa;">No reviews yet.</p>
    <?php else: ?>
      <?php foreach ($reviews as $review): ?>
        <div style="background-color: white; border-radius: 10px; margin-bottom: 15px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); padding: 15px; color: black;">
          <strong><?php echo htmlspecialchars($review['username']); ?></strong>
          <span style="color: #f5b301;"> <?php echo str_repeat("★", $review['rating']) . str_repeat("☆", 5 - $review['rating']); ?></span>
          <p style="margin: 5px 0;"><?php echo nl2br(htmlspecialchars($review['feedback'])); ?></p>
          <small style="color: #888;"><?php echo date("F j, Y, g:i a", strtotime($review['rated_at'])); ?></small>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
  <hr style="margin: 40px 0; border: 0.5px solid #444;" />

  <div class="policy-section" style="padding: 10px 40px; background: #f9f9f9; border-radius: 10px;">
    <h2 style="color: #333;">🚗 Rental Policies</h2>

    <h3 style="color: #555;">✅ Cancellation Policy</h3>
    <p style="font-size: 16px;">
      <b>Instant Confirmation:</b> Cancellations are processed immediately. <b>Rs500</b> cancellation fee applies to all cancellations.
      Cancellations must be done via the <b>cancel button from booking history.</b>
    </p>
    <br>

    <h3 style="color: #555;">📅 Booking Policy</h3>
    <p style="font-size: 16px;">
      <b>Valid ID Required:</b> Renter must provide a valid government-issued ID at the time of pickup.
      Bookings are subject to <b>vehicle availability</b> and must follow the <b>minimum booking duration of 2 hours</b>.
    </p>
    <br>

    <h3 style="color: #555;">⛽ Fuel Policy</h3>
    <p style="font-size: 16px;">
      <b>Same Level Return:</b> The vehicle must be returned with the same fuel level as at pickup. <b>Fuel charges</b> will apply if returned with less fuel.
    </p>
    <br>

    <h3 style="color: #555;">⏰ Late Return Policy</h3>
    <p style="font-size: 16px;">
      <b>Hourly Charges:</b> If the vehicle is returned late, hourly rates will be applied. In case of a delay beyond the return time, additional <b>fines</b> may apply.
    </p>
    <br>

    <h3 style="color: #555;">🔧 Damage Policy</h3>
    <p style="font-size: 16px;">
      <b>Renter Responsibility:</b> Any damage caused to the vehicle during the rental period is the responsibility of the renter. <b>Repair costs</b> will be charged accordingly.
    </p>

  </div>



<script>
    function calculateTotal() {
      const pickupDateStr = document.getElementById("pickup_date").value;
      const pickupTimeStr = document.getElementById("pickup_time").value;
      const returnDateStr = document.getElementById("return_date").value;
      const returnTimeStr = document.getElementById("return_time").value;
      const vehiclePrice = parseFloat(document.getElementById("vehicle_price").value);

      if (!pickupDateStr || !pickupTimeStr || !returnDateStr || !returnTimeStr || isNaN(vehiclePrice)) {
        return;
      }

      const pickup = new Date(`${pickupDateStr}T${pickupTimeStr}`);
      const ret = new Date(`${returnDateStr}T${returnTimeStr}`);

      if (ret <= pickup) {
        document.getElementById("total_price").value = "0.00";
        return;
      }

      const diffMs = ret - pickup;
      const diffHours = diffMs / (1000 * 60 * 60);

      if (diffHours < 2) {
        document.getElementById("total_price").value = "0.00";
        return;
      }

      // Calculate full days and remaining hours
      const fullDays = Math.floor(diffHours / 24);
      const remainingHours = diffHours % 24;

      const hourlyRate = vehiclePrice / 24;
      const total = (fullDays * vehiclePrice) + (remainingHours * hourlyRate);

      document.getElementById("total_price").value = total.toFixed(2);
      document.getElementById("hidden_booking_type").value = fullDays > 0 ? "daily+hourly" : "hourly";
    }

    function validateForm() {
      const pickup = new Date(document.getElementById("pickup_date").value + 'T' + document.getElementById("pickup_time").value);
      const ret = new Date(document.getElementById("return_date").value + 'T' + document.getElementById("return_time").value);
      const diffHours = (ret - pickup) / (1000 * 60 * 60);

      if (ret <= pickup) {
        alert("Return date and time must be after pickup.");
        return false;
      }

      if (diffHours < 2) {
        alert("Minimum booking time is 2 hours.");
        return false;
      }

      var phoneNumber = document.getElementById("number").value;
      var phoneNumberPattern = /^(97|98)\d{8}$/;

      const fullName = document.getElementById("fullname").value;
      const namePattern = /^[A-Z][a-z]+(?: [A-Z][a-z]+)*$/;
      if (!namePattern.test(fullName)) {
        alert("Invalid full name. Each name should start with a capital letter (e.g., John Doe).");
        return false; // Prevent form submission
      }


      if (!phoneNumberPattern.test(phoneNumber)) {
        alert("Phone number must start with '97' and '98' and be exactly 10 digits long.");
        return false; // Prevent form submission
      }

      return true;

    }
  </script>
</body>

</html>