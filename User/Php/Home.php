<?php
session_start();
include '../../Database/database.php';


?>
<!DOCTYPE html>
<html lang="en">


<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Home | Vehicle Rental</title>
  <link rel="stylesheet" href="../../user/Css/style.css" />
 
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


</head>

<body>
  <?php include "Header.php"; ?>

  <div class="slider-container">
    <div class="slider">
      <img src="../../Image/bus.webp" class="slide active" alt="Image 1" />
      <img src="../../Image/images.jpg" class="slide" alt="Image 2" />
      <img src="../../Image/jeep7.jpg" class="slide" alt="Image 3" />
      <img src="../../Image/image.png" class="slide" alt="Image 4" />
    </div>
    <div class="overlay"></div>
    <div class="dots-container">
      <span class="dot active-dot" data-slide="0"></span>
      <span class="dot" data-slide="1"></span>
      <span class="dot" data-slide="2"></span>
      <span class="dot" data-slide="3"></span>
    </div>
  </div>

  <!-- Popular Vehicles Section -->
  <div class="popular-section">

    <?php include '../../Database/popular_vehicles_backend.php'; ?>
  </div>

  <div class="recommendation-section">
    <?php include 'also_booked.php'; ?>
  </div>

  <div class="container">
    <h2>Discover the Nepal's largest car rental marketplace</h2>

    <div class="features">
      <div class="feature">
        <div class="feature-icon">∞</div>
        <h3>Endless options</h3>
        <p>Choose from thousands of vehicles you won’t find anywhere else. Choose it and get picked up where you want
          it.</p>
      </div>

      <div class="feature">
        <div class="feature-icon">🎧</div>
        <h3>24/7 customer support</h3>
        <p>Rest easy knowing that everyone in the RB Rental community is screened, and 24/7 customer support and
          roadside assistance are just a click away.</p>
      </div>

      <div class="feature">
        <div class="feature-icon">🛡️</div>
        <h3>Go for trip confidently</h3>
        <p>Go for trip confidently with your choice of protection plans — all plans include varying levels of liability
          insurance provided through RB Rental's Insurance Agency.</p>
      </div>
    </div>

    <div class="button-container">
      <button class="button" onclick="location.href='Contact.php';">Book the perfect car</button>
    </div>
  </div>

  <div class="vehicle_container">
    <h1>Categories</h1>
    <p class="subtitle">Choose from thousands of cars in over all major cities across the Country.</p>

    <div class="categories">
      <div class="category-card">
        <a href="Listing.php?category=car" class="vcard">
          <img src="../../Image/car3.jpg" alt="Car" />
          <div class="vcard-d">
            <h3>Car</h3>
          </div>
        </a>
      </div>

      <div class="category-card">
        <a href="Listing.php?category=sumo" class="vcard">
          <img src="../../Image/sumo1.jpg" alt="Sumo" />
          <div class="vcard-d">
            <h3>Sumo</h3>
          </div>
        </a>
      </div>

      <div class="category-card">
        <a href="Listing.php?category=hiace" class="vcard">
          <img src="../../Image/hiace1.jpg" alt="Hiace" />
          <div class="vcard-d">
            <h3>Hiace</h3>
          </div>
        </a>
      </div>

      <div class="category-card">
        <a href="Listing.php?category=bus" class="vcard">
          <img src="../../Image/bus1.jpg" alt="Bus" />
          <div class="vcard-d">
            <h3>Bus</h3>
          </div>
        </a>
      </div>
    </div>
  </div>

  <script src="../javascript/slide.js"></script>
</body>

</html>