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
        <h2>Insert Details Of Driver</h2>
        <form action="../../Database/insert_driver.php" method="post" onsubmit="return validatePhoneNumber();">
            <div class="field-group">
                Name: <br>
                <input type="text" name="name" id="name" placeholder="Enter Full Name" required /><br>
                Number: +977 <br>
                <input type="text" name="number" id="number" placeholder="Enter 10 Digits" required /><br>
                Email: <br>
                <input type="email" name="email" id="email" placeholder="example@gmail.com" required /><br>
                <input type="submit" value="submit" class="blue-btn">
            </div>
        </form>
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