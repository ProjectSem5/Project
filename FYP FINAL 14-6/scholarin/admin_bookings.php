<?php
session_start();
include "db.php";

if($_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}
?>

<h2>All Booking Records</h2>

<table border="1">
<tr>
    <th>Booking ID</th>
    <th>User ID</th>
    <th>Room ID</th>
    <th>Status</th>
    <th>Payment</th>
</tr>

<?php
$result = mysqli_query($conn, "SELECT * FROM bookings");

while($row = mysqli_fetch_assoc($result)){
    echo "<tr>";
    echo "<td>".$row['id']."</td>";
    echo "<td>".$row['user_id']."</td>";
    echo "<td>".$row['room_id']."</td>";
    echo "<td>".$row['status']."</td>";
    echo "<td>".$row['payment']."</td>";
    echo "</tr>";
}
?>

</table>