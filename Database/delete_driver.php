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
    $did = $_POST["d-id"];

    // Construct the SQL DELETE query
    $sql = "DELETE FROM driver WHERE `d-id`= '$did'";

    // Execute the query
    if(mysqli_query($conn, $sql)) {
        // If the deletion was successful, return a success message
        echo "Driver deleted successfully";
        include 'ddriver.php';
    } else {
        // If there was an error with the query execution, return an error message
        echo "Error deleting driver: " . mysqli_error($conn);
    }
} else {
    // If the 'v_id' parameter is not set in the request, return an error message
    echo "Error: driver ID not provided";
}
?>
