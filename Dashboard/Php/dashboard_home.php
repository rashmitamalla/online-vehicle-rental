<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard</title>

  <!-- Icons & CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="../../Dashboard/Css/dashboard_home.css" />
  <link rel="stylesheet" href="../../Dashboard/Css/style.css" />

 

  <?php
  include '../../Database/database.php';

  /* ─────────────  COUNTS  ───────────── */
  $vehicle_count = $conn->query("SELECT COUNT(*) total FROM vehicle")->fetch_assoc()['total'];
  $user_count = $conn->query("SELECT COUNT(*) total FROM user")->fetch_assoc()['total'];
  $driver_count = $conn->query("SELECT COUNT(*) total FROM driver")->fetch_assoc()['total'];
  $booking_count = $conn->query("SELECT COUNT(*) total FROM booking")->fetch_assoc()['total'];

  /* ─────────────  MONEY  ───────────── */
  $total_income = $conn->query("SELECT IFNULL(SUM(total_price),0) income FROM booking WHERE bstatus='completed'")
    ->fetch_assoc()['income'];
  /* ─────────────  WEEKLY REVENUE (last 6 weeks)  ───────────── */
  $weeklyData = [];
  $weeklyLabels = [];
  for ($i = 5; $i >= 0; $i--) {
    $start = date('Y-m-d', strtotime("last sunday -$i week"));
    $end = date('Y-m-d', strtotime("next saturday -$i week"));
    $row = $conn->query("
          SELECT IFNULL(SUM(total_price),0) revenue
          FROM booking
          WHERE bstatus='completed' AND booking_date BETWEEN '$start' AND '$end'
      ")->fetch_assoc();
    $weeklyData[] = (float) $row['revenue'];
    $weeklyLabels[] = date('M d', strtotime($start)) . ' - ' . date('M d', strtotime($end));
  }

  /* ─────────────  RECENT ACTIVITY (bookings, sign-ups, sign-ins)  ───────────── */
  $activities = $conn->query("
        SELECT fullname   AS actor,
               bstatus          AS action,
               total_price           AS amount,
               booking_date    AS event_time
        FROM   booking
      UNION ALL
        SELECT username        AS actor,
               'Signed Up'     AS action,
               NULL            AS amount,
               signup_date     AS event_time
        FROM   user
      UNION ALL
        SELECT u.username      AS actor,
               'Signed In'     AS action,
               NULL            AS amount,
               l.login_time    AS event_time
        FROM   login_log l
        JOIN   user u ON u.username = l.username
      ORDER BY event_time DESC
      LIMIT 5
  ");

  $sql = "SELECT COUNT(*) AS total_cancellations FROM booking WHERE bstatus = 'cancelled'";
  $result = $conn->query($sql);
  $row = $result->fetch_assoc();
  $totalCancellations = $row['total_cancellations'];
  ?>
</head>

<body>
  <div class="dashboard_container"><?php include 'dashboard.php'; ?></div>

  <div class="main-card" id="mainContent">
    <h1>Dashboard</h1>

    <!-- ====== TOP CARDS ====== -->
    <div class="cards">
      <div class="card">
        <div class="card-header">
          <i class="fas fa-car"></i>
          <h4>Total Vehicles</h4>
        </div>
        <p><?= $vehicle_count ?></p>
      </div>

      <div class="card">
        <div class="card-header">
          <i class="fas fa-user"></i>
          <h4>Total Users</h4>
        </div>
        <p><?= $user_count ?></p>
      </div>

      <div class="card">
        <div class="card-header">
          <i class="fas fa-user-tie"></i>
          <h4>Total Drivers</h4>
        </div>
        <p><?= $driver_count ?></p>
      </div>

      <div class="card">
        <div class="card-header">
          <i class="fas fa-calendar-check"></i>
          <h4>Total Bookings</h4>
        </div>
        <p><?= $booking_count ?></p>
      </div>

      <div class="card">
        <div class="card-header">
          
          <i class="fas fa-ban"></i>
          <h4>Total Cancellations</h4>
              


        </div>
        <h3><?php echo $totalCancellations; ?></h3> 
      </div>

      <div class="card">
        <div class="card-header">
          <i class="fas fa-chart-line"></i>
          <h4>Weekly Revenue</h4>
        </div>
        <p>Rs <?= number_format(array_sum($weeklyData)) ?></p>
      </div>
    </div>



    <!-- ====== MONEY SUMMARY ====== -->
    <div class="summary-section">
      <div class="summary-card">
        <h4>Total Income</h4>
        <p style="color:#76ff03;font-size:24px">Rs <?= number_format($total_income) ?></p>
      </div>
    </div>

    <!-- ====== CHART + RECENT ACTIVITY ====== -->
    <div class="extra-activities">
      <div class="chart-card">
        <h4>Weekly Revenue</h4>
        <canvas id="revenueChart"></canvas>
      </div>

      <div class="recent-activity">
        <h4>Recent Activity</h4>
        <table>
          <thead>
            <tr>
              <th>Actor</th>
              <th>Action</th>
              <th>Amount</th>
              <th>Event Time</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($a = $activities->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($a['actor']) ?></td>
                <td><?= htmlspecialchars($a['action']) ?></td>
                <td>
                  <?= is_null($a['amount']) ? '-' : 'Rs ' . number_format($a['amount'], 2) ?>
                </td>
                <td><?= date('Y-m-d H:i', strtotime($a['event_time'])) ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ====== CHART.JS ====== -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const labels = <?= json_encode($weeklyLabels) ?>;
    const data = <?= json_encode($weeklyData) ?>;
    new Chart(document.getElementById('revenueChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          label: 'Revenue',
          data,
          backgroundColor: '#76ff03',
          borderRadius: 5
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            callbacks: {
              label: c => `Rs ${c.raw}`
            }
          }
        },
        scales: {
          y: {
            ticks: {
              color: '#fff',
              beginAtZero: true
            }
          },
          x: {
            ticks: {
              color: '#fff'
            }
          }
        }
      }
    });
  </script>
</body>

</html>