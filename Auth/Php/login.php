<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../../User/Css/style.css">

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer" />
</head>

<body>

    <!-- Login main box -->
    <div class="loginmain">
        <!-- login container -->
        <div class="login-container">


            <?php
            // session_start();
            if (isset($_SESSION['login_error'])) {
                echo "<script>alert('" . $_SESSION['login_error'] . "');</script>";
                unset($_SESSION['login_error']);
            }
            ?>

            <form action="../../Auth/Php/logincheck.php" method="post">



                <h1 class="black-h1">Login to access your account</h1>
                <div class="form">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form">
                    <label for="password">Password:</label>
                    <div style="display: flex; flex-direction: row;">
                        <input type="password" id="password" name="password" required>
                        <!-- <i class="fa-solid fa-eye"></i> -->

                    </div>
                </div>
                <div class="hover" style=" display: flex;float:right;" onclick="forget()">forget password?</div><br>
                <div class="form">
                    <button type="submit" class="red-btn">Login</button>
                </div>
                <div style="display: flex;flex-direction: row; font-size: 14px; margin-top:10px;">
                    <div class="hover1">Don't have account?</div>
                    <div class="hover" onclick="signup()">Sign-up</div>

                </div><br>

            </form>
        </div>
        <!-- image container -->
        <div class="login-image-container"></div>
    </div>
</body>

<script>
    function signup() {
        window.location.href = 'signup.php';
    }

    function forget() {
        window.location.href = 'forget.php';
    }
</script>

</html>