<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver</title>
    <link rel="stylesheet" href="../../Dashboard/Css/style.css">
    <style>



    </style>
</head>

<body>
    <div class="dashboard_container"><?php include 'dashboard.php'; ?></div>
    <div class="main">
        <!-- User info side table -->
        <div>
            <h1 class="black-h1">
                Driver Details
            </h1>
        </div>
        <div>
            <div>
                <button onclick="location.href='add_driver.php';" class="btn">Add New Driver</button>
                <input class="search" type="text" id="searchInput" onkeyupbtn="searchTable()" placeholder="Search by Driver-ID"><br><br>
            </div>
            <div>
                <table id="myTable">
                    <tr>
                        <th>Id</th>
                        <th>Full-Name</th>
                        <th>Number</th>
                        <th>Email</th>
                        <th></th>
                    </tr>

                    <?php
                    include '../../Database/database.php';
                    $sql = "SELECT * FROM driver ";
                    $result = mysqli_query($conn, $sql);
                    while ($row = mysqli_fetch_assoc($result)) {


                    ?>

                        <tr>
                            <td><?php echo $row['d-id']; ?></td>
                            <td><?php echo $row['d-name']; ?></td>
                            <td><a href="tel:<?php echo $row['d-number']; ?>"><?php echo $row['d-number']; ?></td>
                            <td><a href="mailto:<?php echo $row['d-email']; ?>"><?php echo $row['d-email']; ?></td>
                            <td class="button-group">
                                <form action="update_driver.php" method="post">
                                    <input type="hidden" name="d-id" value="<?php echo $row['d-id']; ?>">
                                    <button class="btn" type="submit">UPDATE</button>
                                </form>
                                <form action="deletedriver.php" method="post">
                                    <input type="hidden" name="d-id" value="<?php echo $row['d-id']; ?>">
                                    <button type="submit" class="btn">DELETE</button>
                                </form>
                            </td>

                        <tr>
                        <?php
                    }
                        ?>
                </table>
            </div>
            <div>
            </div>


            <script>
                function searchTable() {
                    var input, filter, table, tr, td, i, txtValue;
                    input = document.getElementById("searchInput");
                    filter = input.value.toUpperCase();
                    table = document.getElementById("myTable");
                    tr = table.getElementsByTagName("tr");
                    for (i = 0; i < tr.length; i++) {
                        td = tr[i].getElementsByTagName("td")[0]; // index no 0 is d-id in table
                        if (td) {
                            txtValue = td.textContent || td.innerText;
                            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                                tr[i].style.display = "";
                            } else {
                                tr[i].style.display = "none";
                            }
                        }
                    }
                }
            </script>
</body>

</html>