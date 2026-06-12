<?php
session_start();
include "db.php";

if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
    $userRes = mysqli_query($conn, "SELECT location FROM users WHERE id='$user_id'");
    $user = mysqli_fetch_assoc($userRes);
    $location = $user && $user['location'] ? $user['location'] : 'JB';
    echo json_encode(['location' => $location]);
} else {
    echo json_encode(['location' => 'JB']);
}
?>