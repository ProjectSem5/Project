<?php
session_start();
if($_SESSION['role'] != 'staff'){
    header("Location: login.php");
    exit();
}

include "db.php";

if(isset($_GET['id'])){
    $id = $_GET['id'];

    mysqli_query($conn,
        "UPDATE bookings SET status='Rejected' WHERE id='$id'");

    echo "Booking Rejected";
} else {
    echo "Invalid booking ID";
}
?>
<a href="staff_bookings.php">Back to Bookings</a>