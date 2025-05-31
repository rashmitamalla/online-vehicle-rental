<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Contact Us</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
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
            <form>
                <div class="form-group">
                    <input type="text" placeholder="Enter your name" required>
                    <input type="text" placeholder="Enter your last Name" required>
                </div>
                <div class="form-group">
                    <input type="email" placeholder="example@email.com" required>
                    <input type="tel" placeholder="+9800000000" required>
                </div>
                <textarea placeholder="Type Message" required></textarea>

                <button class="submit-button" type="submit">Submit</button>
            </form>
        </div>
    </div>

</body>

</html>