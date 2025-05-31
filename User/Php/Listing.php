<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle filter inputs
$selectedCarType = isset($_POST['car_type']) ? $_POST['car_type'] : [];
$selectedPassengers = isset($_POST['passengers']) ? $_POST['passengers'] : [];
$whereConditions = [];

// Car Type Filter
if (!empty($selectedCarType) && !in_array("All", $selectedCarType)) {
    $types = implode("','", $selectedCarType);
    $whereConditions[] = "`vehicle_type` IN ('$types')";
}

// Passengers Filter
if (!empty($selectedPassengers)) {
    $passengerConditions = [];
    foreach ($selectedPassengers as $passenger) {
        if ($passenger == "1-2") {
            $passengerConditions[] = "`vehicle_people` BETWEEN 1 AND 2";
        } elseif ($passenger == "3-5") {
            $passengerConditions[] = "`vehicle_people` BETWEEN 3 AND 5";
        } elseif ($passenger == "6+") {
            $passengerConditions[] = "`vehicle_people` >= 6";
        }
    }
    if (!empty($passengerConditions)) {
        $whereConditions[] = "(" . implode(" OR ", $passengerConditions) . ")";
    }
}

// Combine conditions
$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) . " AND `vehicle_status`='Available'" : "WHERE `vehicle_status`='Available'";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Vehicle Listing</title>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places"></script>

    <link rel="stylesheet" href="../Css/vehicle_list.css" />
    <!-- <link rel="stylesheet" href="../Css/style.css" /> -->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

</head>

<body>
    <?php include "Header.php"; ?>

    <div class="main-body-list">


    </div>


    <!-- Top Search Filter Bar -->
    <div class="top-filter-bar">

        <input type="date" class="input-box" />
        <input type="time" class="input-box" />
        <input type="date" class="input-box" />
        <input type="time" class="input-box" />
        <button class="search-btn">Search</button>
    </div>

    <div class="content-wrapper">

        <!-- Sidebar Filters -->
        <!-- Sidebar Filters -->
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
                    <label><input type="checkbox" name="passengers[]" value="1-2" <?php if (in_array("1-2", $selectedPassengers)) echo 'checked'; ?> /> 1-2</label>
                    <label><input type="checkbox" name="passengers[]" value="3-5" <?php if (in_array("3-5", $selectedPassengers)) echo 'checked'; ?> /> 3-5</label>
                    <label><input type="checkbox" name="passengers[]" value="6+" <?php if (in_array("6+", $selectedPassengers)) echo 'checked'; ?> /> 6+</label>
                </div>
                <button type="submit" class="search-btn">Apply Filters</button>
            </form>
        </div>


        <!-- Vehicle Listing Cards -->
        <div class="vehicle-list">
            <?php
            include '../../Database/database.php';
            $sql = "SELECT * FROM vehicle WHERE `vehicle_status`='Available'";
            $result = mysqli_query($conn, $sql);
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $discount = rand(10, 25); // Example discount
                    $originalPrice = $row['vehicle_price'] + ($row['vehicle_price'] * ($discount / 100));
            ?>
                    <div class="vehicle-card">
                        <div class="vehicle-img">
                            <img src="../../image/<?php echo $row['vehicle_image']; ?>" alt="Vehicle Image">
                        </div>
                        <div class="vehicle-details">
                            <div class="vehicle-header">
                                <h3><?php echo $row['vehicle_model']; ?> <span class="badge"><?php echo $row['vehicle_type']; ?></span></h3>
                                <div class="features">
                                    <span><i class="fa fa-users"></i> <?php echo $row['vehicle_people']; ?></span>
                                    <span><i class="fa fa-cog"></i> <?php echo $row['vehicle_oil']; ?></span>
                                    <span><i class="fa fa-snowflake"></i> A/C</span>
                                </div>
                                <div class="services">
                                    <span class="tag success">Free Cancellation</span>
                                    <span class="tag success">Instantly Confirmed</span>
                                    <span class="tag success">Free Wifi</span>
                                </div>
                                <div class="rating">
                                    <span class="rating-score"><?php echo number_format(rand(3, 5), 1); ?></span>
                                    <span class="rating-text"><?php echo rand(100, 400); ?> reviews</span>
                                </div>
                            </div>
                            <div class="vehicle-footer">
                                <div class="price-section">
                                    <span class="discount"><?php echo $discount; ?>% Off!</span>
                                    <span class="original-price">$<?php echo number_format($originalPrice); ?></span>
                                    <span class="final-price">$<?php echo number_format($row['vehicle_price']); ?>/day</span>
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
                echo "Error: " . mysqli_error($conn);
            }
            ?>
        </div>

    </div>
    </div>

    <?php include "Footer.php"; ?>



</body>

</html>