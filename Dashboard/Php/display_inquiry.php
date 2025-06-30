<?php
include '../../Database/database.php';
session_start();

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT id, fullname, phone, email, message, created_at FROM contacts";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Contact List</title>
    <link rel="stylesheet" href="../../Dashboard/Css/style.css" />
</head>

<body>
    <div class="dashboard_container"><?php include 'dashboard.php'; ?></div>

    <div class="main">
        <h1>Contact List</h1>
        <table>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Phone Number</th>
                <th>Email</th>
                <th>Message</th>
                <th>Created At</th>
            </tr>

            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>" . htmlspecialchars($row['id']) . "</td>
                        <td>" . htmlspecialchars($row['fullname']) . "</td>
                        <td>" . htmlspecialchars($row['phone']) . "</td>
                        <td>" . htmlspecialchars($row['email']) . "</td>
                        <td>" . htmlspecialchars($row['message']) . "</td>
                        <td>" . htmlspecialchars($row['created_at']) . "</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No contacts found</td></tr>";
            }
            $conn->close();
            ?>
        </table>
    </div>
</body>

</html>
