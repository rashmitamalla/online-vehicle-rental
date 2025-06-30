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

<style>
    .car-card {
  width: 300px;
  border: 1px solid #ddd;
  border-radius: 12px;
  overflow: hidden;
  font-family: Arial, sans-serif;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  background: #fff;
}

.car-image {
  width: 100%;
  height: 180px;
  object-fit: cover;
}

.car-details {
  padding: 16px;
}

.car-title {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.car-title h3 {
  margin: 0;
  font-size: 18px;
}

.likes {
  font-size: 14px;
  color: #666;
}

.car-icons {
  display: flex;
  justify-content: space-between;
  font-size: 14px;
  margin: 10px 0;
  color: #444;
}

.car-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.price small {
  color: #888;
  font-size: 12px;
}

.price strong {
  font-size: 18px;
  color: #000;
}

.rent-button {
  background-color: #27ae60;
  color: white;
  border: none;
  padding: 8px 12px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: bold;
  font-size: 14px;
}

.rent-button:hover {
  background-color: #219150;
}
</style>

<h1 style="padding: 0px 60px;">Popular Vehicles Based on Rating</h1>

<div class="popular-vehicles" style="display: flex; gap: 20px; flex-wrap: nowrap; overflow-x: auto; padding: 0px 60px;">
    <?php 
    function renderStarRating($rating) {
        $fullStars = floor($rating);
        $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0;
        $emptyStars = 5 - $fullStars - $halfStar;

        // Output full stars
        for ($i = 0; $i < $fullStars; $i++) {
            echo '<i class="fa-solid fa-star" style="color: gold;"></i>';
        }

        // Output half star
        if ($halfStar) {
            echo '<i class="fa-solid fa-star-half-stroke" style="color: gold;"></i>';
        }

        // Output empty stars
        for ($i = 0; $i < $emptyStars; $i++) {
            echo '<i class="fa-regular fa-star" style="color: gold;"></i>';
        }
    }

    while ($row = $result->fetch_assoc()):
    ?>
        <a href="Book.php?vehicle_id=<?php echo $row['vehicle_id']; ?>" class="vehicle-card-link" style="text-decoration: none; color: inherit; width: 23%; box-sizing: border-box;">
            <div class="vehicle-card" style="border: 1px solid #ccc; border-radius: 12px; overflow: hidden; background: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.05); transition: transform 0.3s;">
                <img src="../../Admin/<?php echo htmlspecialchars($row['vehicle_image']); ?>" alt="Vehicle Image" class="vehicle-img" style="width: 100%; height: 180px; object-fit: cover;">
                <div class="vehicle-info" style="padding: 12px 14px;">
                    <h3 style="margin: 0 0 6px 0;"><?php echo htmlspecialchars($row['vehicle_model']); ?></h3>
                    
                    <p class="price" style="margin-top: 8px; font-weight: bold;">Price: <strong>Rs <?php echo htmlspecialchars($row['vehicle_price']); ?></strong>/day</p>

                    <p style="margin: 4px 0;">
                        Rating:
                        <?php renderStarRating(floatval($row['avg_rating'])); ?>
                    </p>
                </div>
            </div>
        </a>
    <?php endwhile; ?>
</div>
