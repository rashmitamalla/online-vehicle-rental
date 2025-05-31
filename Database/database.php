<?php 
    $host = "localhost:3307";
    $user = "root";
    $pass = "";
    $dbname = "vehiclerenting";

$conn = mysqli_connect($host, $user, $pass, $dbname);



if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
