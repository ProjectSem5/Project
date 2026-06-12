<?php
session_start();
if($_SESSION['role'] != 'customer'){
    header("Location: login.php");
    exit();
}

include "db.php";

$user_id = $_SESSION['user_id'];
$loggedIn = isset($user_id);

// Get user's location
$userRes = mysqli_query($conn, "SELECT location FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($userRes);
$location = $user && $user['location'] ? $user['location'] : 'JB';

$paymentSuccess = false;
$receiptUploaded = false;

if(isset($_GET['id'])){
    $id = $_GET['id'];

    // Get booking details
    $res = mysqli_query($conn, "SELECT b.*, r.price FROM bookings b JOIN rooms r ON b.room_id = r.id WHERE b.id='$id'");
    $row = mysqli_fetch_assoc($res);

    if($row['status'] != 'Approved'){
        $error = "Booking not approved yet.";
    } else {
        $checkin = strtotime($row['checkin_date']);
        $checkout = strtotime($row['checkout_date']);
        $days = ceil(($checkout - $checkin) / (60*60*24));
        $total = $days * $row['price'];

        if(isset($_POST['pay'])){
            $card = $_POST['card_number']; // fake

            mysqli_query($conn,
            "UPDATE bookings SET full_payment='Paid RM$total' WHERE id='$id'");

            $paymentSuccess = true;

            // Refresh row
            $res = mysqli_query($conn, "SELECT b.*, r.price FROM bookings b JOIN rooms r ON b.room_id = r.id WHERE b.id='$id'");
            $row = mysqli_fetch_assoc($res);
        }

        if(strpos($row['full_payment'], 'Paid') !== false){
            // Already paid, handle receipt
            if(isset($_POST['upload'])){
                $target_dir = "uploads/";
                $file = $_FILES['receipt'];
                $file_name = basename($file['name']);
                $target_file = $target_dir . $file_name;
                $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                if($file_type != "pdf" && $file_type != "jpg" && $file_type != "jpeg" && $file_type != "png"){
                    $uploadError = "Only PDF, JPG, JPEG, PNG files allowed.";
                } elseif(move_uploaded_file($file['tmp_name'], $target_file)){
                    mysqli_query($conn, "UPDATE bookings SET receipt='$file_name' WHERE id='$id'");
                    $receiptUploaded = true;
                } else {
                    $uploadError = "Error uploading file.";
                }
            }
        }
    }
} else {
    $error = "Invalid booking ID";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Full Payment</title>
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

        .payment-title-bar {
            color: #f5f1e9;
            font-size: 1.6rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            font-weight: 700;
        }

        .payment-main {
            position: relative;
            z-index: 1;
            padding: 72px 40px 40px;
            max-width: 980px;
            margin: 0 auto;
            width: 100%;
        }

        .payment-card {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 32px;
            padding: 36px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.22);
            display: grid;
            gap: 24px;
        }

        .payment-card h1 {
            margin: 0;
            font-size: clamp(2.2rem, 4vw, 3rem);
            color: #fff;
            line-height: 1.02;
        }

        .payment-card p {
            margin: 0;
            color: rgba(245, 241, 233, 0.88);
            line-height: 1.8;
            font-size: 1rem;
        }

        .payment-details {
            display: grid;
            gap: 10px;
            color: rgba(247, 244, 238, 0.9);
        }

        .payment-details span {
            font-size: 0.95rem;
        }

        .payment-form {
            display: grid;
            gap: 20px;
        }

        .form-group {
            display: grid;
            gap: 10px;
        }

        .form-group label {
            color: rgba(255, 255, 255, 0.85);
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
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
            box-shadow: 0 0 0 6px rgba(243, 230, 206, 0.1);
        }

        .submit-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 16px 28px;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(74, 95, 176, 0.95), rgba(111, 140, 236, 0.98));
            color: #fff;
            border: none;
            font-weight: 700;
            transition: transform 0.25s ease, background 0.25s ease, box-shadow 0.25s ease;
            cursor: pointer;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(0, 0, 0, 0.18);
        }

        .message {
            padding: 16px 20px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border: 1px solid;
            font-weight: 600;
        }

        .message.success {
            background: rgba(76, 175, 80, 0.18);
            color: #c8f2c8;
            border-color: rgba(76, 175, 80, 0.4);
        }

        .message.error {
            background: rgba(235, 94, 94, 0.18);
            color: #ffb0b0;
            border-color: rgba(235, 94, 94, 0.4);
        }

