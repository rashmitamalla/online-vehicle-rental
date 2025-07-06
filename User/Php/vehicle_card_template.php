<?php if (!isset($row)) return; ?>
<div class="vehicle-card">
  <div class="vehicle-img">
    <img src="../../image/<?php echo htmlspecialchars($row['vehicle_image']); ?>" alt="Vehicle Image">
  </div>
  <div class="vehicle-details">
    <div class="vehicle-header">
      <h3>
        <a style="color: black; text-decoration: none;" href="Book.php?vehicle_id=<?php echo $row['vehicle_id']; ?>">
          <?php echo htmlspecialchars($row['vehicle_model']); ?>
        </a>
        <span class="badge"><?php echo htmlspecialchars($row['vehicle_type']); ?></span>
      </h3>
      <div class="features">
        <span><i class="fa fa-users"></i> <?php echo htmlspecialchars($row['vehicle_people']); ?></span>
        <span><i class="fa fa-cog"></i> <?php echo htmlspecialchars($row['vehicle_oil']); ?></span>
        <span><i class="fa fa-snowflake"></i> A/C</span>
      </div>
      <div class="services">
        <span class="tag success">Cancellation</span>
        <span class="tag success">Instantly Confirmed</span>
        <span class="tag success">Free Wifi</span>
      </div>
      <div class="rating">
        <?php
        $rating = round($row['avg_rating'], 1);
        $fullStars = floor($rating);
        $halfStar = ($rating - $fullStars) >= 0.5;
        $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);

        for ($i = 0; $i < $fullStars; $i++) echo '<i class="fa fa-star" style="color: gold;"></i>';
        if ($halfStar) echo '<i class="fa fa-star-half-alt" style="color: gold;"></i>';
        for ($i = 0; $i < $emptyStars; $i++) echo '<i class="fa-regular fa-star" style="color: gold;"></i>';
        ?>
        <span class="rating-score" style="margin-left: 8px;"><?php echo number_format($rating, 1); ?></span>
      </div>
    </div>
    <div class="vehicle-footer">
      <div class="price-section">
        <span class="final-price">Rs<?php echo number_format($row['vehicle_price']); ?>/day</span>
      </div>
      <form action="<?php echo isset($_SESSION['username']) ? '../Php/book.php' : '../../Auth/Php/login.php'; ?>" method="post">
        <input type="hidden" name="vehicle_id" value="<?php echo htmlspecialchars($row['vehicle_id']); ?>">
        <input type="hidden" name="booking_token" value="<?php echo $token ?? ''; ?>">
        <button type="submit" class="book-btn">Book Now</button>
      </form>
    </div>
  </div>
</div>
