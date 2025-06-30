<?php if (!isset($row)) return; ?>
<div class="vehicle-card">
  <div class="vehicle-img">
    <img src="../../image/<?php echo htmlspecialchars($row['vehicle_image']); ?>" alt="Vehicle Image">
  </div>

  <div class="vehicle-body">
    <h3 class="vehicle-title">
      <a href="Book.php?vehicle_id=<?php echo $row['vehicle_id']; ?>">
        <?php echo htmlspecialchars($row['vehicle_model']); ?>
      </a>
      <span class="vehicle-type"><?php echo htmlspecialchars($row['vehicle_type']); ?></span>
    </h3>

    <div class="vehicle-meta">
      <span><i class="fa fa-users"></i> <?php echo htmlspecialchars($row['vehicle_people']); ?> Seats</span>
      <span><i class="fa fa-cog"></i> <?php echo htmlspecialchars($row['vehicle_oil']); ?></span>
      <span><i class="fa fa-snowflake"></i> A/C</span>
    </div>

    <div class="vehicle-rating">
      <?php
      $rating = round($row['avg_rating'], 1);
      $fullStars = floor($rating);
      $halfStar = ($rating - $fullStars) >= 0.5;
      $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);

      for ($i = 0; $i < $fullStars; $i++) echo '<i class="fa fa-star" style="color: gold;"></i>';
      if ($halfStar) echo '<i class="fa fa-star-half-alt" style="color: gold;"></i>';
      for ($i = 0; $i < $emptyStars; $i++) echo '<i class="fa-regular fa-star" style="color: gold;"></i>';
      ?>
      <span class="rating-score"><?php echo number_format($rating, 1); ?></span>
    </div>

    <div class="vehicle-price">
      <strong>Rs <?php echo number_format($row['vehicle_price']); ?></strong>/day
    </div>

    <a href="Book.php?vehicle_id=<?php echo $row['vehicle_id']; ?>" class="recommend-btn">Book Now</a>
  </div>
</div>

<style>
    .vehicle-card {
  background-color: #ffffff;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  display: flex;
 
 
  transition: transform 0.3s ease;
}

.vehicle-card:hover {
  transform: translateY(-5px);
}

.vehicle-img img {
  width: 100%;
  height: 160px;
  object-fit: cover;
  border-bottom: 1px solid #eee;
}

.vehicle-body {
  padding: 16px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.vehicle-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 8px;
}

.vehicle-title a {
  color: #007bff;
  text-decoration: none;
}

.vehicle-title a:hover {
  text-decoration: underline;
}

.vehicle-type {
  background-color: #eee;
  color: #555;
  padding: 2px 6px;
  font-size: 0.75rem;
  border-radius: 5px;
  margin-left: 10px;
}

.vehicle-meta {
  font-size: 0.9rem;
  color: #666;
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 10px;
}

.vehicle-rating {
  margin: 10px 0;
  font-size: 0.9rem;
  color: #555;
  display: flex;
  align-items: center;
}

.vehicle-rating i {
  margin-right: 2px;
}

.rating-score {
  margin-left: 6px;
  font-weight: bold;
  color: #444;
}

.vehicle-price {
  font-size: 1rem;
  font-weight: bold;
  color: #000;
  margin-bottom: 12px;
}

.recommend-btn {
  text-align: center;
  background-color:rgb(231, 231, 231);
  color: Black;
  padding: 10px 0;
  border-radius: 6px;
  text-decoration: none;
  font-weight: 600;
  transition: background-color 0.3s ease;
}

.recommend-btn:hover {
  background-color: #218838;
}

</style>