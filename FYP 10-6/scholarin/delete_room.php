<?php
session_start();
include "db.php";

if($_SESSION['role'] != 'staff'){
    header("Location: login.php");
    exit();
}

if(isset($_GET['id']) && is_numeric($_GET['id'])){
    $id = $_GET['id'];
    $roomRes = mysqli_query($conn, "SELECT image FROM rooms WHERE id='$id'");
    $room = mysqli_fetch_assoc($roomRes);
    if($room && !empty($room['image'])){
        $path = __DIR__ . '/uploads/' . $room['image'];
        if(file_exists($path)){
            @unlink($path);
        }
    }
    mysqli_query($conn, "DELETE FROM rooms WHERE id='$id'");
}

header("Location: staff_rooms.php");
exit();
