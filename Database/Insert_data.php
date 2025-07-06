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

    // ✅ Hash the password securely
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if username already exists (use prepared statement for safety)
    $checkQuery = "SELECT * FROM user WHERE username = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $checkResult = $stmt->get_result();

    if ($checkResult->num_rows > 0) {
        // Redirect back with error
        header("Location: ../../Auth/Php/signup.php?error=username_taken");
        exit();
    }

    // ✅ Insert user with hashed password (also use prepared statement)
    $sql = "INSERT INTO user (firstname, middlename, lastname, number, email, username, password) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $firstname, $middlename, $lastname, $number, $email, $username, $hashedPassword);

    if ($stmt->execute()) {
        echo '<script>alert("Signup Successful"); window.location.href="../../Auth/Php/login.php";</script>';
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}

$conn->close();
?>
