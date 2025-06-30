<?php
// Contact.php

// Start session if needed
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include your DB connection file - adjust path if needed
include '../../Database/database.php';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input data
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Basic validation
    if (empty($fullname) || empty($email) || empty($phone) || empty($message)) {
        $error = "Please fill all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO contacts (fullname, phone, email, message, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $fullname, $phone, $email, $message);

        if ($stmt->execute()) {
            // Redirect with success flag
            header("Location: contact.php?success=1");
            exit();
        } else {
            $error = "Database error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Contact Us</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../Css/style.css" />
    <style>
        .header {
            background: url('https://images.unsplash.com/photo-1504215680853-026ed2a45def?fit=crop&w=1400&q=80') no-repeat center center/cover;
            padding: 80px 0;
            color: #fff;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 36px;
            font-weight: 700;
        }

        .header p {
            margin-top: 10px;
            font-size: 16px;
            font-weight: 400;
        }

        .main {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            padding: 60px 20px;
        }

        .contact-info {
            max-width: 400px;
        }

        .contact-info h2 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .contact-info p,
        .contact-info a {
            font-size: 14px;
            margin: 6px 0;
            text-decoration: none;
            color: #333;
        }

        .contact-info a:hover {
            color: #00bfa6;
        }

        .form-container {
            max-width: 400px;
            width: 100%;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        .form-group {
            margin-bottom: 15px;
        }

        input,
        textarea {
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 100%;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .submit-button {
            background-color: #00bfa6;
            color: white;
            border: none;
            padding: 12px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
            font-weight: 600;
        }

        .submit-button:hover {
            background-color: #008f80;
        }

        .message {
            margin-bottom: 20px;
            padding: 12px;
            border-radius: 4px;
        }

        .error {
            background-color: #f8d7da;
            color: #842029;
        }

        .success {
            background-color: #d1e7dd;
            color: #0f5132;
        }
    </style>
</head>

<body>

    <?php include 'Header.php'; ?>

    <div class="header">
        <h1>CONTACT US</h1>
        <p>Our Ride, One Call Away – Seamless Rentals, Endless Adventures</p>
    </div>

    <div class="main">
        <div class="contact-info">
            <h2>Get in touch!</h2>
            <p><strong>Phone</strong></p>
            <p>📞 01-597616 / +977 9801101924</p>

            <p><strong>Email</strong></p>
            <p>📧 <a href="mailto:info@selfdrivenepal.com">info@selfdrivenepal.com</a></p>

            <p><strong>Location</strong></p>
            <p>📍 Balkhu, near TU office, Kathmandu-Nepal</p>
        </div>

        <div class="form-container">
            <?php if (!empty($error)) : ?>
                <div class="message error"><?= htmlspecialchars($error) ?></div>
            <?php elseif (isset($_GET['success'])) : ?>
                <div class="message success">Thank you for contacting us! We will get back to you soon.</div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <input type="text" name="fullname" placeholder="Enter your Full-name" required
                        value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <input type="email" name="email" placeholder="example@email.com" required
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    <input type="tel" name="phone" placeholder="+9800000000" required
                        value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>
                <textarea name="message" placeholder="Type Message" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>

                <button class="submit-button" type="submit">Submit</button>
            </form>
        </div>
    </div>

</body>

</html>
