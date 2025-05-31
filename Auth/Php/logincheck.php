<?php

include '../../Database/database.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $_SESSION["username"] = $username;

    $sql = "SELECT * FROM user WHERE username = '$username' AND password = '$password' ";
    $result = mysqli_query($conn, $sql);

    if ($result->num_rows > 0) {
        $row = mysqli_fetch_assoc($result);
        // Concatenate first name, middle name, and last name
        $fullname = $row['firstname'] . ' ' . $row['middlename'] . ' ' . $row['lastname'];
        $_SESSION["fullname"] = $fullname;
        $_SESSION["email"] = $row['email'];
        $_SESSION["number"] = $row['number'];
        echo "<script>alert('Login Successful');</script>";
        header("Location: ../../User/Php/Home.php");
        exit;
    }

    $sql = "SELECT password FROM admin WHERE username = '$username' AND password = '$password' ";
    $result = mysqli_query($conn, $sql);

    if ($result->num_rows > 0) {
        echo "<script>alert('Login Successful');</script>";
        header("Location: ../../Dashboard/Php/dashboard_home.php");
        exit;
    } else {
        // Username and password do not match
        echo "<script>alert('Invalid username or password');</script>";
        header("Location: ../Php/login.php");
    }
}
