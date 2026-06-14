<?php
include "db.php";
$message = '';
$messageType = '';

if(isset($_POST['submit'])){
    $email = trim($_POST['email']);
    $name = trim($_POST['name']);

    if($email === '' || $name === ''){
        $message = 'Please fill in all fields.';
        $messageType = 'error';
    } else {
        $email = mysqli_real_escape_string($conn, $email);
        $name = mysqli_real_escape_string($conn, $name);

        $result = mysqli_query($conn,
            "SELECT * FROM users WHERE email='$email' AND name='$name' AND role='customer' LIMIT 1");

        if(mysqli_num_rows($result) > 0){
            $user = mysqli_fetch_assoc($result);
            $message = "Account Found! Your password is: " . htmlspecialchars($user['password']);
            $messageType = 'success';
        } else {
            $message = 'Email or username not found in our system.';
            $messageType = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recover Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-image: url('Forgot.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .forgot-box {
            width: min(440px, 100%);
            background: rgba(14, 17, 32, 0.88);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 32px;
            box-shadow: 0 35px 90px rgba(0, 0, 0, 0.45);
            backdrop-filter: blur(20px);
            padding: 44px 36px;
            position: relative;
        }

        .forgot-box::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 32px;
            pointer-events: none;
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.08), transparent 60%);
        }

        .forgot-header {
            position: relative;
            z-index: 1;
            text-align: center;
            margin-bottom: 28px;
        }

        .forgot-header h1 {
            margin: 0;
            font-size: 2rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #ffffff;
        }

        .forgot-header p {
            margin: 12px auto 0;
            max-width: 320px;
            color: rgba(255, 255, 255, 0.72);
            line-height: 1.7;
            font-size: 0.95rem;
        }

        .forgot-form {
            position: relative;
            z-index: 1;
            display: grid;
            gap: 20px;
        }

        .form-group {
            display: grid;
            gap: 8px;
        }

        label {
            display: block;
            color: rgba(255, 255, 255, 0.82);
            font-weight: 600;
            font-size: 0.95rem;
        }

        input {
            width: 100%;
            padding: 16px 18px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            outline: none;
            transition: border-color 0.25s ease, background 0.25s ease, box-shadow 0.25s ease;
            font-size: 0.95rem;
        }

        input:focus {
            border-color: rgba(102, 126, 234, 0.85);
            background: rgba(255, 255, 255, 0.16);
            box-shadow: 0 0 0 6px rgba(102, 126, 234, 0.12);
        }

        button {
            width: 100%;
            padding: 16px 0;
            background: linear-gradient(135deg, rgba(74, 95, 176, 0.95), rgba(111, 140, 236, 0.98));
            color: #ffffff;
            border: none;
            border-radius: 999px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            cursor: pointer;
            transition: transform 0.25s ease, box-shadow 0.25s ease, opacity 0.25s ease;
            font-size: 0.95rem;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px rgba(111, 140, 236, 0.28);
            opacity: 0.98;
        }

        button:active {
            transform: translateY(0);
        }

        .forgot-footer-links {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .back-link,
        .login-link {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            font-size: 0.95rem;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.18);
            padding: 10px 16px;
            border-radius: 999px;
            transition: background 0.25s ease, transform 0.25s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-link:hover,
        .login-link:hover {
            background: rgba(255, 255, 255, 0.22);
            transform: translateY(-1px);
        }

        .message {
            padding: 14px 16px;
            border-radius: 14px;
            text-align: center;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .message.success {
            background: rgba(76, 175, 80, 0.16);
            color: #c8f2c8;
            border: 1px solid rgba(76, 175, 80, 0.3);
        }

        .message.error {
            background: rgba(235, 94, 94, 0.16);
            color: #ffb0b0;
            border: 1px solid rgba(235, 94, 94, 0.3);
        }

        @media (max-width: 480px) {
            .forgot-box {
                padding: 30px 20px;
            }

            input,
            button {
                padding: 12px 14px;
            }

            .forgot-footer-links {
                flex-direction: column;
            }

            .back-link,
            .login-link {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="forgot-box">
        <div class="forgot-header">
            <h1>Recover Password</h1>
            <p>Enter your name and email to recover your account</p>
        </div>

        <?php if(!empty($message)): ?>
            <div class="message <?php echo htmlspecialchars($messageType); ?>">
                <?php if($messageType === 'success'): ?>
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                <?php else: ?>
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($message); ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="forgot-form">
            <div class="form-group">
                <label for="name">Full Name / Username</label>
                <input type="text" id="name" name="name" placeholder="Enter your full name" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="text" id="email" name="email" placeholder="Enter your email" required>
            </div>

            <button type="submit" name="submit"><i class="fas fa-key"></i> Recover Password</button>

            <div class="forgot-footer-links">
                <a href="login.php" class="login-link"><i class="fas fa-sign-in-alt"></i> Back to Login</a>
                <a href="index.php" class="back-link"><i class="fas fa-home"></i> Home</a>
            </div>
        </form>
    </div>
</body>
</html>

