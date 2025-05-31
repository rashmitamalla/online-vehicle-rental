<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <link rel="stylesheet" href="../../Dashboard/Css/style.css">

    <style>
        /* Add your custom styles here */
    </style>
</head>

<body>
    <div class="dashboard_container"><?php include 'dashboard.php'; ?></div>

    <?php
    ob_start();
    include '../../Database/database.php';

    // Fetch vehicle details based on vehicle_ID
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $vid = $_POST["vehicle_id"];

        $sql = "SELECT * FROM vehicle WHERE `vehicle_id`='$vid'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            // Fetch data
            $row = mysqli_fetch_assoc($result);
    ?>
            <div class="main">
                <form method="post" action="../Php/update_vehicle.php" enctype="multipart/form-data">
                    <div class="field-group">
                        <h1>Vehicle Details</h1>
                        <input type="hidden" name="vehicle_id" value="<?php echo $row['vehicle_id']; ?>">

                        <label for="vehicle_type">Vehicle type:</label>
                        <select id="vehicle_type" name="vehicle_type">
                            <option value="Bus" <?php if ($row['vehicle_type'] == 'Bus') echo 'selected'; ?>>Bus</option>
                            <option value="Car" <?php if ($row['vehicle_type'] == 'Car') echo 'selected'; ?>>Car</option>
                            <option value="Hiace" <?php if ($row['vehicle_type'] == 'Hiace') echo 'selected'; ?>>Hiace</option>
                            <option value="Sumo" <?php if ($row['vehicle_type'] == 'Sumo') echo 'selected'; ?>>Sumo</option>
                        </select>

                        <label for="vehicle_oil">vehicle_Oil:</label>
                        <select id="vehicle_oil" name="vehicle_oil">
                            <option value="Petrol" <?php if ($row['vehicle_oil'] == 'Petrol') echo 'selected'; ?>>Petrol</option>
                            <option value="Diesel" <?php if ($row['vehicle_oil'] == 'Diesel') echo 'selected'; ?>>Diesel</option>
                        </select>

                        <label for="vehicle_people">vehicle_People:</label>
                        <input type="text" id="vehicle_people" name="vehicle_people" value="<?php echo $row['vehicle_people']; ?>">

                        <label for="vehicle_number">vehicle_Number:</label>
                        <input type="text" id="vehicle_number" name="vehicle_number" value="<?php echo $row['vehicle_number']; ?>">

                        <label for="vehicle_condition">vehicle_Condition:</label>
                        <select id="vehicle_condition" name="vehicle_condition">
                            <option value="Best" <?php if ($row['vehicle_condition'] == 'Best') echo 'selected'; ?>>Best</option>
                            <option value="Good" <?php if ($row['vehicle_condition'] == 'Good') echo 'selected'; ?>>Good</option>
                            <option value="Average" <?php if ($row['vehicle_condition'] == 'Average') echo 'selected'; ?>>Average</option>
                        </select>

                        <label for="vehicle_price">vehicle_Price:</label>
                        <input type="text" id="vehicle_price" name="vehicle_price" value="<?php echo $row['vehicle_price']; ?>">



                        <label for="vehicle_model">vehicle_Model:</label>
                        <input type="text" id="vehicle_model" name="vehicle_model" value="<?php echo $row['vehicle_model']; ?>">

                        <label for="vehicle_color">vehicle_Color:</label>
                        <input type="text" id="vehicle_color" name="vehicle_color" value="<?php echo $row['vehicle_color']; ?>">

                        <label for="vehicle_status">vehicle_Status:</label>
                        <select id="vehicle_status" name="vehicle_status">
                            <option value="Available" <?php if ($row['vehicle_status'] == 'Available') echo 'selected'; ?>>Available</option>
                            <option value="Un-Available" <?php if ($row['vehicle_status'] == 'Un-Available') echo 'selected'; ?>>Un-Available</option>
                        </select>

                        <!-- File upload for new image -->
                        <!-- Show current image -->
                        <?php if (!empty($row['vehicle_image'])): ?>
                            <label>Current Image:</label><br>
                            <img src="<?php echo $row['vehicle_image']; ?>" alt="Vehicle Image" width="200"><br><br>
                        <?php endif; ?>

                        <!-- File input for new image -->
                        <label for="pic">Upload New Image:</label>
                        <input type="file" name="vehicle_image" accept="image/*">

                        <!-- Hidden input to retain old image path if no new image is uploaded -->
                        <input type="hidden" name="old_image" value="<?php echo $row['vehicle_image']; ?>">


                        <input type="submit" class="blue-btn" name="update_action" value="Save Changes">
                    </div>
                </form>
            </div>
    <?php
        } else {
            echo "No vehicle found with vehicle_ID: $vid";
        }
    }

    // Check if form is submitted for update
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_action'])) {
        // Get data from the form and update the database
        $vid = $_POST["vehicle_id"];
        $vType = $_POST["vehicle_type"];
        $vOil = $_POST["vehicle_oil"];
        $vPeople = $_POST["vehicle_people"];
        $vNumber = $_POST["vehicle_number"];
        $vCondition = $_POST["vehicle_condition"];
        $vPrice = $_POST["vehicle_price"];

        // Handle image upload
        if ($_FILES["vehicle_image"]["error"] == 0) {
            $vImage = '../../Image/' . $_FILES["vehicle_image"]["name"];
            move_uploaded_file($_FILES["vehicle_image"]["tmp_name"], $vImage);
        } else {
            $vImage = $_POST["old_image"]; // use old image if no new one
        }

        $vModel = $_POST["vehicle_model"];
        $vColor = $_POST["vehicle_color"];
        $vStatus = $_POST["vehicle_status"];

        // Update vehicle details in the database
        $sql = "UPDATE vehicle SET 
                `vehicle_type`='$vType', `vehicle_oil`='$vOil', `vehicle_people`='$vPeople', `vehicle_number`='$vNumber', 
                `vehicle_condition`='$vCondition', `vehicle_price`='$vPrice', `vehicle_image`='$vImage', 
                `vehicle_model`='$vModel', `vehicle_color`='$vColor', `vehicle_status`='$vStatus'
                WHERE `vehicle_id`='$vid'";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            // Redirect to display the updated vehicle details
            header("Location: display_vehicle.php");
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
    ob_end_flush();
    ?>
</body>

</html>