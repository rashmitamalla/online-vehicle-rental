<?php
include '../../Database/database.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
$exclude_tailwind_pages = ['Book.php', 'Listing.php'];
$load_tailwind = !in_array($current_page, $exclude_tailwind_pages);



$loggedIn = isset($_SESSION['username']);
$displayLogin = $loggedIn ? 'none' : 'block';
$displayProfile = $loggedIn ? 'block' : 'none';

$notificationCount = 0;

if ($loggedIn) {
    $username = $_SESSION['username'];
    $stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE username = ? AND is_read = 0");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($notificationCount);
    $stmt->fetch();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Header</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <script src="https://cdn.tailwindcss.com"></script>
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
      <?php if ($loggedIn): ?>
        <!-- Favorite Icon (Link to favorite page) -->
        <a href="favorites.php" title="Favorites" style="margin-right: 15px;">
          <i class="fa-solid fa-heart" style="color: red;"></i>
        </a>

        <!-- Notification Icon (Link to notifications page) -->
       <!-- Notification Icon (Link to notifications page) -->
<a href="notification.php" title="Notifications" class="relative inline-block" style="margin-right: 15px;">
  <i class="fa fa-bell" style="color: white;"></i>
  <?php if ($notificationCount > 0): ?>
    <span class="absolute -top-1 -right-2 bg-red-600 text-white text-xs font-semibold rounded-full px-1.5 py-0.5">
      <?= ($notificationCount > 9 ? '9+' : $notificationCount) ?>
    </span>
  <?php endif; ?>
</a>

      <?php endif; ?>

      <!-- Profile/Login Section -->
      <?php if ($loggedIn): ?>
        <p><?php echo $_SESSION["username"]; ?></p>
        <div class="profile-icon" style="display: <?php echo $displayProfile; ?>;">
          <div class="user-profile" onclick="togglemenu()">
            <div class="login-user"><i class="fa-solid fa-user"></i></div>
          </div>

          <div class="sub-menu-wrap" id="sub-menu-wrap">
            <div class="sub-menu">
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
    function togglemenu() {
      const submenu = document.getElementById("sub-menu-wrap");
      submenu.classList.toggle("open-class");
    }
  </script>
</body>
</html>
