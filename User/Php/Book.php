<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
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
  header('Location: Book.php?vehicle_id=' . urlencode($vehicle_id));
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Book Vehicle</title>
  <link rel="stylesheet" href="../../User/Css/style.css" />
  <style>
    .mainbookcard {
      display: flex;
      flex-direction: column;
      column-gap: 20px;
      row-gap: 20px;
      justify-content: center;
      padding: 20px 0px;
    }

    .bookcard {
      display: flex;
      flex-direction: column;
      padding: 60px 120px;
      border: 1px solid black;
    }

    .ucard,
    .book-vcard {
      display: flex;
      flex-direction: row;
      row-gap: 10px;
      column-gap: 40px;
      flex-wrap: wrap;
    }

    legend {
      font-size: 22px;
      font-weight: 700;
    }
  </style>
</head>

<body>
  <?php include "Header.php"; ?>

  <div class="bookcard">
    <h1>Rent Vehicle Here</h1>
    <?php


    if (isset($_SESSION['booking_error'])) {
      $error_message = $_SESSION['booking_error'];
      echo "<script>alert('$error_message');</script>";
      unset($_SESSION['booking_error']);
    }
    ?>

    <hr />
    <form action="../../Database/book_vehicle_backend.php" onsubmit="return validateForm()" method="post">
      <input type="hidden" name="booking_token" value="<?php echo $token; ?>">
      <input type="hidden" name="vehicle_id" value="<?php echo htmlspecialchars($vid); ?>">

      <!-- Customer Information Fieldset -->
      <fieldset>
        <legend>Customer Information</legend>
        <div class="ucard">
          <div class="i-field">
            <label for="fullname">Full Name</label>
            <input type="text" name="fullname" id="fullname" required />
          </div>
          <div class="i-field">
            <label for="username">Username</label>
            <input type="text" name="username" id="username"
              value="<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : ''; ?>" readonly />

          </div>
          <div class="i-field">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required />
          </div>
          <div class="i-field">
            <label for="number">Number</label>
            <input type="tel" name="number" id="number" required />
          </div>
        </div>
      </fieldset>

      <!-- Additional Information Fieldset -->
      <fieldset>
        <legend>Additional Information</legend>
        <div class="book-vcard">


          <div class="i-field">
            <label for="pickup_date">Pick-up Date</label>
            <input type="date" name="pickup_date" id="pickup_date"
              min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required
              onchange="calculateTotal(); updateReturnDateMin()" />
          </div>
          <div class="i-field">
            <label for="pickup_time">Pick-up Time</label>
            <input type="time" name="pickup_time" id="pickup_time" required onchange="calculateTotal()" />
          </div>
          <div class="i-field">
            <label for="return_date">Return Date</label>
            <input type="date" name="return_date" id="return_date"
              min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required
              onchange="calculateTotal()" />
          </div>
          <div class="i-field">
            <label for="return_time">Return Time</label>
            <input type="time" name="return_time" id="return_time" required onchange="calculateTotal()" />
          </div>
          <div class="i-field">
            <label for="pickup_location">Pick-up Location</label>
            <input type="text" name="pickup_location" id="pickup_location" required />
          </div>
        </div>
      </fieldset>

      <!-- Booking Price Fieldset -->
      <fieldset>
        <legend>Booking Price</legend>
        <div class="book-vcard">
          <div class="i-field">
            <label for="vehicle_number">Vehicle Number:</label><br />
            <input type="text" name="vehicle_number" id="vehicle_number"
              value="<?php echo htmlspecialchars($row['vehicle_number']); ?>" readonly />
          </div>
          <div class="i-field">
            <label for="vehicle_price">Price Per Day:</label><br />
            <input type="text" id="vehicle_price" name="vehicle_price"
              value="<?php echo htmlspecialchars($row['vehicle_price']); ?>" readonly />
            <!-- Change name so it doesn't conflict with actual booking_type select -->
            <input type="hidden" name="booking_type_hidden" id="hidden_booking_type">

          </div>
          <div class="i-field">
            <label for="total_price">Total Price:</label><br />
            <input type="text" id="total_price" name="total_price" value="0" readonly />
          </div>
        </div>
      </fieldset>

      <div>
        <button type="submit" class="red-btn">Book</button>
      </div>
    </form>
  </div>



  <?php include "Footer.php"; ?>
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