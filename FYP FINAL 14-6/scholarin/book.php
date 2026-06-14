<?php
session_start();
if($_SESSION['role'] != 'customer'){
    header("Location: login.php");
    exit();
}

include "db.php";
include "room_rules.php";

$user_id = $_SESSION['user_id'];

// Get user's location
$userRes = mysqli_query($conn, "SELECT location FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($userRes);
$location = $user && $user['location'] ? $user['location'] : 'JB';

$room_id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['room_id']) ? intval($_POST['room_id']) : 0);
$checkin = isset($_REQUEST['checkin']) ? $_REQUEST['checkin'] : '';
$checkout = isset($_REQUEST['checkout']) ? $_REQUEST['checkout'] : '';
$useFilteredDates = !empty($_GET['checkin']) && !empty($_GET['checkout']);
$autoBooking = isset($_GET['auto']) && $_GET['auto'] === '1' && $useFilteredDates;

$room = null;
$roomUnavailable = false;
if($room_id > 0){
    $roomResult = mysqli_query($conn, "SELECT * FROM rooms WHERE id='$room_id'");
    if(mysqli_num_rows($roomResult) > 0){
        $room = mysqli_fetch_assoc($roomResult);
        if($room['availability'] !== 'Available'){
            $roomUnavailable = true;
        }
    }
}

$success = '';
$error = '';

