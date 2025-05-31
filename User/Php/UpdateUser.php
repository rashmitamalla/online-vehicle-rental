<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    // Get data from the form and update the database
    $user_id = $_POST["userid"];
    $firstname = $_POST["firstname"];
    $middlename = $_POST["middlename"];
    $lastname = $_POST["lastname"];
    $phone_number = $_POST["number"];
    $email = $_POST["email"];
    $newpassword = $_POST["newpassword"];


    include '../../Database/database.php';
    $sql = "UPDATE `user` SET `firstname`='$firstname', `middlename`='$middlename', `lastname`='$lastname', `number`='$phone_number', `email`='$email', `password`='$newpassword' WHERE `userid`='$user_id'";

    $result = mysqli_query($conn, $sql);

    header("Location: Home.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Update</title>
    <link rel="stylesheet" href="../../User/Css/style.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer">

    <style>
        .edit-profile-main {
            padding: 80px 60px;
            width: 80%;
            display: flex;
            flex-direction: column;
            row-gap: 20px;
        }

        .edit-profile-main>form {
            display: flex;
            flex-wrap: wrap;
            row-gap: 20px;
            column-gap: 20px;

        }
    </style>
</head>

<body>
    <?php
    include "Header.php";
    ?>
    <!-- User Update edit-profile-main box -->
    <div class="edit-profile-main">

        <!-- User Update container -->


        <h1>Update Your Information</h1>
        <form action="UpdateUser.php" method="post" onsubmit="return validateForm();">

            <!-- Retrieve user data from the database -->
            <?php
            // Include database connection
            include '../../Database/database.php';

            $username = $_SESSION["username"];
            $sql = "SELECT * FROM user WHERE username='$username'";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);
            ?>
            <div class="field-group">
                <div>
                    <input type="hidden" name="userid" value="<?php echo $row['userid']; ?>">
                    <label for="firstname">Firstname:</label>
                    <input type="text" id="firstname" name="firstname" value="<?php echo $row['firstname']; ?>" required>
                </div>
                <div>
                    <label for="middlename">Middle Name:</label>
                    <input type="text" id="middlename" name="middlename" value="<?php echo $row['middlename']; ?>">
                </div>

                <div>
                    <label for="lastname">Last Name:</label>
                    <input type="text" id="lastname" name="lastname" value="<?php echo $row['lastname']; ?>" required>
                </div>
            </div>
            <div class="field-group">
                <div>
                    <label for="phoneNumber">Phone Number:+977</label>
                    <input type="number" id="number" name="number" max-min="10" placeholder="Enter 10 digits" value="<?php echo $row['number']; ?>" required>

                </div>
                <div>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo $row['email']; ?>" required>

                </div>
            </div>

            <div class="field-group">

                <div>
                    <label for="oldpassword">Old Password:</label>
                    <input type="password" id="oldpassword" name="oldpassword" required>

                </div>
                <div>
                    <label for="newpassword">New Password:</label>
                    <input type="password" id="newpassword" name="newpassword" placeholder="Not Compulsory to Change">
                </div>
            </div>
            <div class="field-group"><button type="submit" value="Update" name="update" class="red-btn">Update Information</button></div>
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