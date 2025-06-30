<?php
ob_start();
include '../../Database/database.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bid = $_POST["booking_id"];
    $bstatus = $_POST["bstatus"];

    var_dump($bstatus, $bid); // DEBUG

    $sql = "UPDATE booking SET `bstatus` = ? WHERE `booking_id` = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die("Prepare failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "si", $bstatus, $bid);
    $result = mysqli_stmt_execute($stmt);

    if (!$result) {
        echo "Execute failed: " . mysqli_stmt_error($stmt);
    } else {
        header("Location: display_booking_detail.php");
        exit;
    }
}


ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin|Bookings</title>
    <link rel="stylesheet" href="../../Dashboard/Css/style.css">



</head>

<body>


    <div class="dashboard_container"><?php include 'dashboard.php'; ?></div>

    <div class="main">
        <h1 style="margin-top:60px">
            Booking Details
        </h1>

        <div>
            <div>
                <input class="search" type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search by Username"><br><br>
            </div>
            <div>
                <table id="myTable">
                    <tr>
                        <th>Booking ID</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Number</th>
                        <th>V-ID</th>
                        <th>Pick-up Date</th>
                        <th>Pick-up Time</th>
                        <th>Return Date</th>
                        <th>Return Time</th>
                        <th>Pick-up Location</th>
                        <th>Price/Day</th>
                        <th>Total Price</th>
                        <th>Booking Status</th>

                        <th>Cancellation Reason</th>

                    </tr>

                    <?php

                    include '../../Database/database.php';
                    $sql = "SELECT * FROM booking ORDER BY booking_id DESC";
                    $result = mysqli_query($conn, $sql);
                    while ($row = mysqli_fetch_assoc($result)) {
                    ?>

                        <tr>
                            <!-- <td><?php echo $row['bstatus']; ?></td> -->
                            <td><?php echo $row['booking_id']; ?></td>
                            <td><?php echo $row['fullname']; ?></td>
                            <td><?php echo $row['username']; ?></td>
                            <td><a href="mailto:<?php echo $row['email']; ?>"><?php echo $row['email']; ?></td>
                            <td><a href="tel:<?php echo $row['number']; ?>"><?php echo $row['number']; ?></td>
                            <td><?php echo $row['vehicle_id']; ?></td>
                            <td><?php echo $row['pickup_date']; ?></td>
                            <td><?php echo $row['pickup_time']; ?></td>
                            <td><?php echo $row['return_date']; ?></td>
                            <td><?php echo $row['return_time']; ?></td>
                            <td><?php echo $row['pickup_location']; ?></td>
                            <td><?php echo $row['vehicle_price']; ?></td>
                            <td><?php echo $row['total_price']; ?></td>
                            <td>
                                <form method="post" action="../../Database/update_booking_status.php" style="display: flex; flex-direction: column; align-items: flex-start; ">
                                    <input type="hidden" name="booking_id" value="<?php echo $row['booking_id']; ?>">
                                    <select style="font-size: 14px;" id="bstatus" name="bstatus" onchange="confirmCancellation(this)">
                                        <option value="pending" <?php if ($row['bstatus'] == "pending") echo 'selected'; ?>>Pending</option>
                                        <option value="approved" <?php if ($row['bstatus'] == "approved") echo 'selected'; ?>>Approved</option>
                                        <option value="denied" <?php if ($row['bstatus'] == "denied") echo 'selected'; ?>>Denied</option>
                                        <option value="completed" <?php if ($row['bstatus'] == "completed") echo 'selected'; ?>>Completed</option>
                                        <option value="cancelled" <?php if ($row['bstatus'] == "cancelled") echo 'selected'; ?>>Cancelled</option>
                                    </select>
                                                                        <input  style="font-size: 14px;" type="submit" class="update" value="update">


                                    
                                </form>
                            </td>

                            <td>
                                <?php
                                if (!empty($row['cancel_reason'])) {
                                    echo nl2br(htmlspecialchars($row['cancel_reason']));
                                } else {
                                    echo "-";
                                }
                                ?>
                            </td>

                        </tr>

                    <?php
                    }

                    ?>
                </table>
            </div>
            <div>
            </div>


</body>

<script>
    function searchTable() {
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("searchInput");
        filter = input.value.toUpperCase();
        table = document.getElementById("myTable");
        tr = table.getElementsByTagName("tr");
        for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td")[2]; // Index 2 is username in table
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



    function confirmCancellation(selectElement) {
        if (selectElement.value === "cancelled") {
            const confirmed = confirm("Only cancel a booking after receiving a valid cancellation request from the renter via call. Are you sure?");
            if (!confirmed) {
                // Revert the selection back
                // Optional: You could reload or reset to previous value if you store it
                selectElement.value = "approved"; // or previous value
            }
        }
    }
</script>
</body>

</html>