<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../../Dashboard/Css/style.css">
</head>

<body>
    <div class="dashboard_container"><?php include 'dashboard.php'; ?></div>
    <div class="main">

        <form action="insert_admin.php" method="post" onsubmit="return check();">
            <div class="form">
                <h2>Insert Details Of ADMIN</h2><br>
                <!-- for name -->
                <div style="display: flex; flex-direction: row; justify-content: space-between;">
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
                        <input type="number" id="number" name="number" max-min="10" placeholder="Enter 10 digits" required />
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

                <button type="submit" class="blue-btn">Sign Up</button>


            </div>
        </form>
    </div>
</body>
<script>
    function check() {
        var password = document.getElementById("password").value;
        var confirm_password = document.getElementById("confirm_password").value;

        if (password != confirm_password) {
            alert("Passwords do not match!");
            return false;

        } else {
            return true;

        }

    }
</script>

</html>