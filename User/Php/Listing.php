<?php
include '../../Database/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$category = isset($_GET['category']) ? $_GET['category'] : '';
$selectedCarType = isset($_POST['car_type']) ? $_POST['car_type'] : [];
$selectedPassengers = isset($_POST['passengers']) ? $_POST['passengers'] : [];

$whereConditions = [];

// Apply category filter from GET if present
if (!empty($category)) {
    // To avoid conflicts with POST filters, remove "All" if category is set
    $whereConditions[] = "v.vehicle_type = '" . mysqli_real_escape_string($conn, $category) . "'";
}

// Apply filters from POST

// Filter by car type, only if POST car_type is set and "All" is not selected and no category filter exists
if (!empty($selectedCarType) && !in_array("All", $selectedCarType)) {
    // If category is already set via GET, maybe ignore car_type POST filter or merge
    // Here, merge with category if category is empty (already handled above)
    if (empty($category)) {
        $escapedTypes = array_map(function ($type) use ($conn) {
            return "'" . mysqli_real_escape_string($conn, $type) . "'";
        }, $selectedCarType);
        $whereConditions[] = "v.vehicle_type IN (" . implode(",", $escapedTypes) . ")";
    }
}

// Filter by passengers
if (!empty($selectedPassengers)) {
    $passengerConditions = [];
    foreach ($selectedPassengers as $range) {
        switch ($range) {
            case "1-2":
                $passengerConditions[] = "(v.vehicle_people BETWEEN 1 AND 2)";
                break;
            case "3-5":
                $passengerConditions[] = "(v.vehicle_people BETWEEN 3 AND 5)";
                break;
            case "6+":
                $passengerConditions[] = "(v.vehicle_people >= 6)";
                break;
        }
    }
    if (!empty($passengerConditions)) {
        $whereConditions[] = "(" . implode(" OR ", $passengerConditions) . ")";
    }
}

// Build WHERE clause
$whereClause = "";
if (!empty($whereConditions)) {
    $whereClause = "WHERE " . implode(" AND ", $whereConditions);
}

// Main query
$sql = "SELECT v.*, AVG(r.rating) AS avg_rating 
        FROM vehicle v 
        LEFT JOIN ratings r ON v.vehicle_id = r.vehicle_id 
        $whereClause 
        GROUP BY v.vehicle_id";

$result = mysqli_query($conn, $sql);

$vehicles = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $vehicles[] = $row;
    }
}

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
    <?php include "Header.php"; ?>

    <div class="main-body-list"></div>



    <div class="top-filter-bar">
        <input type="date" class="input-box" />
        <input type="time" class="input-box" />
        <input type="date" class="input-box" />
        <input type="time" class="input-box" />
        <button class="search-btn">Search</button>
    </div>

    <div class="content-wrapper">
        <div class="sidebar-filter">
            <h3>Filter</h3>
            <form method="post">
                <div class="filter-group">
                    <h4>Car Type</h4>
                    <label><input type="checkbox" name="car_type[]" value="Bus" <?php if (in_array("Bus", $selectedCarType)) echo 'checked'; ?> /> Bus</label>
                    <label><input type="checkbox" name="car_type[]" value="Car" <?php if (in_array("Car", $selectedCarType)) echo 'checked'; ?> /> Car</label>
                    <label><input type="checkbox" name="car_type[]" value="Hiace" <?php if (in_array("Hiace", $selectedCarType)) echo 'checked'; ?> /> Hiace</label>
                    <label><input type="checkbox" name="car_type[]" value="Sumo" <?php if (in_array("Sumo", $selectedCarType)) echo 'checked'; ?> /> Sumo</label>
                    <label><input type="checkbox" name="car_type[]" value="All" <?php if (in_array("All", $selectedCarType)) echo 'checked'; ?> /> All</label>
                </div>
                <div class="filter-group">
                    <h4>Passengers</h4>
                    <label><input type="checkbox" name="passengers[]" value="1-4" <?php if (in_array("1-4", $selectedPassengers)) echo 'checked'; ?> /> 1-2</label>
                    <label><input type="checkbox" name="passengers[]" value="5-9" <?php if (in_array("5-9", $selectedPassengers)) echo 'checked'; ?> /> 5-9</label>
                    <label><input type="checkbox" name="passengers[]" value="10-14" <?php if (in_array("10-14", $selectedPassengers)) echo 'checked'; ?> /> 10-14</label>
                                        <label><input type="checkbox" name="passengers[]" value="10-14" <?php if (in_array("10-14", $selectedPassengers)) echo 'checked'; ?> /> 10-14</label>
                    <label><input type="checkbox" name="passengers[]" value="15+" <?php if (in_array("15+", $selectedPassengers)) echo 'checked'; ?> /> 15+</label>
                </div>
                <button type="submit" class="search-btn">Apply Filters</button>
            </form>
        </div>


       <div>
    <div class="vehicle-list">
        <?php
        // Query for vehicle list with average rating
        $sql = "SELECT v.*, AVG(r.rating) AS avg_rating FROM vehicle v 
                LEFT JOIN ratings r ON v.vehicle_id = r.vehicle_id 
                $whereClause 
                GROUP BY v.vehicle_id";

        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
        ?>
                <div class="vehicle-card">
                    <div class="vehicle-img">
                        <img src="../../image/<?php echo htmlspecialchars($row['vehicle_image']); ?>" alt="Vehicle Image">
                    </div>
                    <div class="vehicle-details">
                        <div class="vehicle-header">
                            <h3>
                                <a style="color: black;" href="Book.php?vehicle_id=<?php echo $row['vehicle_id']; ?>">
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
                                $rating = round($row['avg_rating'], 1); // round to 1 decimal
                                $fullStars = floor($rating);            // full stars
                                $halfStar = ($rating - $fullStars) >= 0.5; // show half star?
                                $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0); // remaining empty stars

                                for ($i = 0; $i < $fullStars; $i++) {
                                    echo '<i class="fa fa-star" style="color: gold;"></i>';
                                }
                                if ($halfStar) {
                                    echo '<i class="fa fa-star-half-alt" style="color: gold;"></i>';
                                }
                                for ($i = 0; $i < $emptyStars; $i++) {
                                    echo '<i class="fa-regular fa-star" style="color: gold;"></i>';
                                }
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
                                <input type="hidden" name="booking_token" value="<?php echo $token; ?>">
                                <button type="submit" class="book-btn">Book Now</button>
                            </form>
                        </div>
                    </div>
                </div>
        <?php
            }
        } else {
            echo "<p>No vehicles found.</p>";
        }
        ?>
    </div> <!-- close vehicle-list -->
 <!-- close content-wrapper -->
    </div> <!-- close main-body-list -->


    </div>



    
</body>

</html>