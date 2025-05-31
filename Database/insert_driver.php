<?php

include 'database.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST["name"];
    $number = $_POST["number"];
    $email = $_POST["email"];

    $sql = "INSERT INTO `driver`(`d-name`, `d-number`, `d-email`) 
    VALUES ('$name','$number','$email')";
       

       $query=mysqli_query($conn,$sql);
       if($query)
   {
       
       include 'ddriver.php';
       echo '<script>alert("data is inserted into database table driver");</script>';
       exit();
   } else {
       echo "Error: " . $sql . "<br>" . mysqli_error($conn);
   }

   
}

mysqli_close($conn);
?>
