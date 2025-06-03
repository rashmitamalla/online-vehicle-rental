<?php

include '../../Database/database.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Use prepared statement for user login
    $stmt = $conn->prepare("SELECT * FROM user WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Store user details in session
        $_SESSION["username"] = $username;
        $fullname = $row['firstname'] . ' ' . ($row['middlename'] ?? '') . ' ' . $row['lastname'];
        $_SESSION["fullname"] = trim($fullname);
        $_SESSION["email"] = $row['email'];
        $_SESSION["number"] = $row['number'];

        echo "<script>
            alert('Login Successful');
            window.location.href = '../../User/Php/Home.php';
        </script>";
        exit;
    }

    // Admin login
    $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>
            alert('Admin Login Successful');
            window.location.href = '../../Dashboard/Php/dashboard_home.php';
        </script>";
        exit;
    } else {
        echo "<script>
            alert('Invalid username or password');
            window.location.href = '../Php/login.php';
        </script>";
        exit;
    }
}
?>
