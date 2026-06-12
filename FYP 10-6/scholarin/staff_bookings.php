<?php
session_start();
if($_SESSION['role'] != 'staff'){
    header("Location: login.php");
    exit();
}
header("Location: staff_home.php?section=booking_requests");
exit();
