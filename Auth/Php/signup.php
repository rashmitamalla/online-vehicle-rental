<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../../User/Css/style.css">


</head>

<body>
    <!-- Signup main box -->
    <div class="signupmain">

        <!-- Signup container -->
        <div class="signup-container">

            <?php
            if (isset($_GET['error']) && $_GET['error'] == 'username_taken') {
                echo "<p style='color:red; text-align:center;'>Username already exists. Please choose a different one.</p>";
            }
            ?>

            <form action="../../Database/Insert_data.php" method="post" onsubmit="return check();">
                <button type="button" class="black-btn"><a href="login.php">Back</a></button>

                <h1 class="black-h1">Signup To Create Your Account</h1><br>

                <div class="form">
                    <!-- for name -->
                    <div style="display: flex; flex-direction: row;column-gap:10px; justify-content: space-between; width:100%">
                        <div>
                            <label for="firstname">Firstname:</label>
                            <input type="text" id="firstname" name="firstname" required />
                        </div>
                        <div>
                            <label for="middlename">Middle Name:</label>
                            <input type="text" id="middlename" name="middlename" />
                        </div>
                        <div>
                            <label for="lastname">Last Name:</label>
                            <input type="text" id="lastname" name="lastname" required />
                        </div>
                    </div>
                    <!-- for number and email -->
                    <div style="display: flex; flex-direction: row; width: 100%;">
                        <div>
                            <label for="phoneNumber">Phone Number:+977</label>
                            <input type="text" id="number" name="number" required />
                        </div>
                        <div style="padding-left: 15px;">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" required />
                        </div>
                    </div>

                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required />

                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required />

                    <label for="password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required /><br>

                    <div style="display: flex;flex-direction: row; font-size: 14px;">
                        <div class="hover1">Already have account?</div>
                        <div class="hover" onclick="login()">Log-in</div>
                    </div>
                    <br>
                    <button type="submit" class="red-btn">Sign Up</button>


                </div>
            </form>
            <!-- image container -->

        </div>
        <div class="login-image-container"></div>
    </div>


    <script>
        function login() {
            window.location.href = 'login.php';
        }

        function check() {
            var password = document.getElementById("password").value;
            var confirm_password = document.getElementById("confirm_password").value;
            var passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
            var phoneNumber = document.getElementById("number").value;
            var phoneNumberPattern = /^(97|98|78)\d{8}$/;

            if (password != confirm_password) {
                alert("Passwords do not match!");
                return false;
            }

            if (!passwordPattern.test(password)) {
                alert("Password must be at least 8 characters long, contain at least one uppercase letter, one lowercase letter, one number, and one special character.");
                return false;
            }

            if (!phoneNumberPattern.test(phoneNumber)) {
                alert("Phone number must start with '97' or '78' and be exactly 10 digits long.");
                return false;
            }

            return true;
        }
    </script>



</body>

</html>