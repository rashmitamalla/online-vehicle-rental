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
    <title>Bookings History</title>
    <style>
        .status-main {
            padding: 60px 60px;
            display: flex;
            flex-direction: column;
            row-gap: 20px;
        }

        table {

            width: 100%;
            border: 2px solid black;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;

            border: solid 1px black;
        }

        th {
            background-color: #f2f2f2;
        }

        td {
            background-color: white;
        }

        th:last-child,
        td:last-child {
            border-right: none;
        }

        .popup-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .popup-content {
            background: white;
            padding: 20px;
            border-radius: 6px;
            width: 400px;
        }

        .cancel-btn {
            background-color: #d9534f;
            color: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 4px;
        }

        .cancel-btn:hover {
            background-color: #c9302c;
        }
    </style>
</head>

<body>
    <?php
    include "Header.php";
    ?>
    <div class="status-main">
        <h1>
            Booking Details
        </h1>
        <table id="myTable">
            <tr>
                <th>Image</th>
                <th>Full Name</th>
                <th>Number</th>
                <th>vehicle Number</th>
                <th>Pick-up Date</th>
                <th>Pick-up Time</th>
                <th>Return Date</th>
                <th>Return Time</th>
                <th>Pick-up Location</th>
                <th>Price/Day</th>
                <th>Total Price</th>
                <th>Booking Status</th>
                <th>Action</th>
            </tr>

            <?php

            include '../../Database/database.php';
            $username = mysqli_real_escape_string($conn, $_SESSION["username"]);
            $sql = "SELECT booking.*, vehicle.vehicle_image 
        FROM booking 
        JOIN vehicle ON booking.vehicle_number = vehicle.vehicle_number 
        WHERE booking.username = '$username'";
            $result = mysqli_query($conn, $sql);

            while ($row = mysqli_fetch_assoc($result)) {
            ?>

                <tr>
                    <td> <img src="../../Image/<?php echo $row['vehicle_image']; ?>" alt="Vehicle Image">
                    </td>
                    <td><?php echo $row['fullname']; ?></td>
                    <td><?php echo $row['number']; ?></td>
                    <td><?php echo $row['vehicle_number']; ?></td>
                    <td><?php echo $row['pickup_date']; ?></td>
                    <td><?php echo $row['pickup_time']; ?></td>
                    <td><?php echo $row['return_date']; ?></td>
                    <td><?php echo $row['return_time']; ?></td>
                    <td><?php echo $row['pickup_location']; ?></td>
                    <td><?php echo $row['vehicle_price']; ?></td>
                    <td><?php echo $row['total_price']; ?></td>
                    <td><?php echo $row['bstatus']; ?></td>
                    <td>
                        <!-- Inside your booking table row -->
                        <form action="../../Database/cancellation_handle.php" method="POST">
                            <input type="hidden" name="booking_id" value="<?php echo  $row['booking_id'] ?>">
                            <textarea name="cancel_reason" required></textarea>
                            <button type="submit">Cancel Booking</button>
                            <!-- <?php echo 'Booking ID is: ' . $row['booking_id']; ?> -->

                        </form>
                    </td>

                </tr>

            <?php
            }
            if (mysqli_num_rows($result) === 0) {
                echo "<tr><td colspan='13'>No bookings found.</td></tr>";
            }

            ?>
        </table>

        <!-- Cancel Popup Modal (only once in the page) -->
        <!-- Cancel Popup Modal (only once on the page) -->


    </div>









</body>

</html>