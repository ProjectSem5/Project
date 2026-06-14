<?php
session_start();
include "db.php";

if(isset($_POST['location']) && isset($_SESSION['user_id'])){
    $location = $_POST['location'];
    $user_id = $_SESSION['user_id'];
    
    // Save location to database
    mysqli_query($conn, "UPDATE users SET location='$location' WHERE id='$user_id'");
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>
