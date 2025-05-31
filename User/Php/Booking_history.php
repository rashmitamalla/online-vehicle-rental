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
                <th>Action</th>
            </tr>

            <?php

            include '../../Database/database.php';
            $username = mysqli_real_escape_string($conn, $_SESSION["username"]);
            $sql = "SELECT * FROM booking WHERE `username`='$username'";
            $result = mysqli_query($conn, $sql);

            while ($row = mysqli_fetch_assoc($result)) {
            ?>

                <tr>
                    <td><?php echo $row['fullname']; ?></td>
                    <td><?php echo $row['username']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['number']; ?></td>
                    <td><?php echo $row['vehicle_id']; ?></td>
                    <td><?php echo $row['pickup_date']; ?></td>
                    <td><?php echo $row['pickup_time']; ?></td>
                    <td><?php echo $row['return_date']; ?></td>
                    <td><?php echo $row['return_time']; ?></td>
                    <td><?php echo $row['pickup_location']; ?></td>
                    <td><?php echo $row['vehicle_price']; ?></td>
                    <td><?php echo $row['total_price']; ?></td>
                    <td><?php echo $row['bstatus']; ?></td>
                    <td>
                        <button class="cancel-btn" onclick="openCancelPopup(<?= $row['booking_id'] ?>)">Cancel Booking</button>
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
        <div id="cancelPopup" class="popup-modal" style="display:none;">
            <div class="popup-content">
                <h3>Cancel Booking</h3>
                <p>A fine charge of ₹500 applies for cancellation.</p>
                <form id="cancelForm" method="POST" action="../../User/Php/book_vehicle_backend.php">
                    <input type="hidden" name="booking_id" id="bookingIdInput" value="">
                    <label for="cancel_reason">Reason for cancellation:</label><br />
                    <textarea id="cancel_reason" name="cancel_reason" required rows="4" cols="40"></textarea><br />

                    <button type="submit" id="confirmBtn">Confirm Cancellation</button>
                    <button type="button" onclick="closeCancelPopup()" id="abortBtn">Abort</button>

                    <p id="cancelMessage" style="display:none; color: green; margin-top:10px;">Cancellation is in process...</p>
                </form>
            </div>
        </div>

    </div>


    <script>
        const cancelForm = document.getElementById('cancelForm');
        cancelForm.addEventListener('submit', function(event) {
            event.preventDefault(); // stop default submit for now

            // Show cancellation in process message (you can style this as you want)
            let msgDiv = document.createElement('div');
            msgDiv.innerText = 'Cancellation is in process...';
            msgDiv.style.color = 'blue';
            msgDiv.style.marginTop = '10px';

            cancelForm.appendChild(msgDiv);

            // Optionally disable form inputs to prevent double submit
            [...cancelForm.elements].forEach(el => el.disabled = true);

            // Now submit the form programmatically after short delay (optional)
            setTimeout(() => {
                cancelForm.submit();
            }, 500); // half second delay to show message

        });
    </script>



</body>

</html>