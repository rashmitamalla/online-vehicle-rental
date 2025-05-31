<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicles</title>
    <link rel="stylesheet" href="../../Dashboard/Css/style.css">


</head>

<body>
    <div class="main_body">


        <!-- dashboard menu container -->
        <div class="dashboard_container"><?php include 'dashboard.php'; ?></div>

        <!-- main body -->
        <div class="main_inside">
            <?php
            if (isset($_GET['success']) && $_GET['success'] == 1) {
                echo "<div style='color: green; font-weight: bold;'>Vehicle inserted successfully!</div>";
            }
            ?>

            <h1>Vehicles Details</h1>
            <button onclick="location.href='add_vehicle.php';" class="green-btn">Add New Vehicle</button>

            <div class="category">
                <button onclick="filterVehicles('All')" class="green-btn">All</button>
                <button onclick="filterVehicles('Car')" class="green-btn">Car</button>
                <button onclick="filterVehicles('Bus')" class="green-btn">Bus</button>
                <button onclick="filterVehicles('Hiace')" class="green-btn">Hiace</button>
                <button onclick="filterVehicles('Sumo')" class="green-btn">Sumo</button>
            </div>

            <input class="search" type="text" id="searchInput" onkeyup="searchtable()" placeholder="Search by Vehicle-id"><br><br>

            <div class="vehicle_table_container">
                <table class="vehicle_table" id="vehicleTable">
                    <thead>
                        <tr>
                            <th>vehicle_ID</th>
                            <th>Image</th>
                            <th>Type</th>
                            <th>Number</th>
                            <th>Oil</th>
                            <th>People</th>
                            <th>Condition</th>
                            <th>Price/Day</th>
                            <th>Model</th>
                            <th>Status</th>
                            <th>Color</th>
                            <th>Actions</th>
                            <th>Rating</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include __DIR__ . '/../../Database/database.php';
                        $sql = "SELECT * FROM vehicle";
                        $result = mysqli_query($conn, $sql);
                        while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                            <tr>
                                <td><?php echo $row['vehicle_id']; ?></td>
                                <td>
                                    <img src="../../Image/<?php echo $row['vehicle_image']; ?>" alt="Vehicle Image">

                                </td>
                                <td class="vtype"><?php echo $row['vehicle_type']; ?></td>
                                <td><?php echo $row['vehicle_number']; ?></td>
                                <td><?php echo $row['vehicle_oil']; ?></td>
                                <td><?php echo $row['vehicle_people']; ?></td>
                                <td><?php echo $row['vehicle_condition']; ?></td>
                                <td>Rs <?php echo $row['vehicle_price']; ?></td>
                                <td><?php echo $row['vehicle_model']; ?></td>
                                <td><?php echo $row['vehicle_status']; ?></td>
                                <td><?php echo $row['vehicle_color']; ?></td>

                                <td>
                                    <form action="../../Dashboard/Php/update_vehicle.php" method="post" style="display:inline;">
                                        <input type="hidden" name="vehicle_id" value="<?php echo $row['vehicle_id']; ?>">
                                        <button class="blue-btn" type="submit">UPDATE</button>
                                    </form>
                                    <form action="../../Database/delete_vehicle.php" method="post" style="display:inline;">
                                        <input type="hidden" name="vehicle_id" value="<?php echo $row['vehicle_id']; ?>">
                                        <button class="red-btn" type="submit">DELETE</button>
                                    </form>
                                </td>

                                <td>
                                    <button class="btn" type="button" onclick="location.href='view_ratings.php?vehicle_id=<?php echo $row['vehicle_id']; ?>'">
                                        View<br>Rating
                                    </button>

                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        function searchtable() {
            var input = document.getElementById("searchInput");
            var filter = input.value.toUpperCase();
            var table = document.getElementById("vehicleTable");
            var tr = table.getElementsByTagName("tr");
            for (var i = 1; i < tr.length; i++) {
                var td = tr[i].getElementsByTagName("td")[0]; // vehicle_ID column
                if (td) {
                    var txtValue = td.textContent || td.innerText;
                    tr[i].style.display = txtValue.toUpperCase().indexOf(filter) > -1 ? "" : "none";
                }
            }
        }

        function filterVehicles(category) {
            var table = document.getElementById("vehicleTable");
            var tr = table.getElementsByTagName("tr");
            for (var i = 1; i < tr.length; i++) {
                var typeCell = tr[i].getElementsByClassName("vtype")[0];
                if (typeCell) {
                    var type = typeCell.textContent.trim();
                    tr[i].style.display = (category === "All" || type === category) ? "" : "none";
                }
            }
        }
    </script>
</body>

</html>