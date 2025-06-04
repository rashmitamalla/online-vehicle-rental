<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Enhanced Dashboard</title>

  <!-- Icons & CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="../../Dashboard/Css/dashboard_home.css" />
  <link rel="stylesheet" href="../../Dashboard/Css/style.css" />

  <!-- Inline dark-theme tweaks -->
  <style>
    .main-card {

      max-width: 1200px;
      margin: auto;
      padding: 40px;

      border-radius: 15px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, .3);
    }

    .cards,
    .extra-activities {
      display: flex;
      flex-wrap: wrap;
      gap: 1.5rem;
      margin-top: 20px;
    }

    .card,
    .chart-card,
    .recent-activity,
    .summary-card {

      background: #2c2f48;
      padding: 1.5rem;
      border-radius: 15px;
      flex: 1;
      box-shadow: 0 2px 10px rgba(0, 0, 0, .3);
    }

    .card i {
      font-size: 30px;
      margin-bottom: 10px;
      color: #76ff03;
    }

    table {
      width: 100%;
      color: #fff;
      color: black;
    }

    th,
    td {
      padding: 10px;
      text-align: left;
      border-bottom: 1px solid #444;
      color: black;
    }

    .summary-section {
      margin-top: 2rem;
      display: flex;
      gap: 1.5rem;
    }

    .card-header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 10px;
    }

    .card-header i {
      font-size: 28px;
      color: #76ff03;
    }
  </style>

  <?php
  include '../../Database/database.php';

  /* ─────────────  COUNTS  ───────────── */
  $vehicle_count = $conn->query("SELECT COUNT(*) total FROM vehicle")->fetch_assoc()['total'];
  $user_count    = $conn->query("SELECT COUNT(*) total FROM user")->fetch_assoc()['total'];
  $driver_count  = $conn->query("SELECT COUNT(*) total FROM driver")->fetch_assoc()['total'];
  $booking_count = $conn->query("SELECT COUNT(*) total FROM booking")->fetch_assoc()['total'];

  /* ─────────────  MONEY  ───────────── */
  $total_income   = $conn->query("SELECT IFNULL(SUM(total_price),0) income FROM booking WHERE bstatus='completed'")
    ->fetch_assoc()['income'];
  /* ─────────────  WEEKLY REVENUE (last 6 weeks)  ───────────── */
  $weeklyData   = [];
  $weeklyLabels = [];
  for ($i = 5; $i >= 0; $i--) {
    $start = date('Y-m-d', strtotime("last monday -$i week"));
    $end   = date('Y-m-d', strtotime("next sunday -$i week"));
    $row   = $conn->query("
          SELECT IFNULL(SUM(total_price),0) revenue
          FROM booking
          WHERE bstatus='completed' AND booking_date BETWEEN '$start' AND '$end'
      ")->fetch_assoc();
    $weeklyData[]   = (float)$row['revenue'];
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
          <h3>Total Vehicles</h3>
        </div>
        <p><?= $vehicle_count ?></p>
      </div>

      <div class="card">
        <div class="card-header">
          <i class="fas fa-user"></i>
          <h3>Total Users</h3>
        </div>
        <p><?= $user_count ?></p>
      </div>

      <div class="card">
        <div class="card-header">
          <i class="fas fa-user-tie"></i>
          <h3>Total Drivers</h3>
        </div>
        <p><?= $driver_count ?></p>
      </div>

      <div class="card">
        <div class="card-header">
          <i class="fas fa-calendar-check"></i>
          <h3>Total Bookings</h3>
        </div>
        <p><?= $booking_count ?></p>
      </div>

      <div class="card">
        <div class="card-header">
          <i class="fas fa-chart-line"></i>
          <h3>Weekly Revenue</h3>
        </div>
        <p>Rs <?= number_format(array_sum($weeklyData)) ?></p>
      </div>
    </div>


    <!-- ====== MONEY SUMMARY ====== -->
    <div class="summary-section">
      <div class="summary-card">
        <h3>Total Income</h3>
        <p style="color:#76ff03;font-size:24px">Rs <?= number_format($total_income) ?></p>
      </div>
    </div>

    <!-- ====== CHART + RECENT ACTIVITY ====== -->
    <div class="extra-activities">
      <div class="chart-card">
        <h3>Weekly Revenue</h3>
        <canvas id="revenueChart"></canvas>
      </div>

      <div class="recent-activity">
        <h3>Recent Activity</h3>
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
