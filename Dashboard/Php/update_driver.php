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
    <title>Update Driver Details</title>
    <link rel="stylesheet" href="../../Dashboard/Css/style.css">
    <style>
        /* Add any additional styling here */
    </style>
</head>

<body>
    <div class="dashboard_container"><?php include 'dashboard.php'; ?></div>

    <div class="main">
        <button class="black-btn"><a href="ddriver.php">Back</a></button>
        <h2>Update Driver Details</h2>
        <?php
        include 'database.php';

        // Fetch driver details based on driver ID
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["d-id"])) {
            $did = $_POST["d-id"];

            $sql = "SELECT * FROM driver WHERE `d-id`='$did'";
            $result = mysqli_query($conn, $sql);

            if (mysqli_num_rows($result) > 0) {
                // Fetch data
                $row = mysqli_fetch_assoc($result);
        ?>
                <form method="post" action="dupdatedriver.php" onsubmit="return validatePhoneNumber();">
                    <div class="field-group">
                        <input type="hidden" name="d-id" value="<?php echo $row['d-id']; ?>">
                        Name:<br>
                        <input type="text" name="name" id="name" value="<?php echo $row['d-name']; ?>" required /><br>
                        Number (+977):<br>
                        <input type="text" name="number" id="number" value="<?php echo $row['d-number']; ?>" required /><br>
                        Email:<br>
                        <input type="email" name="email" id="email" value="<?php echo $row['d-email']; ?>" required /><br>
                        <input type="submit" value="Update" name="update" class="blue-btn">
                    </div>
                </form>
        <?php
            } else {
                echo "No driver found with ID: $did";
            }
        }

        // Check if form is submitted for driver update
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
            // Get data from the form and update the database
            $did = $_POST["d-id"];
            $name = $_POST["name"];
            $number = $_POST["number"];
            $email = $_POST["email"];

            // Construct SQL query for updating driver
            $sql = "UPDATE driver SET `d-name`='$name', `d-number`='$number', `d-email`='$email' WHERE `d-id`='$did'";
            $result = mysqli_query($conn, $sql);

            // Check if the update was successful
            if ($result) {
                // Redirect to a page to display updated driver details
                header("Location: ddriver.php");
                exit(); // Make sure to stop script execution after redirection
            } else {
                // Display an error message if the update failed
                echo "Error: " . mysqli_error($conn);
            }
        }
        ?>
    </div>

    <script>
        function validatePhoneNumber() {
            var phoneNumber = document.getElementById("number").value;
            var phoneNumberPattern = /^(97|98)\d{8}$/;

            if (!phoneNumberPattern.test(phoneNumber)) {
                alert("Phone number must start with '97' or '98' and be exactly 10 digits long.");
                return false; // Prevent form submission
            }

            return true; // Allow form submission
        }
    </script>
</body>

</html>