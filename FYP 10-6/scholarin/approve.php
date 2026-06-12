<?php
include "db.php";

$id = $_GET['id'];

mysqli_query($conn,
"UPDATE bookings SET status='Approved' WHERE id='$id'");

echo "Approved";
?>