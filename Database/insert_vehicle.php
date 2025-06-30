<?php

include __DIR__ . '/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Form data
    $number = $_POST["number"];
    $condition = $_POST["condition"];
    $price = $_POST["price"];
    $people = $_POST["people"];
    $fuel = $_POST["oil"];
    $type = $_POST["type"];
    $model = $_POST["model"];
    $brand = $_POST["brand"];
    $color = $_POST["color"];
    $status = $_POST["status"];

    // Duplicate check for vehicle_number
    $check = mysqli_query($conn, "SELECT * FROM vehicle WHERE vehicle_number = '$number'");
    if (mysqli_num_rows($check) > 0) {
        // Redirect back with error
        header("Location: ../Dashboard/Php/add_vehicle.php?error=duplicate");
        exit();
    }

    // Image upload
    $uploadDir = __DIR__ . '/../Image/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = basename($_FILES["pic"]["name"]);
    $targetPath = $uploadDir . $filename;
    $relativePath = '../Image/' . $filename;

    if (move_uploaded_file($_FILES["pic"]["tmp_name"], $targetPath)) {
        $sql = "INSERT INTO `vehicle`(`vehicle_number`, `vehicle_condition`, `vehicle_image`, `vehicle_price`, `vehicle_people`, `vehicle_oil`, `vehicle_type`, `vehicle_model`,`brand`, `vehicle_color`, `vehicle_status`) 
                VALUES ('$number','$condition','$relativePath','$price','$people','$fuel','$type','$model','$brand','$color','$status')";

        $query = mysqli_query($conn, $sql);

        if ($query) {
            header("Location: ../Dashboard/Php/display_vehicle.php?success=1");
            exit();
            echo '<script>alert("Data is inserted into the vehicle table");</script>';
            exit();
        } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
    } else {
        echo '<script>alert("Failed to upload image.");</script>';
        include __DIR__ . '/../Dashboard/Php/add_vehicle.php';
    }
}
