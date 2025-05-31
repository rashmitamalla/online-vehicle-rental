<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Forgot Password</title>
    <style>
       
       
        .message {
            margin-top: 20px;
            color: #333;
        }
        .contact-info {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="forget-main">
        <div class="forget-container">
        <button type="button" class="black-btn"><a href="login.php">Back</a></button>
            <h1 class="black-h1">Forgot Password</h1>
            <p>Please Enter Your old Username with Connected E-mail.</p>
            <form method="post" class="form">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required><br>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required><br><br>

                <input type="submit" name="submit" value="Submit" class="red-btn">
            </form>

            <?php
            // Include the database connection file
            include 'database.php';
            function generatePassword($length = 8) {
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()-_';
                $password = '';
                $max = strlen($characters) - 1;
    
                for ($i = 0; $i < $length; $i++) {
                    $password .= $characters[mt_rand(0, $max)];
                }
    
                return $password;
            }

            if(isset($_POST['submit'])) {
            // Retrieve input values
                $username = $_POST['username'];
                $email = $_POST['email'];

            // Query to check if username and email match in the database
                $query = "SELECT * FROM user WHERE username='$username' AND email='$email'";
                $result = mysqli_query($conn, $query);

                if(mysqli_num_rows($result) == 1) {
                    $newPassword = generatePassword();
                echo "Your new password is: $newPassword";
                
                $newp = "UPDATE user SET password='$newPassword' WHERE username='$username'";
                mysqli_query($conn, $newp);
                
                } else {
                // Username and email do not match
                echo "<p class='message'>Invalid username or email. They don't match with previous records.</p>";
                }
            }

            ?>
        </div>
        <div class="login-image-container"></div>


    </div>
    
</body>
</html>