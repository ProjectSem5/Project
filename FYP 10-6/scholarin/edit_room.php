<?php
session_start();
include "db.php";

if($_SESSION['role'] != 'staff'){
    header("Location: login.php");
    exit();
}

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    header("Location: staff_rooms.php");
    exit();
}

$id = $_GET['id'];
$message = '';

$roomRes = mysqli_query($conn, "SELECT * FROM rooms WHERE id='$id'");
$room = mysqli_fetch_assoc($roomRes);
if(!$room){
    header("Location: staff_rooms.php");
    exit();
}

if(isset($_POST['update'])){
    $title = $_POST['title'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $availability = $_POST['availability'];
    $max_units = max(1, intval($_POST['max_units'] ?? $room['max_units'] ?? 3));

    $image = $room['image'];
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)){
            $image = basename($_FILES["image"]["name"]);
        }
    }

    mysqli_query($conn,
        "UPDATE rooms SET title='$title', price='$price', description='$description', location='$location', availability='$availability', image='$image', max_units='$max_units' WHERE id='$id'");

    $message = 'Room updated successfully.';
    $room = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM rooms WHERE id='$id'"));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Room</title>
    <link rel="stylesheet" href="LandingStyle.css">
    <style>
        .form-wrapper { max-width: 650px; margin: 40px auto; padding: 24px; border: 1px solid #ccc; border-radius: 16px; background: #fff; }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 8px; font-weight: 700; }
        input, textarea, select { width: 100%; padding: 12px; border: 1px solid #bbb; border-radius: 10px; }
        textarea { min-height: 120px; resize: vertical; }
        button { padding: 12px 20px; border: none; background: #0f3b79; color: #fff; border-radius: 10px; cursor: pointer; }
        .message { margin-bottom: 18px; padding: 12px 16px; border-radius: 12px; background: #e6ffec; border: 1px solid #8fd19a; }
        .current-image { margin-top: 8px; max-width: 180px; display: block; }
        .back-link { display: inline-block; margin-bottom: 16px; }
    </style>
</head>
<body>
    <div class="form-wrapper">
        <a href="staff_rooms.php" class="back-link">← Back to Room Listings</a>
        <h1>Edit Room #<?php echo $room['id']; ?></h1>

        <?php if($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Room Title</label>
                <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($room['title']); ?>" required>
            </div>
            <div class="form-group">
                <label for="price">Price</label>
                <input type="text" name="price" id="price" value="<?php echo htmlspecialchars($room['price']); ?>" required>
            </div>
            <div class="form-group">
                <label for="location">Location</label>
                <select name="location" id="location" required>
                    <option value="JB" <?php echo $room['location'] == 'JB' ? 'selected' : ''; ?>>JB</option>
                    <option value="KL" <?php echo $room['location'] == 'KL' ? 'selected' : ''; ?>>KL</option>
                </select>
            </div>
            <div class="form-group">
                <label for="max_units">Max units for this room type</label>
                <input type="number" name="max_units" id="max_units" min="1" value="<?php echo intval($room['max_units'] ?? 3); ?>" required>
            </div>
            <div class="form-group">
                <label for="availability">Availability</label>
                <select name="availability" id="availability" required>
                    <option value="Available" <?php echo $room['availability'] == 'Available' ? 'selected' : ''; ?>>Available</option>
                    <option value="Unavailable" <?php echo $room['availability'] == 'Unavailable' ? 'selected' : ''; ?>>Unavailable</option>
                </select>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description"><?php echo htmlspecialchars($room['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="image">Room Image (leave blank to keep current)</label>
                <input type="file" name="image" id="image" accept="image/*">
                <?php if(!empty($room['image'])): ?>
                    <img class="current-image" src="uploads/<?php echo htmlspecialchars($room['image']); ?>" alt="Current room image">
                <?php endif; ?>
            </div>
            <button type="submit" name="update">Save Room</button>
        </form>
    </div>
</body>
</html>
