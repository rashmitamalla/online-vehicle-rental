<?php
include '../../Database/database.php';
session_start();

// Fetch contacts from database
$sql = "SELECT username, fullname, phone, email, message FROM contacts";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact List</title>
    <link rel="stylesheet" href="../../Dashboard/Css/style.css">

</head>

<body>
    <div class="dashboard_container"><?php include 'dashboard.php'; ?></div>

    <div class="main">
        <h1>Contact List</h1>
        <table>
            <tr>
                <th>Username</th>
                <th>Full Name</th>
                <th>Phone Number</th>
                <th>Email</th>
                <th>Message</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['username']}</td>
                        <td>{$row['fullname']}</td>
                        <td>{$row['phone']}</td>
                        <td>{$row['email']}</td>
                        <td>{$row['message']}</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No contacts found</td></tr>";
            }
            $conn->close();
            ?>
        </table>
    </div>
</body>

</html>