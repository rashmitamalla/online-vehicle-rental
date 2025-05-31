
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

    $sql = "INSERT INTO admin (firstname, middlename, lastname, number, email, username, password) 
    VALUES ('$firstname', '$middlename', '$lastname', '$number', '$email', '$username', '$password')";


    $result=mysqli_query($conn,$sql);
        if($result)
    {
        
        include 'dadmin.php';
        echo '<script>alert("Admin Added Successfully");</script>';
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }

}

mysqli_close($conn);
?>