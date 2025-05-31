<?php
include '../../Database/database.php';

$vehicle_id = $_GET['vehicle_id'] ?? null;

if (!$vehicle_id) {
    die("Vehicle ID is required.");
}

// Get all ratings for this vehicle
$sql = "SELECT full_name, rating, feedback, rated_at FROM ratings WHERE vehicle_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$result = $stmt->get_result();

// Get average rating
$avg_sql = "SELECT AVG(rating) AS avg_rating FROM ratings WHERE vehicle_id = ?";
$avg_stmt = $conn->prepare($avg_sql);
$avg_stmt->bind_param("i", $vehicle_id);
$avg_stmt->execute();
$avg_result = $avg_stmt->get_result();
$avg_rating = $avg_result->fetch_assoc()['avg_rating'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Ratings</title>
    <link rel="stylesheet" href="../../Dashboard/Css/style.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #333;
            color: white;
        }

        .average-box {
            font-weight: bold;
            padding: 10px;
            background-color: #e6ffe6;
            border-left: 5px solid #4CAF50;
            width: fit-content;
        }

        .stars {
            color: #FFD700;
        }
    </style>
</head>

<body>
    <div class="dashboard_container"><?php include 'dashboard.php'; ?></div>


    <h2>Ratings & Feedback for Vehicle ID: <?= htmlspecialchars($vehicle_id) ?></h2>

    <table>
        <tr>
            <th>Full Name</th>
            <th>Rating</th>
            <th>Feedback</th>
            <th>Date</th>
        </tr>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td class="stars">
                        <?php
                        $stars = intval($row['rating']);
                        echo str_repeat('★', $stars) . str_repeat('☆', 5 - $stars);
                        ?>
                    </td>
                    <td><?= htmlspecialchars($row['feedback']) ?></td>
                    <td><?= $row['rated_at'] ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">No ratings found for this vehicle.</td>
            </tr>
        <?php endif; ?>
    </table>

    <div class="average-box">
        Average Rating:
        <span class="stars">
            <?php
            $avg_int = intval(round($avg_rating));
            echo $avg_rating ? str_repeat('★', $avg_int) . str_repeat('☆', 5 - $avg_int) . " (" . round($avg_rating, 2) . ")" : "N/A";
            ?>
        </span>
    </div>

</body>

</html>