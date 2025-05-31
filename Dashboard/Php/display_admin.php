<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="stylesheet" href="../../Dashboard/Css/style.css">


</head>

<body>
    <div class="dashboard_container"><?php include 'dashboard.php'; ?></div>
    <div class="main">
        <h1>Admin Details </h1>


        <div>
            <button onclick="location.href='add_admin.php';" class="green-btn">Add New Admin</button>
            <input class="search" type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search by User-Name"><br><br>

            <div>
                <table id="myTable">
                    <tr>
                        <th>Id</th>
                        <th>First-Name</th>
                        <th>Middle-Name</th>
                        <th>Last-Name</th>
                        <th>Number</th>
                        <th>Email</th>
                        <th>Username</th>
                    </tr>

                    <?php
                    include '../../Database/database.php';
                    $sql = "SELECT * FROM admin ";
                    $result = mysqli_query($conn, $sql);
                    while ($row = mysqli_fetch_assoc($result)) {


                    ?>

                        <tr>
                            <td><?php echo $row['admin-id']; ?></td>
                            <td><?php echo $row['firstname']; ?></td>
                            <td><?php echo $row['middlename']; ?></td>
                            <td><?php echo $row['lastname']; ?></td>
                            <td><?php echo $row['number']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td><?php echo $row['username']; ?></td>

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
                        td = tr[i].getElementsByTagName("td")[6]; // index no 6 is username in table
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