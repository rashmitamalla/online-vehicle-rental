<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Home</title>

  <?php
  include '../../Database/database.php';

  $vehicle_count = $conn->query("SELECT COUNT(*) AS total_rows FROM vehicle")->fetch_assoc()["total_rows"];
  $user_count = $conn->query("SELECT COUNT(*) AS total_rows FROM user")->fetch_assoc()["total_rows"];
  $driver_count = $conn->query("SELECT COUNT(*) AS total_rows FROM driver")->fetch_assoc()["total_rows"];
  ?>

  <link rel="stylesheet" href="../../Dashboard/Css/dashboard_home.css">
  <link rel="stylesheet" href="../../Dashboard/Css/style.css">


</head>

<body>
  <div class="dashboard_container"><?php include 'dashboard.php'; ?></div>

  <!-- Main Content -->
  <div class="main-card" id="mainContent">
    <h1>Dashboard</h1>

    <div class="cards">
      <div class="card">
        <h3>Total Vehicles</h3>
        <p><?php echo $vehicle_count; ?></p>
        <span>+2.1% today</span>
        <div class="trend-indicator" style="color: green;">▲</div>
        <div class="goal">Target: 5</div>
      </div>

      <div class="card">
        <h3>Total Users</h3>
        <p><?php echo $user_count; ?></p>
        <span>+1.7% today</span>
        <div class="trend-indicator" style="color: green;">▲</div>
        <div class="goal">Target: 7</div>
      </div>

      <div class="card">
        <h3>Total Drivers</h3>
        <p><?php echo $driver_count; ?></p>
        <span>-0.5% today</span>
        <div class="trend-indicator" style="color: red;">▼</div>
        <div class="goal">Target: 4</div>
      </div>
      <div class="card">
        <h3>Total Booking</h3>
        <p><?php echo $driver_count; ?></p>
        <span>-0.5% today</span>
        <div class="trend-indicator" style="color: red;">▼</div>
        <div class="goal">Target: 4</div>
      </div>
    </div>


    <div class="extra-activities">
      <div class="chart-card">
        <h3>Revenue</h3>
        <canvas id="revenueChart"></canvas>
      </div>


      <div class="recent-activity">
        <h3>Recent Activity</h3>
        <table>
          <thead>
            <tr>
              <th>Customer</th>
              <th>Status</th>
              <th>Amount</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>bishal</td>
              <td>Signed Up</td>
              <td>$500</td>
            </tr>
            <tr>
              <td>Ujjawal</td>
              <td>New Booking</td>
              <td>$750</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>



  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="../../Dashboard/Javascript/dashboard.js"></script>



</body>

</html>