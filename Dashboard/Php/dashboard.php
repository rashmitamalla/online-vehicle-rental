<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once(__DIR__ . "/../../Database/database.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard</title>
  <link rel="stylesheet" href="../../Dashboard/Css/dashboard.css">

  <style>

  </style>
</head>

<body>

  <!-- Hamburger Button -->
  <div class="hamburger" onclick="toggleMenu()">
    &#9776;
  </div>

  <!-- Sidebar Dashboard -->
  <div class="dashboard">
    <ul>

      <?php if (isset($_SESSION['username'])): ?>
        <li>
          <a href="#">
            <i class="fas fa-user"></i>
            <div><?php echo htmlspecialchars($_SESSION['username']); ?></div>
          </a>
        </li>
      <?php endif; ?>

      <li><a href="../../Dashboard/Php/dashboard_home.php"><i class="fas fa-home"></i>
          <div>Home</div>
        </a></li>
      <li><a href="../../Dashboard/Php/display_vehicle.php"><i class="fa-solid fa-car"></i>
          <div>Vehicle</div>
        </a></li>
      <li><a href="../../Dashboard/Php/display_user.php"><i class="fa-solid fa-users"></i>
          <div>Users</div>
        </a></li>
      <li><a href="display_driver.php"><i class="fa-regular fa-id-card"></i>
          <div>Drivers</div>
        </a></li>
      <li><a href="display_admin.php"><i class="fa-solid fa-user-tie"></i>
          <div>Admin</div>
        </a></li>
      <li><a href="../../Dashboard/Php/display_booking_detail.php"><i class="fa-solid fa-list-check"></i>
          <div>Bookings</div>
        </a></li>
      <li><a href="update_admin.php"><i class="fa-solid fa-user-pen"></i>
          <div>Update-Profile</div>
        </a></li>
      <li><a href="display_inquiry.php"><i class="fa-regular fa-envelope"></i>
          <div>Inquiry</div>
        </a></li>
      <li><a href="../../Auth/Php/logout.php" class="logout"><i class="fas fa-sign-out-alt"></i>
          <div>Log out</div>
        </a></li>
    </ul>
  </div>

  <!-- JavaScript for toggle -->
  <script>
    function toggleMenu() {
      document.querySelector('.dashboard').classList.toggle('open');
    }
  </script>

</body>

</html>