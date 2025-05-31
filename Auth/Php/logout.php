<?php
// Start the session
session_start();
session_unset();

// Destroy all session data
session_destroy();

// Redirect the user to the login page or any other desired location
header('Location: ../../User/Php/Home.php?logout=true');
exit;
?>
