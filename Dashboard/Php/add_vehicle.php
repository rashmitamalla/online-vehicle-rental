<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Vehicle</title>
    <link rel="stylesheet" href="../../Dashboard/Css/style.css">
</head>

<body>
    <div class="dashboard_container"><?php include 'dashboard.php'; ?></div>


    <div class="main">
        <button class="black-btn"><a href="display_vehicle.php">Back</a></button>

        <?php if (isset($_GET['error']) && $_GET['error'] == 'duplicate'): ?>
            <p style="color: red; font-weight: bold;">Vehicle number already exists. Please enter a unique vehicle number.</p>
        <?php endif; ?>

        <h2>Insert Details Of Vehicle</h2>
        <form action="../../Database/insert_vehicle.php" method="post" enctype="multipart/form-data">

            <div class="field-group">

                <label for="pic">Upload Image:</label>
                <input type="file" name="pic" accept="image/*" required>

                <select id="type" name="type">
                    <option value="">--select vehicle type--</option>
                    <option value="Bus">Bus</option>
                    <option value="Car">Car</option>
                    <option value="Hiace">Hiace</option>
                    <option value="Sumo">Sumo</option>
                    <option value="Truck">Mini-Truck</option>
                    <option value="Scorpio">Scorpio</option>
                    <option value="Hilux">Hilux</option>


                </select>
                <select id="oil" name="oil">
                    <option value="">--select fuel type--</option>
                    <option value="Petrol">Petrol</option>
                    <option value="Diesel">Diesel</option>
                                        <option value="Electric">Electric</option>

                </select>
                <input type="text" name="color" id="color" placeholder="Enter vehicle color" required />
                <input type="text" name="price" id="price" placeholder="Enter price per day" required />
                <input type="submit" value="Submit" class="blue-btn">
            </div>
            <div class="field-group">
                <select id="status" name="status">
                    <!-- <option value="">--select avaibility of Vehicle</option> -->
                    <option value="Available">Available</option>
                    <option value="Un-Available">Un-Available</option>
                </select>
                <select id="condition" name="condition">
                    <option value="">--select condition of vehicle</option>
                    <option value="Best">Best</option>
                    <option value="Good">Good</option>
                    <option value="Average">Average</option>
                </select>

                <input type="text" name="people" id="people" placeholder="Enter the number of people" required />
                <input type="text" name="number" id="number" placeholder="Enter Vehicle plate number" required />
                <input type="text" name="brand" id="brand" placeholder="Enter brand Name" required />
                <input type="text" name="model" id="model" placeholder="Enter Model Number" required />




            </div>




        </form>

    </div>
</body>

</html>