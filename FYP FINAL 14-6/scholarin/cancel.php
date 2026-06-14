<?php
session_start();
if($_SESSION['role'] != 'customer'){
    header("Location: login.php");
    exit();
}

include "db.php";

if(isset($_GET['id'])){
    $id = $_GET['id'];

    mysqli_query($conn,
        "DELETE FROM bookings WHERE id='$id'");

    $_SESSION['flash_message'] = 'Booking cancelled successfully.';
    $_SESSION['flash_type'] = 'success';
    header('Location: booking_status.php');
    exit();
} else {
    $_SESSION['flash_message'] = 'Invalid booking ID.';
    $_SESSION['flash_type'] = 'error';
    header('Location: booking_status.php');
    exit();
}
?>
