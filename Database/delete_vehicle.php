<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<?php
// Include your database connection file
include 'database.php';

// Check if the 'v_id' parameter is set in the request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vid = $_POST["vehicle_id"];

    // Construct the SQL DELETE query
    $sql = "DELETE FROM vehicle WHERE `vehicle_id`= '$vid'";


    // Execute the query
    if (mysqli_query($conn, $sql)) {
        // If the deletion was successful, return a success message
        echo "Vehicle deleted successfully. Redirecting...";
        header("refresh:1;url=../../Dashboard/Php/display_vehicle.php");
        exit;
    } else {
        // If there was an error with the query execution, return an error message
        echo "Error deleting vehicle: " . mysqli_error($conn);
    }
} else {
    // If the 'v_id' parameter is not set in the request, return an error message
    echo "Error: Vehicle ID not provided";
}
?>
