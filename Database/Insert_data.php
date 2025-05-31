
<?php

include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = $_POST["firstname"];
    $middlename = $_POST["middlename"];
    $lastname = $_POST["lastname"];
    $number = $_POST["number"];
    $email = $_POST["email"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    // $userPassword = password_hash($Password, PASSWORD_DEFAULT);
    // Check if username already exists
    $checkQuery = "SELECT * FROM user WHERE username = '$username'";
    $result = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        // Redirect back with error
        header("Location: ../../Auth/Php/signup.php?error=username_taken");
        exit();
    }
    $sql = "INSERT INTO user (firstname, middlename, lastname, number, email, username, password) 
    VALUES ('$firstname', '$middlename', '$lastname', '$number', '$email', '$username', '$password')";


    $result = mysqli_query($conn, $sql);
    if ($result) {

        include __DIR__ . '../../Auth/Php/login.php';
        echo '<script>alert("Signup Successful");</script>';
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>