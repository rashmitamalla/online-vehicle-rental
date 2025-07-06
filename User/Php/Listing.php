<?php
include '../../Database/database.php';
if (session_status() === PHP_SESSION_NONE) session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Vehicle Listing</title>
  <link rel="stylesheet" href="../Css/vehicle_list.css" />
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
  <?php include '../Php/header.php'; ?>
  

  <form id="date-filter-form" method="POST" style="margin-bottom: 20px;">
    <div class="top-filter-bar">
      <input type="date" name="pickup_date" class="input-box" required />
      <input type="time" name="pickup_time" class="input-box" required />
      <input type="date" name="return_date" class="input-box" required />
      <input type="time" name="return_time" class="input-box" required />
      <button type="submit" class="search-btn">Search</button>
    </div>
  </form>

  <div class="content-wrapper">
    <div class="sidebar-filter">
      <h3>Filter</h3>
      <form id="filter-form" method="post">
        <div class="filter-group">
          <h4>Category</h4>
          <label><input type="checkbox" name="car_type[]" value="Bus" /> Bus</label>
          <label><input type="checkbox" name="car_type[]" value="Car" /> Car</label>
          <label><input type="checkbox" name="car_type[]" value="Hiace" /> Hiace</label>
          <label><input type="checkbox" name="car_type[]" value="Sumo" /> Sumo</label>
          <label><input type="checkbox" name="car_type[]" value="All" /> All</label>
        </div>

        <div class="filter-group">
          <h4>Fuel Type</h4>
          <label><input type="checkbox" name="fuel_type[]" value="Petrol" /> Petrol</label>
          <label><input type="checkbox" name="fuel_type[]" value="Diesel" /> Diesel</label>
          <label><input type="checkbox" name="fuel_type[]" value="Electric" /> Electric</label>
        </div>

        <div class="filter-group">
          <h3>Price Range Slider</h3>
          <div class="price-labels">
            <span id="min-price">Rs 1000</span>
            <span id="max-price">Rs 50000</span>
          </div>
          <div class="slider-container">
            <input type="range" min="1000" max="50000" value="1000" id="range-min">
            <input type="range" min="1000" max="50000" value="50000" id="range-max">
            <input type="hidden" name="min_price" id="hidden-min-price" value="1000">
            <input type="hidden" name="max_price" id="hidden-max-price" value="50000">
          </div>
        </div>

        <button type="submit" class="search-btn">Apply Filters</button>
        <button type="button" id="reset-btn">Reset</button>
      </form>
    </div>

    <div class="vehicle-list">
      <!-- AJAX response will go here -->
    </div>
  </div>

  <script src="../Javascript/vehicle_list.js"></script>
</body>
</html>
