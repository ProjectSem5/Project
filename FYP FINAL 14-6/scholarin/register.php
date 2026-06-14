<?php
session_start();
include "db.php";

$loggedIn = isset($_SESSION['user_id']);
$message = '';
$error = '';

if(isset($_POST['register'])){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $ic = trim($_POST['ic']);

    if($name === '' || $email === '' || $password === '' || $ic === ''){
        $error = 'Please complete all fields.';
    } else {
        $name = mysqli_real_escape_string($conn, $name);
        $email = mysqli_real_escape_string($conn, $email);
        $password = mysqli_real_escape_string($conn, $password);
        $ic = mysqli_real_escape_string($conn, $ic);

        $query = "INSERT INTO users (name,email,password,ic,role) VALUES ('$name','$email','$password','$ic','customer')";
        if(mysqli_query($conn, $query)){
            $message = 'Registration successful! You may now log in.';
        } else {
            $error = 'Registration failed: ' . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Scholar Inn</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="LandingStyle.css">
    <style>
        .register-page {
            position: relative;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .register-card {
            position: relative;
            z-index: 1;
            max-width: 520px;
            margin: 96px auto 80px;
            background: rgba(10, 10, 10, 0.72);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 28px;
            padding: 40px 36px;
            box-shadow: 0 35px 90px rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(18px);
        }

        .register-card h1 {
            margin: 0 0 22px;
            font-size: 2.2rem;
            letter-spacing: -0.04em;
            color: #ffffff;
        }

        .register-card p.lead {
            margin: 0 0 32px;
            color: rgba(245, 241, 233, 0.88);
            line-height: 1.7;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: rgba(247, 244, 238, 0.78);
            margin-bottom: 10px;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            background: rgba(255, 255, 255, 0.06);
            color: #f7f4ee;
            font-size: 1rem;
        }

        .form-group input:focus {
            outline: none;
            border-color: rgba(243, 230, 206, 0.6);
            background: rgba(255, 255, 255, 0.12);
        }

        .submit-btn {
            width: 100%;
            margin-top: 10px;
            padding: 16px 18px;
            border-radius: 999px;
            border: 1px solid rgba(243, 230, 206, 0.28);
            background: rgba(243, 230, 206, 0.16);
            color: #f7f4ee;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.25s ease, background 0.25s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            background: rgba(243, 230, 206, 0.28);
        }

        .form-footer {
            margin-top: 24px;
            color: rgba(247, 244, 238, 0.74);
            font-size: 0.95rem;
            text-align: center;
        }

        .form-footer a {
            color: #f3e6ce;
            text-decoration: none;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        .message {
            margin-bottom: 24px;
            padding: 16px 18px;
            border-radius: 16px;
            font-weight: 600;
        }

        .message.success {
            background: rgba(76, 175, 80, 0.12);
            color: #c7ffd8;
            border: 1px solid rgba(76, 175, 80, 0.28);
        }

        .message.error {
            background: rgba(244, 67, 54, 0.12);
            color: #ffbebe;
            border: 1px solid rgba(244, 67, 54, 0.28);
        }

        @media (max-width: 720px) {
            .register-card {
                margin: 60px 16px 48px;
                padding: 28px 24px;
            }
        }
    </style>
</head>
<body>
    <div class="landing-page">
        <div class="hero-overlay"></div>

        <header class="landing-header">
            <div class="branding">
                <img src="UTM LOGO.jpg" alt="UTM Logo" class="utm-logo">
                <div class="logo">Scholar Inn</div>
            </div>
            <nav class="landing-nav">
                <a href="index.php#about">About</a>
                <a href="index.php#contact">Contact</a>
                <a href="<?php echo $loggedIn ? 'view_rooms.php' : 'login.php'; ?>">Rooms</a>
                <a href="<?php echo $loggedIn ? 'rating_review.php' : 'login.php'; ?>">Review</a>
            </nav>
            <div class="header-menu">
                <a href="<?php echo $loggedIn ? 'customer_home.php' : 'login.php'; ?>" class="button login-button"><?php echo $loggedIn ? 'Dashboard' : 'Login'; ?></a>
                <div class="menu-dropdown">
                    <?php if($loggedIn): ?>
                        <a class="menu-item" href="customer_home.php">Customer Dashboard</a>
                        <a class="menu-item logout-link" href="logout.php">Sign Out</a>
                    <?php else: ?>
                        <a class="menu-item" href="login.php">Login</a>
                        <a class="menu-item" href="register.php">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <main class="hero-content">
            <div class="register-card">
                <h1>Join Scholar Inn</h1>
                <p class="lead">Create your account to book rooms, track reservations, and manage your stay with ease.</p>

                <?php if($message): ?>
                    <div class="message success"><?php echo $message; ?></div>
                <?php endif; ?>

                <?php if($error): ?>
                    <div class="message error"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" placeholder="Your name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="you@example.com" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Create a password" required>
                    </div>

                    <div class="form-group">
                        <label for="ic">IC Number</label>
                        <input type="text" id="ic" name="ic" placeholder="Your IC number" required>
                    </div>

                    <button type="submit" name="register" class="submit-btn">Register</button>
                </form>

                <div class="form-footer">
                    Already have an account? <a href="login.php">Login here</a>.
                </div>
            </div>
        </main>
    </div>
</body>
</html>