<?php
session_start();
if($_SESSION['role'] != 'customer'){
    header("Location: login.php");
    exit();
}

include "db.php";

$user_id = $_SESSION['user_id'];

// Get user's location
$userRes = mysqli_query($conn, "SELECT location FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($userRes);
$location = $user && $user['location'] ? $user['location'] : 'JB';

if(!isset($_GET['id'])){
    $_SESSION['flash_message'] = 'Invalid booking ID.';
    $_SESSION['flash_type'] = 'error';
    header('Location: booking_status.php');
    exit();
}

$id = intval($_GET['id']);
$message = '';
$error = '';

if(isset($_POST['pay'])){
    $card = trim($_POST['card_number']); // fake input, NOT stored

    if(empty($card)){
        $error = 'Please enter your card number to proceed.';
    } else {
        mysqli_query($conn,
        "UPDATE bookings SET payment='Paid RM50' WHERE id='$id'");

        $_SESSION['flash_message'] = 'Deposit payment successful!';
        $_SESSION['flash_type'] = 'success';
        header('Location: booking_status.php');
        exit();
    }
}

$bookingRes = mysqli_query($conn, "SELECT b.*, r.title, r.price FROM bookings b JOIN rooms r ON b.room_id=r.id WHERE b.id='$id' AND b.user_id='".$user_id."'");
$booking = mysqli_fetch_assoc($bookingRes);
if(!$booking){
    $_SESSION['flash_message'] = 'Booking not found.';
    $_SESSION['flash_type'] = 'error';
    header('Location: booking_status.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deposit Payment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="LandingStyle.css">
    <style>
        .payment-page {
            min-height: 100vh;
        }

        .payment-header {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 30px 40px;
        }

        .payment-title {
            font-size: 1.6rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            font-weight: 700;
            color: #f5f1e9;
        }

        .payment-main {
            position: relative;
            z-index: 1;
            padding: 72px 40px 40px;
            max-width: 900px;
            margin: 0 auto;
            width: 100%;
        }

        .payment-card {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 32px;
            padding: 40px;
            box-shadow: 0 35px 90px rgba(0, 0, 0, 0.25);
            display: grid;
            gap: 28px;
        }

        .payment-card h1 {
            margin: 0;
            color: #ffffff;
            font-size: clamp(2rem, 3.5vw, 3rem);
            line-height: 1.02;
        }

        .payment-card p,
        .payment-card span {
            color: rgba(245, 241, 233, 0.88);
            line-height: 1.75;
        }

        .booking-meta {
            display: grid;
            gap: 10px;
            color: rgba(247, 244, 238, 0.9);
            font-size: 0.96rem;
        }

        .booking-meta span {
            display: block;
        }

        .booking-meta strong {
            color: #f9f6ef;
        }

        .payment-form {
            display: grid;
            gap: 24px;
        }

        .form-group {
            display: grid;
            gap: 10px;
        }

        .form-group label {
            color: rgba(255, 255, 255, 0.86);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .form-group input {
            width: 100%;
            padding: 16px 18px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-size: 0.95rem;
            transition: all 0.25s ease;
        }

        .form-group input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.16);
            border-color: rgba(243, 230, 206, 0.5);
            box-shadow: 0 0 0 6px rgba(243, 230, 206, 0.12);
        }

        .submit-btn,
        .back-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 16px 28px;
            border-radius: 999px;
            font-weight: 700;
            text-decoration: none;
            transition: transform 0.25s ease, background 0.25s ease, box-shadow 0.25s ease;
        }

        .submit-btn {
            border: none;
            background: linear-gradient(135deg, rgba(74, 95, 176, 0.95), rgba(111, 140, 236, 0.98));
            color: #fff;
        }

        .back-btn {
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.18);
            color: #f7f4ee;
        }

        .submit-btn:hover,
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(0, 0, 0, 0.18);
        }

        .message {
            border-radius: 18px;
            padding: 16px 20px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border: 1px solid;
            font-weight: 600;
        }

        .message.success {
            background: rgba(86, 181, 102, 0.16);
            color: #c8f2c8;
            border-color: rgba(86, 181, 102, 0.4);
        }

        .message.error {
            background: rgba(235, 94, 94, 0.16);
            color: #ffb0b0;
            border-color: rgba(235, 94, 94, 0.4);
        }

        @media (max-width: 820px) {
            .payment-header,
            .payment-main {
                padding-left: 24px;
                padding-right: 24px;
            }
        }

        @media (max-width: 620px) {
            .payment-card {
                padding: 32px;
            }

            .submit-btn,
            .back-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="landing-page payment-page">
        <div class="hero-overlay"></div>

        <header class="payment-header">
            <div class="payment-title"><i class="fas fa-credit-card"></i> Deposit Payment</div>
            <div class="action-buttons">
                <a href="booking_status.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Bookings</a>
                <a href="customer_home.php" class="back-btn"><i class="fas fa-home"></i> Dashboard</a>
            </div>
        </header>

        <main class="payment-main">
            <div class="payment-card">
                <h1>Pay RM50 Deposit</h1>
                <div class="booking-meta">
                    <span><strong>Room:</strong> <?php echo htmlspecialchars($booking['title']); ?></span>
                    <span><strong>Status:</strong> <?php echo htmlspecialchars($booking['status']); ?></span>
                    <span><strong>Deposit:</strong> <?php echo htmlspecialchars($booking['payment']); ?></span>
                    <span><strong>Check-in:</strong> <?php echo htmlspecialchars($booking['checkin_date']); ?></span>
                    <span><strong>Check-out:</strong> <?php echo htmlspecialchars($booking['checkout_date']); ?></span>
                </div>

                <?php if($error): ?>
                    <div class="message error"><i class="fas fa-times-circle"></i> <?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" class="payment-form">
                    <div class="form-group">
                        <label for="card_number">Card Number</label>
                        <input type="text" id="card_number" name="card_number" placeholder="0000 0000 0000 0000" required>
                    </div>

                    <button type="submit" name="pay" class="submit-btn"><i class="fas fa-credit-card"></i> Pay Deposit</button>
                </form>

                <div class="action-buttons">
                    <a href="booking_status.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Bookings</a>
                    <a href="index.php" class="back-btn"><i class="fas fa-home"></i> Home</a>
                </div>
            </div>
        </main>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const location = '<?php echo $location; ?>';
            const imageUrl = location === 'KL' ? 'SCHOLAR INN KL.webp' : 'Scholar inn image out side.jpg';
            const pageDiv = document.querySelector('.landing-page');
            pageDiv.style.backgroundImage = `url('${imageUrl}')`;
        });
    </script>
</body>
</html>