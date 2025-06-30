<?php session_start();
include '../../Database/database.php';

$username = $_SESSION['username'] ?? null; // get logged-in user ID
$booking_id = $_GET['booking_id'] ?? null;

if (!$booking_id || !$username) {
    die("Missing booking ID or user not logged in.");
}

// Get vehicle details for booking
$sql = "SELECT v.vehicle_id, v.vehicle_number, v.vehicle_model, v.vehicle_type,
v.vehicle_image, v.vehicle_color
FROM booking b
JOIN vehicle v ON b.vehicle_id = v.vehicle_id
WHERE b.booking_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$row = $result->fetch_assoc()) {
    die("Invalid booking or vehicle not found.");
}

$vehicle_id = $row['vehicle_id'];

// Check if user already rated this vehicle for this booking (or just user+vehicle)
$sql_rating = "SELECT rating, feedback FROM ratings WHERE booking_id = ? AND vehicle_id = ? AND username = ?";
$stmt_rating = $conn->prepare($sql_rating);
$stmt_rating->bind_param("iii", $booking_id, $vehicle_id, $username);
$stmt_rating->execute();
$result_rating = $stmt_rating->get_result();

$existing_rating = null;
if ($row_rating = $result_rating->fetch_assoc()) {
    $existing_rating = $row_rating;
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Rate Vehicle</title>
    <style>
        /* Your styles here */
    </style>
</head>

<body>

    <h2>Rate Your Booking </h2>

    <div class="vehicle-card">
        <img src="../../uploads/<?= htmlspecialchars($row['vehicle_image']) ?>" alt="Vehicle" style="width:100px;height:100px;object-fit:cover;">
        <div>
            <h3><?= htmlspecialchars($row['vehicle_model']) ?> (<?= htmlspecialchars($row['vehicle_number']) ?>)</h3>
            <p>Type: <?= htmlspecialchars($row['vehicle_type']) ?></p>
            <p>Color: <?= htmlspecialchars($row['vehicle_color']) ?></p>
        </div>
    </div>

    <?php if ($existing_rating): ?>
        <p style="color: green; font-weight: bold;">
            You have already rated this vehicle. You can update your rating below.
        </p>
    <?php endif; ?>

    <form action="submit_rating.php" method="post">
        <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking_id) ?>">
        <input type="hidden" name="vehicle_id" value="<?= htmlspecialchars($vehicle_id) ?>">

        <label for="rating">Your Rating:</label>
        <select name="rating" required>
            <option value="">--Select Rating--</option>
            <?php
            $ratings = [5 => '★★★★★ (Excellent)', 4 => '★★★★ (Good)', 3 => '★★★ (Average)', 2 => '★★ (Poor)', 1 => '★ (Very Poor)'];
            foreach ($ratings as $val => $label) {
                $selected = ($existing_rating && $existing_rating['rating'] == $val) ? 'selected' : '';
                echo "<option value='$val' $selected>$label</option>";
            }
            ?>
        </select><br><br>

        <label for="feedback">Feedback:</label><br>
        <textarea name="feedback" rows="4" cols="40" placeholder="Write something (optional)..."><?= htmlspecialchars($existing_rating['feedback'] ?? '') ?></textarea><br><br>

        <input type="submit" value="<?= $existing_rating ? 'Update Rating' : 'Submit Rating' ?>">
    </form>

</body>

</html>

<?php
$stmt->close();
$stmt_rating->close();
$conn->close();
?>