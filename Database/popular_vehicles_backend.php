<?php
include '../../Database/database.php';

$sql = "
    SELECT v.*, 
           IFNULL(AVG(r.rating), 0) AS avg_rating,
           COUNT(r.rating) as rating_count
    FROM vehicle v
    LEFT JOIN ratings r ON v.vehicle_id = r.vehicle_id
    GROUP BY v.vehicle_id
    HAVING rating_count > 0
    ORDER BY avg_rating DESC
    LIMIT 4
";

$result = $conn->query($sql);
?>

<h1 style="padding: 0px 60px;">Popular Vehicles Based on Rating</h1>

<div class="popular-vehicles" style="display: flex; gap: 20px; flex-wrap: nowrap; padding: 0px 60px;">
    <?php while ($row = $result->fetch_assoc()): ?>
        <a href="Book.php?vehicle_id=<?php echo $row['vehicle_id']; ?>"
            style="text-decoration: none; color: inherit; width: 23%; box-sizing: border-box;">
            <div class="vehicle-card" style="border: 1px solid #ccc; padding: 10px;">
                <img src="../../Admin/<?php echo htmlspecialchars($row['vehicle_image']); ?>" style="width: 100%; height: auto;">
                <h3><?php echo htmlspecialchars($row['vehicle_number']); ?></h3>
                <p>Type: <?php echo htmlspecialchars($row['vehicle_type']); ?></p>
                <p>Model: <?php echo htmlspecialchars($row['vehicle_model']); ?></p>
                <p>Rating:
                    <?php
                    $rating = floatval($row['avg_rating']);
                    $fullStars = floor($rating);
                    $hasHalfStar = ($rating - $fullStars) >= 0.25 && ($rating - $fullStars) < 0.75;
                    $extraFull = ($rating - $fullStars) >= 0.75 ? 1 : 0;
                    $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0) - $extraFull;

                    // Full stars
                    for ($i = 0; $i < $fullStars + $extraFull; $i++) {
                        echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="#FFD700" xmlns="http://www.w3.org/2000/svg">
        <path d="M12 2L14.94 8.63L22 9.24L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.24L9.06 8.63L12 2Z"/>
      </svg>';
                    }

                    // Half star
                    if ($hasHalfStar) {
                        echo '<svg width="16" height="16" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <linearGradient id="half">
            <stop offset="50%" stop-color="#FFD700"/>
            <stop offset="50%" stop-color="#ccc"/>
          </linearGradient>
        </defs>
        <path fill="url(#half)" d="M12 2L14.94 8.63L22 9.24L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.24L9.06 8.63L12 2Z"/>
      </svg>';
                    }

                    // Empty stars
                    for ($i = 0; $i < $emptyStars; $i++) {
                        echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="#ccc" xmlns="http://www.w3.org/2000/svg">
        <path d="M12 2L14.94 8.63L22 9.24L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.24L9.06 8.63L12 2Z"/>
      </svg>';
                    }
                    ?>
                </p>
                <p>Price: Rs <?php echo htmlspecialchars($row['vehicle_price']); ?>/day</p>
            </div>
        </a>
    <?php endwhile; ?>
</div>