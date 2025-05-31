<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Check if the user is logged in
$loggedIn = isset($_SESSION['username']);
$displayLogin = $loggedIn ? 'none' : 'block';
$displayProfile = $loggedIn ? 'block' : 'none';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Header</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="../Css/header_style.css">
</head>

<body>
  <!-- menu container -->
  <div class="menu-container">
    <div class="menu-item">
      <ul>
        <li><a href="Home.php"><img src="../../Image/Logo White.png" alt=""></a></li>
        <li><a href="Home.php">Home</a></li>
        <li><a href="About.php">About Us</a></li>
        <li><a href="Listing.php">Vehicle Catalog</a></li>
        <li><a href="Contact.php">Contact Us</a></li>
      </ul>
    </div>

    <div class="sec">
      <!-- Notification Bell -->
      <div class="notification-container">
        <i class="fa fa-bell" id="notificationBell"></i>
        <div class="notification-dropdown" id="notificationDropdown"></div>
      </div>

      <!-- Profile/Login -->
      <?php if ($loggedIn): ?>
        <p><?php echo $_SESSION["username"]; ?></p>
        <div class="profile-icon" style="display: <?php echo $displayProfile; ?>;">
          <div class="user-profile" onclick="togglemenu()">
            <div class="login-user"><i class="fa-solid fa-user"></i></div>
          </div>

          <!-- Profile Dropdown -->
          <div class="sub-menu-wrap" id="sub-menu-wrap">
            <div class="sub-menu">
              <hr style="margin-top: 5px" />
              <a href="../../User/Php/UpdateUser.php" class="sub-menu-link">
                <i class="fa-solid fa-user-pen"></i>
                <p>Edit Profile</p>
              </a>
              <a href="../../User/Php/Booking_history.php" class="sub-menu-link">
                <i class="fa-solid fa-signal"></i>
                <p>View Status</p>
              </a>
              <a href="../../Auth/Php/logout.php" class="sub-menu-link">
                <i class="fa-solid fa-right-from-bracket"></i>
                <p>Logout</p>
              </a>
            </div>
          </div>
        </div>
      <?php else: ?>
        <div class="profile-icon" style="display: <?php echo $displayLogin; ?>;">
          <button class="red-btn"><a href="../../Auth/Php/login.php">Login</a></button>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const submenu = document.getElementById("sub-menu-wrap");
      const bell = document.getElementById("notificationBell");
      const dropdown = document.getElementById("notificationDropdown");

      // Toggle profile submenu
      window.togglemenu = function() {
        submenu.classList.toggle("open-class");
      };

      // Toggle and load notifications
      bell.addEventListener("click", function(event) {
        event.stopPropagation();
        if (dropdown.style.display === "block") {
          dropdown.style.display = "none";
        } else {
          dropdown.style.display = "block";
          dropdown.innerHTML = "<div class='notification-item'>Loading...</div>";

          const xhr = new XMLHttpRequest();
          xhr.open("GET", "../../User/Php/get_notifications.php", true);
          xhr.onload = function() {
            dropdown.innerHTML = xhr.status === 200 ? xhr.responseText : "<div class='notification-item'>Error loading notifications</div>";
          };
          xhr.onerror = function() {
            dropdown.innerHTML = "<div class='notification-item'>Network error</div>";
          };
          xhr.send();
        }
      });

      // Hide dropdowns when clicking outside
      window.addEventListener("click", function(event) {
        if (!event.target.closest(".notification-container")) {
          dropdown.style.display = "none";
        }
        if (!event.target.closest(".profile-icon")) {
          submenu.classList.remove("open-class");
        }
      });
    });
  </script>
</body>

</html>