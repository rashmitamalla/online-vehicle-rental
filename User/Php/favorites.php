<?php
session_start();
include_once(__DIR__ . "/../../Database/database.php");

if (!isset($_SESSION['username'])) {
    echo "<p class='text-center text-red-500 mt-10'>You must be logged in to view your favorites.</p>";
    exit;
}

$username = $_SESSION['username'];

// Fetch favorite vehicles for this user
$stmt = $conn->prepare("
    SELECT v.vehicle_id, v.vehicle_image, v.vehicle_model, v.vehicle_price
    FROM wishlist w
    JOIN vehicle v ON w.vehicle_id = v.vehicle_id
    WHERE w.username = ?
    ORDER BY w.liked_at DESC
");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

$favorites = [];
while ($row = $result->fetch_assoc()) {
    $favorites[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Your Favorites</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-4">
    <?php include_once(__DIR__ . "/Header.php"); ?>
  <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow" style="margin-top: 20px;">
    <h2 class="text-xl font-bold mb-4">Your Favorite Vehicles</h2>

    <?php if (empty($favorites)): ?>
      <p class="text-center text-gray-500">You have no favorite vehicles yet.</p>
    <?php else: ?>
      <?php foreach ($favorites as $vehicle): ?>
        <div class="flex items-start gap-4 p-4 border-b hover:bg-gray-50">
          <img src="../../Image/<?= htmlspecialchars($vehicle['vehicle_image']) ?>"
               alt="<?= htmlspecialchars($vehicle['vehicle_model']) ?>"
               class="w-20 h-14 object-cover rounded" />
          <div class="flex-1">
            <a href="Book.php?vehicle_id=<?= $vehicle['vehicle_id'] ?>"
               class="text-md font-semibold text-blue-600 hover:underline">
              <?= htmlspecialchars($vehicle['vehicle_model']) ?>
            </a>
            <p class="text-sm text-gray-600 mt-1">Rs <?= number_format($vehicle['vehicle_price']) ?>/day</p>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</body>
</html>
