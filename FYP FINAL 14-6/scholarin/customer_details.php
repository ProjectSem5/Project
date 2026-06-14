<?php
session_start();
include "db.php";

if($_SESSION['role'] != 'staff'){
    header("Location: login.php");
    exit();
}

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    header("Location: staff_booking_history.php");
    exit();
}

$customer_id = $_GET['id'];
$userRes = mysqli_query($conn, "SELECT * FROM users WHERE id='$customer_id'");
$user = mysqli_fetch_assoc($userRes);
if(!$user){
    header("Location: staff_booking_history.php");
    exit();
}

$bookingsRes = mysqli_query($conn, "SELECT b.*, r.title AS room_title FROM bookings b LEFT JOIN rooms r ON b.room_id = r.id WHERE b.user_id='$customer_id' ORDER BY b.id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Details</title>
    <link rel="stylesheet" href="LandingStyle.css">
    <style>
        .details { max-width: 760px; margin: 40px auto; padding: 24px; border: 1px solid #ccc; border-radius: 16px; background: #fff; }
        .details h1 { margin-bottom: 18px; }
        .field { margin-bottom: 14px; }
        .field label { display: block; font-weight: 700; margin-bottom: 6px; }
        .field span { display: block; font-size: 1rem; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px 10px; border: 1px solid #ccc; text-align: left; }
        th { background: #f4f4f4; }
        .top-links { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; }
        .top-links a { text-decoration: none; color: #0f3b79; padding: 10px 14px; border: 1px solid #0f3b79; border-radius: 8px; }
        .top-links a:hover { background: #0f3b79; color: #fff; }
    </style>
</head>
<body>
    <div class="details">
        <div class="top-links">
            <a href="staff_booking_history.php">Back to Booking History</a>
            <a href="staff_home.php">Dashboard</a>
        </div>
        <h1>Customer Details</h1>
        <div class="field">
            <label>Full Name</label>
            <span><?php echo htmlspecialchars($user['name']); ?></span>
        </div>
        <div class="field">
            <label>Email</label>
            <span><?php echo htmlspecialchars($user['email']); ?></span>
        </div>
        <div class="field">
            <label>IC</label>
            <span><?php echo htmlspecialchars($user['ic']); ?></span>
        </div>
        <div class="field">
            <label>Location</label>
            <span><?php echo htmlspecialchars($user['location']); ?></span>
        </div>
        <div class="field">
            <label>Role</label>
            <span><?php echo htmlspecialchars($user['role']); ?></span>
        </div>

        <h2>Booking History for Customer</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Room</th>
                <th>Status</th>
                <th>Deposit</th>
                <th>Full Payment</th>
                <th>Check-in</th>
                <th>Check-out</th>
            </tr>
            <?php while($booking = mysqli_fetch_assoc($bookingsRes)): ?>
                <tr>
                    <td><?php echo $booking['id']; ?></td>
                    <td><?php echo htmlspecialchars($booking['room_title'] ?: 'Unknown'); ?></td>
                    <td><?php echo htmlspecialchars($booking['status']); ?></td>
                    <td><?php echo htmlspecialchars($booking['payment']); ?></td>
                    <td><?php echo htmlspecialchars($booking['full_payment']); ?></td>
                    <td><?php echo htmlspecialchars($booking['checkin_date']); ?></td>
                    <td><?php echo htmlspecialchars($booking['checkout_date']); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
