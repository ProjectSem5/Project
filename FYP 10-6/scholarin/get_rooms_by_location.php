<?php
include "db.php";
include "room_rules.php";

header('Content-Type: application/json');

if(!isset($_GET['location'])){
    echo json_encode(['success' => false, 'error' => 'Location not provided']);
    exit();
}

$location = mysqli_real_escape_string($conn, $_GET['location']);
$checkin = isset($_GET['checkin']) ? trim($_GET['checkin']) : '';
$checkout = isset($_GET['checkout']) ? trim($_GET['checkout']) : '';

// Validate location is either KL or JB
if($location !== 'KL' && $location !== 'JB'){
    echo json_encode(['success' => false, 'error' => 'Invalid location']);
    exit();
}

$res = mysqli_query($conn, "SELECT id, title, price, description, image, availability FROM rooms WHERE location='$location' ORDER BY id DESC LIMIT 50");

if(!$res){
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit();
}

$rooms = [];
while($row = mysqli_fetch_assoc($res)){
    $status = scholarin_room_status($conn, $row, $checkin, $checkout);
    $row['availability'] = $status['availability'];
    $row['available_units'] = $status['available_units'];
    $row['booked_units'] = $status['booked_units'];
    $rooms[] = $row;
}

echo json_encode(['success' => true, 'rooms' => $rooms]);
?>