if($autoBooking || isset($_POST['book'])){
    if(isset($_POST['book'])){
        $checkin = $_POST['checkin'];
        $checkout = $_POST['checkout'];
    }

    if(!$room){
        $error = 'Selected room not found.';
    } elseif($roomUnavailable) {
        $error = 'This room is currently unavailable. Please choose another room.';
    } elseif(empty($checkin) || empty($checkout)) {
        $error = 'Please choose both check-in and check-out dates.';
    } else {
        $status = scholarin_room_status($conn, $room, $checkin, $checkout);
        if($status['availability'] !== 'Available' || $status['available_units'] <= 0){
            $error = 'This room type is fully booked for the selected dates. Please choose another room or date range.';
        } else {
            mysqli_query($conn,
            "INSERT INTO bookings (user_id,room_id,status,payment,checkin_date,checkout_date)
            VALUES ('$user_id','$room_id','Pending','Unpaid','$checkin','$checkout')");
            $success = 'Booked successfully! Your reservation is now pending approval.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Room</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="LandingStyle.css">
    <style>
        .booking-page {
            min-height: 100vh;
        }

        .booking-header {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 30px 40px;
        }

        .location-selector {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(247, 244, 238, 0.85);
            font-size: 0.95rem;
        }

        .location-selector label {
            font-weight: 600;
            color: #f5f1e9;
        }

        .location-select {
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: rgba(255, 255, 255, 0.08);
            color: #000;
            padding: 10px 14px;
            font-size: 0.95rem;
            min-width: 90px;
            cursor: pointer;
        }

        .location-select:focus {
            outline: none;
            border-color: rgba(243, 230, 206, 0.6);
            background: rgba(255, 255, 255, 0.14);
        }

        .booking-title {
            font-size: 1.6rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            font-weight: 700;
            color: #f5f1e9;
        }

        .booking-main {
            position: relative;
            z-index: 1;
            padding: 72px 40px 40px;
            max-width: 980px;
            margin: 0 auto;
            width: 100%;
        }

        .booking-card {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 32px;
            padding: 36px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.22);
            display: grid;
            gap: 24px;
        }

        .booking-card h1 {
            margin: 0;
            font-size: clamp(2.2rem, 4vw, 3rem);
            color: #fff;
            line-height: 1.02;
        }

        .booking-card p {
            margin: 0;
            color: rgba(245, 241, 233, 0.88);
            line-height: 1.8;
            font-size: 1rem;
        }

        .room-details {
            display: grid;
            gap: 10px;
            color: rgba(247, 244, 238, 0.9);
        }

        .room-details span {
            font-size: 0.95rem;
        }

        .booking-form {
            display: grid;
            gap: 20px;
        }

        .form-group {
            display: grid;
            gap: 10px;
        }

        .form-group label {
            color: rgba(255, 255, 255, 0.85);
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        .form-group input {
            width: 100%;
            padding: 16px 18px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-size: 0.95rem;
            transition: all 0.25s ease;
        }

        .form-group input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.16);
            border-color: rgba(243, 230, 206, 0.5);
            box-shadow: 0 0 0 6px rgba(243, 230, 206, 0.1);
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            justify-content: flex-start;
        }

        .submit-btn,
        .back-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 16px 28px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 700;
            transition: transform 0.25s ease, background 0.25s ease, box-shadow 0.25s ease;
        }

        .submit-btn {
            background: linear-gradient(135deg, rgba(74, 95, 176, 0.95), rgba(111, 140, 236, 0.98));
            color: #fff;
            border: none;
        }

        .back-btn {
            background: rgba(255, 255, 255, 0.14);
            color: #f7f4ee;
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .submit-btn:hover,
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(0, 0, 0, 0.18);
        }

        .message {
            padding: 16px 20px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border: 1px solid;
            font-weight: 600;
        }

        .message.success {
            background: rgba(76, 175, 80, 0.18);
            color: #c8f2c8;
            border-color: rgba(76, 175, 80, 0.4);
        }

        .message.error {
            background: rgba(235, 94, 94, 0.18);
            color: #ffb0b0;
            border-color: rgba(235, 94, 94, 0.4);
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 800px) {
            .booking-header,
            .booking-main {
                padding-left: 24px;
                padding-right: 24px;
            }

            .booking-header {
                flex-direction: column;
                gap: 16px;
                align-items: flex-start;
            }

            .location-selector {
                width: 100%;
            }
        }

        @media (max-width: 620px) {
            .booking-card {
                padding: 28px;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="landing-page booking-page">
        <div class="hero-overlay"></div>

        <header class="booking-header">
            <div class="booking-title"><i class="fas fa-calendar-check"></i> Room Booking</div>
            <div class="location-selector">
                <label for="location-select">Location</label>
                <select id="location-select" class="location-select" onchange="changeLocation()">
                    <option value="JB" <?php echo $location === 'JB' ? 'selected' : ''; ?>>JB</option>
                    <option value="KL" <?php echo $location === 'KL' ? 'selected' : ''; ?>>KL</option>
                </select>
            </div>
            <div class="action-buttons">
                <a href="view_rooms.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Rooms</a>
                <a href="customer_home.php" class="back-btn"><i class="fas fa-home"></i> Dashboard</a>
            </div>
        </header>

        <main class="booking-main">
            <div class="booking-card">
                <?php if($room): ?>
                    <h1>Book: <?php echo htmlspecialchars($room['title']); ?></h1>
                    <div class="room-details">
                        <span><strong>Price:</strong> RM <?php echo htmlspecialchars($room['price']); ?></span>
                        <?php if(!empty($room['description'])): ?>
                            <span><strong>Description:</strong> <?php echo htmlspecialchars($room['description']); ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if($success): ?>
                        <div class="message success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?> <span style="margin-left:8px;color:#ffffff;">Check your booking status for the latest updates.</span></div>
                    <?php endif; ?>
                    <?php if($error): ?>
                        <div class="message error"><i class="fas fa-times-circle"></i> <?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <?php if(!$success && !$roomUnavailable): ?>
                        <form method="POST" class="booking-form">
                            <?php if($useFilteredDates): ?>
                                <div class="form-group">
                                    <label>Selected Dates</label>
                                    <input type="text" value="<?php echo htmlspecialchars($checkin); ?> → <?php echo htmlspecialchars($checkout); ?>" readonly style="background:rgba(255,255,255,0.08);color:#fff;cursor:default;">
                                    <input type="hidden" name="checkin" value="<?php echo htmlspecialchars($checkin); ?>">
                                    <input type="hidden" name="checkout" value="<?php echo htmlspecialchars($checkout); ?>">
                                </div>
                            <?php else: ?>
                                <div class="form-group">
                                    <label for="checkin">Check-in Date</label>
                                    <input type="date" id="checkin" name="checkin" value="<?php echo htmlspecialchars($checkin); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="checkout">Check-out Date</label>
                                    <input type="date" id="checkout" name="checkout" value="<?php echo htmlspecialchars($checkout); ?>" required>
                                </div>
                            <?php endif; ?>

                            <button type="submit" name="book" class="submit-btn"><i class="fas fa-bed"></i> Book Now</button>
                        </form>
                    <?php elseif($roomUnavailable): ?>
                        <div class="message error"><i class="fas fa-times-circle"></i> This room is unavailable and cannot be booked. Please return to room selection.</div>
                    <?php endif; ?>
                <?php else: ?>
                    <h1>Room Not Found</h1>
                    <p>We couldn't find the room you are trying to book. Please return to the room list and select a room again.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script>
        let currentLocation = '<?php echo $location; ?>';

        function applyBackgroundImage() {
            const imageUrl = currentLocation === 'KL' ? 'SCHOLAR INN KL.webp' : 'Scholar inn image out side.jpg';
            const pageDiv = document.querySelector('.landing-page');
            pageDiv.style.backgroundImage = `url('${imageUrl}')`;
        }

        function changeLocation() {
            const newLocation = document.getElementById('location-select').value;
            if(newLocation === currentLocation) return;

            currentLocation = newLocation;
            applyBackgroundImage();

            // Save location to database
            fetch('save_location.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'location=' + encodeURIComponent(newLocation)
            });

            // Show room selection modal/dialog
            showRoomSelector(newLocation);
        }

        function showRoomSelector(location) {
            // Create and show modal for room selection
            const modal = document.createElement('div');
            modal.id = 'roomSelectorModal';
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.7);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1000;
            `;

            const modalContent = document.createElement('div');
            modalContent.style.cssText = `
                background: rgba(255, 255, 255, 0.08);
                border: 1px solid rgba(255, 255, 255, 0.14);
                border-radius: 32px;
                padding: 36px;
                max-width: 600px;
                width: 90%;
                max-height: 80vh;
                overflow-y: auto;
                color: #f5f1e9;
            `;

            modalContent.innerHTML = `
                <h2 style="margin: 0 0 20px 0; font-size: 1.6rem;">Select a Room in ${location}</h2>
                <div id="roomsLoadingSpinner" style="text-align: center; padding: 40px;">
                    <div style="width: 50px; height: 50px; border: 4px solid rgba(255,255,255,0.2); border-top: 4px solid #f3e6ce; border-radius: 50%; margin: 0 auto; animation: spin 1s linear infinite;"></div>
                </div>
                <div id="roomsList"></div>
                <button onclick="closeRoomSelector()" style="
                    margin-top: 20px;
                    padding: 12px 24px;
                    background: rgba(235, 94, 94, 0.18);
                    border: 1px solid rgba(235, 94, 94, 0.34);
                    color: #ffb0b0;
                    border-radius: 999px;
                    cursor: pointer;
                    font-weight: 600;
                ">Close</button>
            `;

            modal.appendChild(modalContent);
            document.body.appendChild(modal);

            // Fetch rooms from the selected location
            fetchRoomsByLocation(location);
        }

        function fetchRoomsByLocation(location) {
            fetch('get_rooms_by_location.php?location=' + encodeURIComponent(location))
                .then(response => response.json())
                .then(data => {
                    const spinner = document.getElementById('roomsLoadingSpinner');
                    const roomsList = document.getElementById('roomsList');

                    if(spinner) spinner.style.display = 'none';

                    if(!data.success) {
                        roomsList.innerHTML = '<p style="color: #ffb0b0;">Error loading rooms. Please try again.</p>';
                        return;
                    }

                    if(data.rooms.length === 0) {
                        roomsList.innerHTML = '<p style="color: rgba(245,241,233,0.7);">No rooms available in ' + location + '.</p>';
                        return;
                    }

                    let html = '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;">';
                    data.rooms.forEach(room => {
                        html += `
                            <div style="
                                background: rgba(255, 255, 255, 0.08);
                                border: 1px solid rgba(255, 255, 255, 0.12);
                                border-radius: 16px;
                                padding: 16px;
                                cursor: pointer;
                                transition: all 0.25s ease;
                            " onmouseover="this.style.background='rgba(255, 255, 255, 0.12)'" 
                               onmouseout="this.style.background='rgba(255, 255, 255, 0.08)'"
                               onclick="selectRoom(${room.id}, '${room.title.replace(/'/g, "\\'")}', '${room.price.replace(/'/g, "\\'")}', '${room.description ? room.description.replace(/'/g, "\\'") : ''}')">
                                ${room.image ? `<img src="uploads/${room.image}" style="width: 100%; height: 120px; object-fit: cover; border-radius: 8px; margin-bottom: 12px;">` : ''}
                                <h4 style="margin: 0 0 8px 0; font-size: 0.95rem;">${room.title}</h4>
                                <p style="margin: 0; color: rgba(245,241,233,0.8); font-weight: 600;">RM ${room.price}</p>
                            </div>
                        `;
                    });
                    html += '</div>';
                    roomsList.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    const spinner = document.getElementById('roomsLoadingSpinner');
                    const roomsList = document.getElementById('roomsList');
                    if(spinner) spinner.style.display = 'none';
                    roomsList.innerHTML = '<p style="color: #ffb0b0;">Failed to load rooms. Please try again.</p>';
                });
        }

        function selectRoom(roomId, roomTitle, roomPrice, roomDescription) {
            closeRoomSelector();
            updateBookingCard(roomId, roomTitle, roomPrice, roomDescription);
        }

        function updateBookingCard(roomId, roomTitle, roomPrice, roomDescription) {
            const bookingCard = document.querySelector('.booking-card');
            if(!bookingCard) {
                // Create booking card if it doesn't exist
                const main = document.querySelector('.booking-main');
                const newCard = document.createElement('div');
                newCard.className = 'booking-card';
                main.appendChild(newCard);
            }

            // Generate the new HTML for the room
            let html = `
                <h1>Book: ${roomTitle}</h1>
                <div class="room-details">
                    <span><strong>Price:</strong> RM ${roomPrice}</span>
                    ${roomDescription ? `<span><strong>Description:</strong> ${roomDescription}</span>` : ''}
                </div>
                <form method="POST" class="booking-form" onsubmit="return updateBookingForm(${roomId})">
                    <div class="form-group">
                        <label for="checkin">Check-in Date</label>
                        <input type="date" id="checkin" name="checkin" value="" required>
                    </div>
                    <div class="form-group">
                        <label for="checkout">Check-out Date</label>
                        <input type="date" id="checkout" name="checkout" value="" required>
                    </div>
                    <button type="submit" name="book" class="submit-btn"><i class="fas fa-bed"></i> Book Now</button>
                </form>
            `;

            document.querySelector('.booking-card').innerHTML = html;
        }

        function updateBookingForm(roomId) {
            // Update the hidden room_id when form is submitted
            const form = document.querySelector('.booking-form');
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'room_id';
            input.value = roomId;
            form.appendChild(input);
            return true;
        }

        function closeRoomSelector() {
            const modal = document.getElementById('roomSelectorModal');
            if(modal) modal.remove();
        }

        document.addEventListener('DOMContentLoaded', function() {
            applyBackgroundImage();
        });
    </script>
</body>
</html>