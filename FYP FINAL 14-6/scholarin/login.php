<?php
session_start();
include "db.php";
$error = '';

if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) > 0){
        $user = mysqli_fetch_assoc($result);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        if($user['role'] === 'admin'){
            header("Location: admin_home.php");
        } elseif($user['role'] === 'staff'){
            header("Location: staff_home.php");
        } elseif($user['role'] === 'customer'){
            header("Location: customer_home.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $error = 'Invalid email or password. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Sign In | Scholar Inn</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="LoginStyle.css">
</head>

<body>

<div class="login-box">
    <div class="login-header">
        <p>Sign in to manage your booking and enjoy the best stay.</p>
    </div>
    <form method="POST" class="login-form">
        <label>Email</label>
        <input type="text" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit" name="login">
            Sign In
        </button>

        <div class="login-footer-links">
            <a href="forgot_password.php" class="forgot-link">Forgot Password?</a>
            <a href="register.php" class="signup-link">No account? Sign Up</a>
            <a href="index.php" class="back-link">← Back to Home</a>
        </div>

        <?php if(!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
    </form>
</div>

</body>
</html>