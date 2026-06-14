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
?>

<!DOCTYPE html>
<Html>

<head>
    <meta charset="UTF-8">
    <meta name= "viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="DashStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-image: url('<?php echo $location === 'KL' ? 'SCHOLAR INN KL.webp' : 'Scholar inn image out side.jpg'; ?>');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
    </style>
</head>

<body>


<div class="CustomerHome">

<h1><i class="fas fa-tachometer-alt"></i> Customer Dashboard</h1>


<div class="Section">

    <h2><i class="fas fa-user"></i> Account</h2>
    <a href="view_profile.php"><i class="fas fa-user-cog"></i> Manage Profile</a><br>

</div>

<div class="Section">

<h2><i class="fas fa-bed"></i> Rooms & Bookings</h2>
<a href="booking_status.php"><i class="fas fa-calendar-check"></i> View Booking Status</a><br>

</div>

<div class="Section">

<h2><i class="fas fa-credit-card"></i> Payments & Reviews</h2>
<a href="rating_review.php"><i class="fas fa-star"></i> Leave Rating and Review</a><br>

</div>

<div class="Section">

<h2><i class="fas fa-sign-out-alt"></i> Exit</h2>
<a href="index.php" class="back-to-home"><i class="fas fa-home"></i> Back to Home</a><br>
<a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Sign Out</a>

</div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sections = document.querySelectorAll('.Section');
    sections.forEach((section, index) => {
        section.style.opacity = '0';
        section.style.transform = 'translateY(20px)';
        setTimeout(() => {
            section.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            section.style.opacity = '1';
            section.style.transform = 'translateY(0)';
        }, index * 250);
    });
});
</script>

</body>
</html>