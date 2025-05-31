<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    // Get data from the form and update the database
    $adminid = $_POST["adminid"];
    $firstname = $_POST["firstname"];
    $middlename = $_POST["middlename"];
    $lastname = $_POST["lastname"];
    $number = $_POST["number"];
    $email = $_POST["email"];
    $newpassword = $_POST["newpassword"];


    include '../../Database/database.php';
    $sql = "UPDATE `admin` SET `firstname`='$firstname', `middlename`='$middlename', `lastname`='$lastname', `number`='$number', `email`='$email', `password`='$newpassword' WHERE `admin-id`='$adminid'";

    $result = mysqli_query($conn, $sql);

    header("Location: dhome.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Update</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="../../Dashboard/Css/style.css">

    <style>
    </style>
</head>

<body>
    <div class="dashboard_container"><?php include 'dashboard.php'; ?></div>
    <!-- User Update main box -->
    <div class="main">
        <!-- User Update container -->

        <h2>Update Your Information</h2>
        <form action="update_admin.php" method="post" onsubmit="return validateForm();">

            <!-- Retrieve user data from the database -->
            <?php
            // Include database connection
            include '../../Database/database.php';

            $username = $_SESSION["username"];
            $sql = "SELECT * FROM admin WHERE username='$username'";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);
            ?>
            <div class="field-group">
                <input type="hidden" name="adminid" value="<?php echo $row['admin-id']; ?>">
                <label for="firstname">Firstname:</label>
                <input type="text" id="firstname" name="firstname" value="<?php echo $row['firstname']; ?>" required>

                <label for="middlename">Middle Name:</label>
                <input type="text" id="middlename" name="middlename" value="<?php echo $row['middlename']; ?>">

                <label for="lastname">Last Name:</label>
                <input type="text" id="lastname" name="lastname" value="<?php echo $row['lastname']; ?>" required>
                <label for="newpassword">New Password:</label>
                <input type="password" id="newpassword" name="newpassword" placeholder="Not Compulsory to Change">

            </div>
            <div class="field-group">
                <label for="phoneNumber">Phone Number:+977</label>
                <input type="number" id="number" name="number" max-min="10" placeholder="Enter 10 digits" value="<?php echo $row['number']; ?>" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo $row['email']; ?>" required>

                <label for="oldpassword">Old Password:</label>
                <input type="password" id="oldpassword" name="oldpassword" required>
                <br>
                <br>
                <button type="submit" value="Update" name="update" class="red-btn">Update Information</button>
            </div>

        </form>

    </div>

    <script>
        function validateForm() {
            // Get old password and new password from form
            var oldPassword = document.getElementById("oldpassword").value;

            // Perform the check for old password
            // You would need to adjust this to validate against your database
            if (oldPassword !== "<?php echo $row['password']; ?>") {
                alert("Old password doesn't match!");
                return false; // Prevent form submission
            }

            return true; // Allow form submission
        }
    </script>
</body>

</html>