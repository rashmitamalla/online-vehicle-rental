<?php
include '../../Database/database.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fname'];
    $phone = $_POST['number'];
    $email = $_POST['email'];
    $message = $_POST['message'];

    // Insert data into database
    $sql = "INSERT INTO contacts (fullname, phone, email, message) VALUES ('$fullname', '$phone', '$email', '$message')";

    if ($conn->query($sql) === TRUE) {
        echo '<script>alert("Inquiry sent Successfully");</script>';

        include '../../User/Php/Contact.php';
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
