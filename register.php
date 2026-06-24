<?php
include "includes/config.php";

$message = "";

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if (mysqli_num_rows($check) > 0) {
        $message = "Email already exists!";
    } else {
        $query = "INSERT INTO users (username, email, password) 
                  VALUES ('$username', '$email', '$password')";

        if (mysqli_query($conn, $query)) {
            $message = "Registration successful! You may login now.";
        } else {
            $message = "Registration failed.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register | Student To-Do List</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">

<div class="overlay">
    <div class="login-box">
        <div class="logo">📝</div>
        <h1>Create Account</h1>
        <p>Register to start managing your tasks</p>

        <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>

            <button name="register">Register</button>
        </form>

        <p class="link">Already have an account? <a href="login.php">Login</a></p>
    </div>
</div>

</body>
</html>