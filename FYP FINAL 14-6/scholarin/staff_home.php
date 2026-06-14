<?php
session_start();
include "db.php";

if($_SESSION['role'] != 'staff'){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$staff_name = $_SESSION['user_name'] ?? 'Staff';
$message = '';
$error = '';
$section = isset($_GET['section']) ? $_GET['section'] : 'overview';
$edit_room_id = isset($_GET['edit_room_id']) ? intval($_GET['edit_room_id']) : 0;
$customer_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;

// Helper function to log staff activities
function log_staff_activity($conn, $staff_id, $staff_name, $action, $details) {
    $staff_name = mysqli_real_escape_string($conn, $staff_name);
    $action = mysqli_real_escape_string($conn, $action);
    $details = mysqli_real_escape_string($conn, $details);
    mysqli_query($conn, "INSERT INTO system_activities (user_id, user_name, role, action, details) VALUES ('$staff_id', '$staff_name', 'staff', '$action', '$details')");
}


if(isset($_POST['add_room'])){
    $title = $_POST['title'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $availability = $_POST['availability'];
    $max_units = max(1, intval($_POST['max_units'] ?? 3));
    $image = '';

    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $target_dir = "";
        $target_file = $target_dir . basename($_FILES['image']['name']);
        if(move_uploaded_file($_FILES['image']['tmp_name'], $target_file)){
            $image = basename($_FILES['image']['name']);
        }
    }

    mysqli_query($conn, "INSERT INTO rooms (title, price, description, image, availability, location, max_units) VALUES ('$title','$price','$description','$image','$availability','$location','$max_units')");
    log_staff_activity($conn, $user_id, $staff_name, 'Added room', "Title: $title, Price: RM$price, Location: $location");
    $message = 'Room added successfully.';
    $section = 'rooms';
}

if(isset($_POST['update_room'])){
    $id = intval($_POST['room_id']);
    $title = $_POST['title'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $availability = $_POST['availability'];
    $max_units = max(1, intval($_POST['max_units'] ?? 3));

    $roomRes = mysqli_query($conn, "SELECT image FROM rooms WHERE id='$id'");
    $room = mysqli_fetch_assoc($roomRes);
    $image = $room['image'];
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $target_dir = "";
        $target_file = $target_dir . basename($_FILES['image']['name']);
        if(move_uploaded_file($_FILES['image']['tmp_name'], $target_file)){
            $image = basename($_FILES['image']['name']);
        }
    }

    mysqli_query($conn, "UPDATE rooms SET title='$title', price='$price', description='$description', location='$location', availability='$availability', image='$image', max_units='$max_units' WHERE id='$id'");
    log_staff_activity($conn, $user_id, $staff_name, 'Edited room', "Room ID: $id, Title: $title, Price: RM$price");
    $message = 'Room updated successfully.';
    $section = 'rooms';
}

if(isset($_POST['delete_room'])){
    $id = intval($_POST['room_id']);
    $roomRes = mysqli_query($conn, "SELECT image FROM rooms WHERE id='$id'");
    $room = mysqli_fetch_assoc($roomRes);
    if($room && !empty($room['image'])){
        $path = __DIR__ . '/' . $room['image'];
        if(file_exists($path)){
            @unlink($path);
        }
    }
    mysqli_query($conn, "DELETE FROM rooms WHERE id='$id'");
    log_staff_activity($conn, $user_id, $staff_name, 'Deleted room', "Room ID: $id");
    $message = 'Room deleted successfully.';
    $section = 'rooms';
}

if(isset($_POST['approve_booking'])){
    $booking_id = intval($_POST['booking_id']);
    mysqli_query($conn, "UPDATE bookings SET status='Approved' WHERE id='$booking_id'");
    log_staff_activity($conn, $user_id, $staff_name, 'Approved booking', "Booking ID: $booking_id");
    $message = 'Booking approved.';
    $section = 'booking_requests';
}

if(isset($_POST['reject_booking'])){
    $booking_id = intval($_POST['booking_id']);
    mysqli_query($conn, "UPDATE bookings SET status='Rejected' WHERE id='$booking_id'");
    log_staff_activity($conn, $user_id, $staff_name, 'Rejected booking', "Booking ID: $booking_id");
    $message = 'Booking rejected.';
    $section = 'booking_requests';
}

if(isset($_POST['profile_update'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    mysqli_query($conn, "UPDATE users SET name='$name', email='$email' WHERE id='$user_id'");
    log_staff_activity($conn, $user_id, $staff_name, 'Updated profile', "Name: $name, Email: $email");
    $message = 'Profile updated successfully.';
    $section = 'profile';
}

if(isset($_POST['change_password'])){
    $old = $_POST['old_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if($new !== $confirm){
        $error = 'New password and confirmation do not match.';
    } elseif($new === $old) {
        $error = 'New password cannot be the same as the old password.';
    } else {
        $check = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id' AND password='$old'");
        if(mysqli_num_rows($check) > 0){
            mysqli_query($conn, "UPDATE users SET password='$new' WHERE id='$user_id'");
            log_staff_activity($conn, $user_id, $staff_name, 'Changed password', 'Password reset');
            $message = 'Password changed successfully.';
        } else {
            $error = 'Current password is incorrect.';
        }
    }
    $section = 'profile';
}

if($edit_room_id){
    $editRoomResult = mysqli_query($conn, "SELECT * FROM rooms WHERE id='$edit_room_id'");
    $editRoom = mysqli_fetch_assoc($editRoomResult);
    $section = 'rooms';
}

if($customer_id){
    $customerRes = mysqli_query($conn, "SELECT * FROM users WHERE id='$customer_id'");
    $customer = mysqli_fetch_assoc($customerRes);
    $customerBookings = mysqli_query($conn, "SELECT b.*, r.title AS room_title FROM bookings b LEFT JOIN rooms r ON b.room_id=r.id WHERE b.user_id='$customer_id' ORDER BY b.id DESC");
    $section = 'customer_details';
}

$rooms = mysqli_query($conn, "SELECT * FROM rooms ORDER BY id DESC");
$bookings = mysqli_query($conn, "SELECT b.*, u.name AS customer_name, u.email AS customer_email, u.ic AS customer_ic, r.title AS room_title FROM bookings b LEFT JOIN users u ON b.user_id = u.id LEFT JOIN rooms r ON b.room_id = r.id ORDER BY b.id DESC");
$staff = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'"));
$roomCount = mysqli_num_rows($rooms);
$pendingCount = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM bookings WHERE status='Pending'"));
$approvedCount = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM bookings WHERE status='Approved'"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; min-height: 100vh; background: #f4f6fb; }
        .page { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
        .header h1 { margin: 0; font-size: 1.8rem; }
        .logout-button { padding: 10px 16px; border-radius: 8px; background: #d33; color: white; text-decoration: none; }
        .nav { display: flex; flex-wrap: wrap; gap: 10px; margin: 20px 0; }
        .nav a { padding: 10px 16px; border-radius: 8px; background: white; border: 1px solid #ccd1db; text-decoration: none; color: #222; }
        .nav a.active { background: #0f3b79; color: white; border-color: #0f3b79; }
        .card { background: white; border: 1px solid #ccd1db; border-radius: 16px; padding: 20px; margin-bottom: 20px; }
        .card h2 { margin-top: 0; }
        .grid { display: grid; gap: 16px; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
        .stat { padding: 18px; border-radius: 14px; background: #eef2fb; }
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 700; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px 12px; border: 1px solid #ccd1db; border-radius: 10px; }
        .form-actions { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; }
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 11px 18px; border-radius: 10px; border: none; cursor: pointer; text-decoration: none; color: white; background: #0f3b79; }
        .btn.secondary { background: #5c6a82; }
        .message { padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; }
        .message.success { background: #eaffea; color: #256a1f; border: 1px solid #8ed08a; }
        .message.error { background: #ffe9e9; color: #a12a2a; border: 1px solid #e09b9b; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        table th, table td { padding: 12px; border: 1px solid #d7dce9; vertical-align: top; }
        table th { background: #f6f8fc; text-align: left; }
        .small-link { color: #0f3b79; text-decoration: none; font-size: 0.95rem; }
        .small-link:hover { text-decoration: underline; }
        .actions form { display: inline; }
        @media (max-width: 840px) { .header, .form-actions { flex-direction: column; align-items: stretch; } }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <div>
                <h1>Staff Dashboard</h1>
                <div>Welcome, <?php echo htmlspecialchars($staff['name']); ?></div>
            </div>
            <a href="logout.php" class="logout-button">Sign Out</a>
        </div>

        <?php if($message): ?><div class="message success"><?php echo $message; ?></div><?php endif; ?>
        <?php if($error): ?><div class="message error"><?php echo $error; ?></div><?php endif; ?>

        <div class="nav">
            <a href="staff_home.php?section=overview" class="<?php echo $section=='overview' ? 'active' : ''; ?>">Overview</a>
            <a href="staff_home.php?section=add_room" class="<?php echo $section=='add_room' ? 'active' : ''; ?>">Add Room</a>
            <a href="staff_home.php?section=rooms" class="<?php echo $section=='rooms' ? 'active' : ''; ?>">Room Listings</a>
            <a href="staff_home.php?section=booking_requests" class="<?php echo $section=='booking_requests' ? 'active' : ''; ?>">Booking Requests</a>
            <a href="staff_home.php?section=booking_history" class="<?php echo $section=='booking_history' ? 'active' : ''; ?>">Booking History</a>
            <a href="staff_home.php?section=profile" class="<?php echo $section=='profile' ? 'active' : ''; ?>">Edit Profile</a>
        </div>

        <div class="card" style="display: <?php echo $section=='overview' ? 'block' : 'none'; ?>;">
            <h2>Overview</h2>
            <div class="grid">
                <div class="stat"><strong><?php echo $roomCount; ?></strong><div>Rooms</div></div>
                <div class="stat"><strong><?php echo $pendingCount; ?></strong><div>Pending Requests</div></div>
                <div class="stat"><strong><?php echo $approvedCount; ?></strong><div>Approved Bookings</div></div>
                <div class="stat"><strong><?php echo mysqli_num_rows($bookings); ?></strong><div>Total Bookings</div></div>
            </div>
        </div>

        <div class="card" style="display: <?php echo $section=='add_room' ? 'block' : 'none'; ?>;">
            <h2>Add Room</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group"><label>Room Title</label><input type="text" name="title" required></div>
                <div class="form-group"><label>Price</label><input type="text" name="price" required></div>
                <div class="form-group"><label>Location</label><select name="location" required><option value="JB">JB</option><option value="KL">KL</option></select></div>
                <div class="form-group"><label>Max units</label><input type="number" name="max_units" min="1" value="3" required></div>
                <div class="form-group"><label>Availability</label><select name="availability" required><option value="Available">Available</option><option value="Unavailable">Unavailable</option></select></div>
                <div class="form-group"><label>Description</label><textarea name="description"></textarea></div>
                <div class="form-group"><label>Image</label><input type="file" name="image" accept="image/*"></div>
                <button type="submit" name="add_room" class="btn">Add Room</button>
            </form>
        </div>

        <div class="card" style="display: <?php echo $section=='rooms' ? 'block' : 'none'; ?>;">
            <h2>Room Listings</h2>
            <?php if($edit_room_id && $editRoom): ?>
                <form method="POST" enctype="multipart/form-data" style="margin-bottom:24px;">
                    <h3>Edit Room #<?php echo $editRoom['id']; ?></h3>
                    <input type="hidden" name="room_id" value="<?php echo $editRoom['id']; ?>">
                    <div class="form-group"><label>Title</label><input type="text" name="title" value="<?php echo htmlspecialchars($editRoom['title']); ?>" required></div>
                    <div class="form-group"><label>Price</label><input type="text" name="price" value="<?php echo htmlspecialchars($editRoom['price']); ?>" required></div>
                    <div class="form-group"><label>Location</label><select name="location" required><option value="JB" <?php echo $editRoom['location']=='JB' ? 'selected' : ''; ?>>JB</option><option value="KL" <?php echo $editRoom['location']=='KL' ? 'selected' : ''; ?>>KL</option></select></div>
                    <div class="form-group"><label>Max units</label><input type="number" name="max_units" min="1" value="<?php echo intval($editRoom['max_units'] ?? 3); ?>" required></div>
                    <div class="form-group"><label>Availability</label><select name="availability" required><option value="Available" <?php echo $editRoom['availability']=='Available' ? 'selected' : ''; ?>>Available</option><option value="Unavailable" <?php echo $editRoom['availability']=='Unavailable' ? 'selected' : ''; ?>>Unavailable</option></select></div>
                    <div class="form-group"><label>Description</label><textarea name="description"><?php echo htmlspecialchars($editRoom['description']); ?></textarea></div>
                    <div class="form-group"><label>Image (leave blank to keep current)</label><input type="file" name="image" accept="image/*"></div>
                    <div class="form-actions"><button type="submit" name="update_room" class="btn">Save Changes</button><a href="staff_home.php?section=rooms" class="btn secondary">Cancel</a></div>
                </form>
            <?php endif; ?>
            <table>
                <tr><th>ID</th><th>Title</th><th>Price</th><th>Location</th><th>Max Units</th><th>Availability</th><th>Description</th><th>Image</th><th>Actions</th></tr>
                <?php mysqli_data_seek($rooms, 0); while($room = mysqli_fetch_assoc($rooms)): ?>
                    <tr>
                        <td><?php echo $room['id']; ?></td>
                        <td><?php echo htmlspecialchars($room['title']); ?></td>
                        <td><?php echo htmlspecialchars($room['price']); ?></td>
                        <td><?php echo htmlspecialchars($room['location']); ?></td>
                        <td><?php echo intval($room['max_units'] ?? 3); ?></td>
                        <td><?php echo htmlspecialchars($room['availability']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($room['description'])); ?></td>
                        <td><?php if($room['image']): ?><img src="<?php echo htmlspecialchars($room['image']); ?>" alt="Room" style="width:100px; border-radius:10px;"><?php else: ?>No image<?php endif; ?></td>
                        <td class="actions">
                            <a class="small-link" href="staff_home.php?section=rooms&edit_room_id=<?php echo $room['id']; ?>">Edit</a><br>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Confirm delete this room?');">
                                <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                                <button type="submit" name="delete_room" class="btn secondary" style="padding:6px 10px;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <div class="card" style="display: <?php echo $section=='booking_requests' ? 'block' : 'none'; ?>;">
            <h2>Booking Requests</h2>
            <table>
                <tr><th>ID</th><th>Customer</th><th>IC</th><th>Room</th><th>Status</th><th>Deposit</th><th>Full Payment</th><th>Check-in</th><th>Check-out</th><th>Receipt</th><th>Actions</th></tr>
                <?php mysqli_data_seek($bookings, 0); while($row = mysqli_fetch_assoc($bookings)): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['customer_name'] ?: 'Unknown'); ?><br><?php echo htmlspecialchars($row['customer_email'] ?: ''); ?><br><?php if($row['user_id']): ?><a class="small-link" href="staff_home.php?section=customer_details&customer_id=<?php echo $row['user_id']; ?>">Details</a><?php endif; ?></td>
                        <td><?php echo htmlspecialchars($row['customer_ic'] ?: ''); ?></td>
                        <td><?php echo htmlspecialchars($row['room_title'] ?: 'Unknown'); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td><?php echo htmlspecialchars($row['payment']); ?></td>
                        <td><?php echo htmlspecialchars($row['full_payment']); ?></td>
                        <td><?php echo htmlspecialchars($row['checkin_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['checkout_date']); ?></td>
                        <td><?php if(!empty($row['receipt'])): ?><a class="small-link" href="<?php echo htmlspecialchars($row['receipt']); ?>" target="_blank">View</a><?php else: ?>None<?php endif; ?></td>
                        <td>
                            <?php if($row['status'] !== 'Approved'): ?>
                                <form method="POST" style="display:inline; margin-bottom:6px;"><input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>"><button type="submit" name="approve_booking" class="btn">Approve</button></form>
                            <?php endif; ?>
                            <form method="POST" style="display:inline;"><input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>"><button type="submit" name="reject_booking" class="btn secondary">Reject</button></form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <div class="card" style="display: <?php echo $section=='booking_history' ? 'block' : 'none'; ?>;">
            <h2>Booking History</h2>
            <table>
                <tr><th>ID</th><th>Customer</th><th>IC</th><th>Room</th><th>Status</th><th>Deposit</th><th>Full Payment</th><th>Check-in</th><th>Check-out</th><th>Customer Details</th></tr>
                <?php mysqli_data_seek($bookings, 0); while($row = mysqli_fetch_assoc($bookings)): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['customer_name'] ?: 'Unknown'); ?><br><?php echo htmlspecialchars($row['customer_email'] ?: ''); ?></td>
                        <td><?php echo htmlspecialchars($row['customer_ic'] ?: ''); ?></td>
                        <td><?php echo htmlspecialchars($row['room_title'] ?: 'Unknown'); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td><?php echo htmlspecialchars($row['payment']); ?></td>
                        <td><?php echo htmlspecialchars($row['full_payment']); ?></td>
                        <td><?php echo htmlspecialchars($row['checkin_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['checkout_date']); ?></td>
                        <td><?php if($row['user_id']): ?><a class="small-link" href="staff_home.php?section=customer_details&customer_id=<?php echo $row['user_id']; ?>">View</a><?php else: ?>N/A<?php endif; ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <?php if($section=='customer_details' && isset($customer)): ?>
            <div class="card">
                <h2>Customer Details</h2>
                <div class="form-group"><label>Name</label><div><?php echo htmlspecialchars($customer['name']); ?></div></div>
                <div class="form-group"><label>Email</label><div><?php echo htmlspecialchars($customer['email']); ?></div></div>
                <div class="form-group"><label>IC</label><div><?php echo htmlspecialchars($customer['ic']); ?></div></div>
                <div class="form-group"><label>Location</label><div><?php echo htmlspecialchars($customer['location']); ?></div></div>
                <h3>Customer Booking History</h3>
                <table>
                    <tr><th>ID</th><th>Room</th><th>Status</th><th>Deposit</th><th>Full Payment</th><th>Check-in</th><th>Check-out</th></tr>
                    <?php while($booking = mysqli_fetch_assoc($customerBookings)): ?>
                        <tr>
                            <td><?php echo $booking['id']; ?></td>
                            <td><?php echo htmlspecialchars($booking['room_title'] ?: 'Unknown'); ?></td>
                            <td><?php echo htmlspecialchars($booking['status']); ?></td>
                            <td><?php echo htmlspecialchars($booking['payment']); ?></td>
                            <td><?php echo htmlspecialchars($booking['full_payment']); ?></td>
                            <td><?php echo htmlspecialchars($booking['checkin_date']); ?></td>
                            <td><?php echo htmlspecialchars($booking['checkout_date']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        <?php endif; ?>

        <div class="card" style="display: <?php echo $section=='profile' ? 'block' : 'none'; ?>;">
            <h2>My Profile</h2>
            <form method="POST">
                <div class="form-group"><label>Full Name</label><input type="text" name="name" value="<?php echo htmlspecialchars($staff['name']); ?>" required></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" value="<?php echo htmlspecialchars($staff['email']); ?>" required></div>
                <button type="submit" name="profile_update" class="btn">Update Profile</button>
            </form>
            <hr style="margin:24px 0;">
            <h3>Change Password</h3>
            <form method="POST">
                <div class="form-group"><label>Current Password</label><input type="password" name="old_password" required></div>
                <div class="form-group"><label>New Password</label><input type="password" name="new_password" required></div>
                <div class="form-group"><label>Confirm New Password</label><input type="password" name="confirm_password" required></div>
                <button type="submit" name="change_password" class="btn">Change Password</button>
            </form>
        </div>
    </div>
</body>
</html>