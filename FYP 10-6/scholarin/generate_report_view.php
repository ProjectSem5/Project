<?php
session_start();
include "db.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: login.php");
    exit();
}

if(!isset($_GET['room_id'])){
    echo "Room ID missing.";
    exit();
}

$room_id = (int)$_GET['room_id'];

$roomRes = mysqli_query($conn, "SELECT * FROM rooms WHERE id='$room_id'");
if(!$roomRes || mysqli_num_rows($roomRes) === 0){
    echo "Room not found.";
    exit();
}
$room = mysqli_fetch_assoc($roomRes);

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS admin_report_seen (id INT AUTO_INCREMENT PRIMARY KEY, admin_id INT NOT NULL, room_id INT NOT NULL, review_count INT NOT NULL DEFAULT 0, seen_at DATETIME NOT NULL, UNIQUE KEY uniq_admin_room (admin_id, room_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$reviewCount = (int)mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM reviews WHERE room_id='" . (int)$room_id . "'"))['cnt'];
mysqli_query($conn, "INSERT INTO admin_report_seen (admin_id, room_id, review_count, seen_at) VALUES ('" . (int)($_SESSION['user_id'] ?? 0) . "', '" . (int)$room_id . "', '" . $reviewCount . "', NOW()) ON DUPLICATE KEY UPDATE review_count='" . $reviewCount . "', seen_at=NOW()"
);

// fetch bookings with reviews
$bookingsRes = mysqli_query($conn, "SELECT b.*, u.name AS customer_name, u.email AS customer_email, rv.review AS review_text, rv.rating AS review_rating, rv.cleanliness, rv.services, rv.staff FROM bookings b LEFT JOIN users u ON b.user_id=u.id LEFT JOIN reviews rv ON rv.user_id=b.user_id AND rv.room_id=b.room_id WHERE b.room_id='$room_id' ORDER BY b.id DESC");

// prepare report data
$report = [
    'room_id' => $room_id,
    'room_title' => $room['title'] ?? null,
    'generated_at' => date('Y-m-d H:i:s'),
    'generated_by' => $_SESSION['user_id'] ?? null,
    'bookings' => []
];

while($b = mysqli_fetch_assoc($bookingsRes)){
    $report['bookings'][] = [
        'id' => $b['id'],
        'user_id' => $b['user_id'],
        'customer_name' => $b['customer_name'],
        'customer_email' => $b['customer_email'],
        'checkin_date' => $b['checkin_date'],
        'checkout_date' => $b['checkout_date'],
        'status' => $b['status'],
        'payment' => $b['payment'],
        'created_at' => $b['created_at'] ?? ($b['booked_at'] ?? null),
        'review_rating' => $b['review_rating'] ?? null,
        'cleanliness' => $b['cleanliness'] ?? null,
        'services' => $b['services'] ?? null,
        'staff' => $b['staff'] ?? null,
        'review_text' => $b['review_text'] ?? null,
    ];
}

// Save report snapshot to DB (create table if necessary)
$createSql = "CREATE TABLE IF NOT EXISTS room_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    generated_at DATETIME NOT NULL,
    created_by INT DEFAULT NULL,
    report_json LONGTEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
mysqli_query($conn, $createSql);

$reportJson = mysqli_real_escape_string($conn, json_encode($report));
mysqli_query($conn, "INSERT INTO room_reports (room_id, generated_at, created_by, report_json) VALUES ('". (int)$room_id ."', '". date('Y-m-d H:i:s') ."', '". (int)($_SESSION['user_id'] ?? 0) ."', '$reportJson')");

$savedId = mysqli_insert_id($conn);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Room Report - <?php echo htmlspecialchars($room['title'] ?? 'Room'); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body{font-family:Segoe UI, Tahoma, sans-serif;background:#0b111e;color:#e9ecf2;margin:0;padding:24px}
        .card{background:rgba(255,255,255,0.04);padding:20px;border-radius:12px;margin-bottom:18px}
        table{width:100%;border-collapse:collapse}
        th,td{padding:10px;border-bottom:1px solid rgba(255,255,255,0.06);text-align:left}
        th{color:#d1d5db;text-transform:uppercase;font-size:12px}
        .small{font-size:0.9rem;color:#cbd5e1}
        .back{display:inline-block;margin-bottom:12px;color:#f3e6ce;text-decoration:none}
    </style>
</head>
<body>
    <a class="back" href="admin_home.php?section=reports">← Back to Reports</a>
    <div class="card">
        <h2>Room Report: <?php echo htmlspecialchars($room['title'] ?? 'Room '.$room_id); ?></h2>
        <p class="small">Generated at: <?php echo date('Y-m-d H:i:s'); ?> | Saved as report ID: <?php echo (int)$savedId; ?></p>
    </div>

    <div class="card">
        <h3>Room Summary</h3>
        <p class="small">Room: <?php echo htmlspecialchars($room['title'] ?? 'Room '.$room_id); ?> | Price: RM<?php echo htmlspecialchars($room['price'] ?? '0'); ?> | Location: <?php echo htmlspecialchars($room['location'] ?? 'N/A'); ?> | Availability: <?php echo htmlspecialchars($room['availability'] ?? 'N/A'); ?></p>
    </div>

    <div class="card">
        <h3>Bookings (<?php echo count($report['bookings']); ?>)</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Checkin</th>
                    <th>Checkout</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Booked At</th>
                    <th>Overall</th>
                    <th>Cleanliness</th>
                    <th>Services</th>
                    <th>Staff</th>
                    <th>Review</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($report['bookings'] as $b): ?>
                    <tr>
                        <td><?php echo (int)$b['id']; ?></td>
                        <td><?php echo htmlspecialchars($b['customer_name'] . "\n" . ($b['customer_email'] ?? '')); ?></td>
                        <td><?php echo htmlspecialchars($b['checkin_date']); ?></td>
                        <td><?php echo htmlspecialchars($b['checkout_date']); ?></td>
                        <td><?php echo htmlspecialchars($b['status']); ?></td>
                        <td><?php echo htmlspecialchars($b['payment']); ?></td>
                        <td><?php echo htmlspecialchars($b['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($b['review_rating'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($b['cleanliness'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($b['services'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($b['staff'] ?? ''); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($b['review_text'] ?? '')); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