.receipt-upload {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 28px;
            padding: 24px;
            display: grid;
            gap: 16px;
        }

        .receipt-upload h3 {
            margin: 0;
            color: #f9f6ef;
            font-size: 1.4rem;
        }

        .receipt-upload input[type="file"] {
            padding: 10px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        @media (max-width: 800px) {
            .payment-header,
            .payment-main {
                padding-left: 24px;
                padding-right: 24px;
            }
        }

        @media (max-width: 620px) {
            .payment-card {
                padding: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="landing-page payment-page">
        <div class="hero-overlay"></div>

        <header class="payment-header">
            <div class="payment-title-bar"><i class="fas fa-credit-card"></i> Full Payment</div>
            <div>
                <a href="customer_home.php" class="button secondary-button"><i class="fas fa-arrow-left"></i> Dashboard</a>
                <a href="index.php" class="button primary-button"><i class="fas fa-home"></i> Home</a>
            </div>
        </header>

        <main class="payment-main">
            <div class="payment-card">
                <?php if(isset($error)): ?>
                    <h1>Payment Error</h1>
                    <div class="message error"><i class="fas fa-times-circle"></i> <?php echo htmlspecialchars($error); ?></div>
                    <a href="booking_status.php" class="button secondary-button">Back to Bookings</a>
                <?php elseif($paymentSuccess): ?>
                    <h1>Payment Successful</h1>
                    <div class="message success"><i class="fas fa-check-circle"></i> Your payment of RM<?php echo htmlspecialchars($total); ?> has been processed successfully!</div>
                    <p>Please upload your receipt at <strong>Booking Status</strong> for staff confirmation.</p>
                    <a href="booking_status.php" class="button secondary-button">Go to Booking Status</a>
                <?php elseif(strpos($row['full_payment'], 'Paid') !== false): ?>
                    <h1>Receipt Upload</h1>
                    <p>Your full payment has been completed. Please upload your payment receipt for verification.</p>
                    <?php if($receiptUploaded): ?>
                        <div class="message success"><i class="fas fa-check-circle"></i> Receipt uploaded successfully!</div>
                    <?php endif; ?>
                    <?php if(isset($uploadError)): ?>
                        <div class="message error"><i class="fas fa-times-circle"></i> <?php echo htmlspecialchars($uploadError); ?></div>
                    <?php endif; ?>
                    <?php if(empty($row['receipt'])): ?>
                        <div class="receipt-upload">
                            <h3>Upload Receipt</h3>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="receipt">Receipt (PDF, JPG, JPEG, PNG)</label>
                                    <input type="file" id="receipt" name="receipt" required>
                                </div>
                                <button type="submit" name="upload" class="submit-btn"><i class="fas fa-upload"></i> Upload Receipt</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="message success"><i class="fas fa-check-circle"></i> Receipt already uploaded and verified.</div>
                    <?php endif; ?>
                    <a href="booking_status.php" class="button secondary-button">Back to Bookings</a>
                <?php else: ?>
                    <h1>Complete Full Payment</h1>
                    <div class="payment-details">
                        <span><strong>Days:</strong> <?php echo htmlspecialchars($days); ?></span>
                        <span><strong>Price per day:</strong> RM<?php echo htmlspecialchars($row['price']); ?></span>
                        <span><strong>Total Amount:</strong> RM<?php echo htmlspecialchars($total); ?></span>
                    </div>
                    <form method="POST" class="payment-form">
                        <div class="form-group">
                            <label for="card_number">Card Number</label>
                            <input type="text" id="card_number" name="card_number" placeholder="Enter card number" required>
                        </div>
                        <button type="submit" name="pay" class="submit-btn"><i class="fas fa-credit-card"></i> Pay Now</button>
                    </form>
                <?php endif; ?>
            </div>
        </main>
    </div>

