<?php
include "includes/config.php";

$error = "";

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if (mysqli_num_rows($query) == 1) {
        $user = mysqli_fetch_assoc($query);

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Wrong password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login | Student To-Do List</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">

<div class="overlay">
    <div class="login-box">
        <div class="logo">📝</div>
        <h1>Welcome Back</h1>
        <p>Please login to continue</p>

        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>

            <button name="login">Login</button>
        </form>

        <p class="link">No account? <a href="register.php">Register</a></p>
    </div>
</div>

</body>
</html>