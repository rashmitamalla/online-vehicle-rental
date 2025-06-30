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
      <div class="favorite-dropdown-container">
        <i class="fa-solid fa-heart" id="favoriteIcon" title="Show Favorite" style="color: red; font-size: 1.5em; cursor: pointer;"></i>
        <div class="favorite-dropdown" id="favoriteDropdown">
          <div class="dropdown-header">Favorites</div>
          <div class="dropdown-content" id="favoriteContent">
            <!-- Dynamically load favorites here -->
            <p>Loading...</p>
          </div>
        </div>
      </div>


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
      const isLoggedIn = <?php echo isset($_SESSION['username']) ? 'true' : 'false'; ?>;

      const submenu = document.getElementById("sub-menu-wrap");
      const bell = document.getElementById("notificationBell");
      const dropdown = document.getElementById("notificationDropdown");
      const favoriteIcon = document.getElementById("favoriteIcon");
      const favoriteDropdown = document.getElementById("favoriteDropdown");
      const favoriteContent = document.getElementById("favoriteContent");

      // Toggle profile submenu
      window.togglemenu = function() {
        submenu.classList.toggle("open-class");
      };

      // Toggle and load notifications
      bell.addEventListener("click", function(event) {
        event.stopPropagation();
        dropdown.style.display =
          dropdown.style.display === "block" ? "none" : "block";

        if (dropdown.style.display === "block") {
          dropdown.innerHTML = "<div class='notification-item'>Loading...</div>";
          const xhr = new XMLHttpRequest();
          xhr.open("GET", "../../User/Php/get_notifications.php", true);
          xhr.onload = function() {
            dropdown.innerHTML =
              xhr.status === 200 ?
              xhr.responseText :
              "<div class='notification-item'>Error loading notifications</div>";
          };
          xhr.onerror = function() {
            dropdown.innerHTML =
              "<div class='notification-item'>Network error</div>";
          };
          xhr.send();
        }
      });

      // Toggle and load favorites (wishlist) with login check
      favoriteIcon.addEventListener("click", function(e) {
        e.stopPropagation();

        if (!isLoggedIn) {
          alert("User not logged in. Please log in to view favorites.");
          return;
        }

        favoriteDropdown.style.display =
          favoriteDropdown.style.display === "block" ? "none" : "block";

        if (favoriteDropdown.style.display === "block") {
          favoriteContent.innerHTML = "<div class='notification-item'>Loading...</div>";
          fetch("fetch_wishlist.php")
            .then((res) => res.json())
            .then((data) => {
              if (data.length > 0) {
                favoriteContent.innerHTML = "";
                data.forEach((item) => {
                  favoriteContent.innerHTML += `
  <a href="Book.php?vehicle_id=${item.vehicle_id}" style="display:flex; align-items:center; gap:10px; text-decoration:none; color:inherit; margin-bottom:8px; font-size:14px;">
    <img src="../../Image/${item.vehicle_image}" alt="${item.vehicle_model}" style="width:40px; height:30px; object-fit:cover; border-radius:4px;">
    <div>
      ${item.vehicle_model} - Rs ${item.vehicle_price}/day
    </div>
  </a>
`;
                });
              } else {
                favoriteContent.innerHTML = "<p>No favorites yet.</p>";
              }
            })
            .catch(() => {
              favoriteContent.innerHTML = "<p>Failed to load favorites.</p>";
            });
        }
      });

      // Hide dropdowns when clicking outside
      document.addEventListener("click", function() {
        dropdown.style.display = "none";
        favoriteDropdown.style.display = "none";
      });

      // Prevent closing when clicking inside the dropdowns
      dropdown.addEventListener("click", function(e) {
        e.stopPropagation();
      });

      favoriteDropdown.addEventListener("click", function(e) {
        e.stopPropagation();
      });
    });
  </script>


</body>

</html>