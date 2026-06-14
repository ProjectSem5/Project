<?php
session_start();
include "db.php";

if($_SESSION['role'] != 'staff'){
    header("Location: login.php");
}

if(isset($_POST['add'])){
    $title = $_POST['title'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $max_units = max(1, intval($_POST['max_units'] ?? 3));

    $image = '';
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $target_dir = "";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)){
            $image = basename($_FILES["image"]["name"]);
        }
    }

    mysqli_query($conn,
    "INSERT INTO rooms (title,price,description,image,availability,location,max_units)
    VALUES ('$title','$price','$description','$image','Available','$location','$max_units')");

    echo "Room added";
}
?>

<form method="POST" enctype="multipart/form-data">
Room Title: <input type="text" name="title" required><br>
Price: <input type="text" name="price" required><br>
Description: <textarea name="description"></textarea><br>
Location: <select name="location" required>
    <option value="JB">JB</option>
    <option value="KL">KL</option>
</select><br>
Max units per room type: <input type="number" name="max_units" min="1" value="3" required><br>
Image: <input type="file" name="image" accept="image/*"><br>
<button name="add">Add Room</button>
</form>