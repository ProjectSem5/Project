<?php
session_start();
if($_SESSION['role'] != 'customer'){
    header("Location: login.php");
    exit();
}

include "db.php";
include "room_rules.php";

$user_id = $_SESSION['user_id'];
$checkin = isset($_GET['checkin']) ? $_GET['checkin'] : '';
$checkout = isset($_GET['checkout']) ? $_GET['checkout'] : '';

// Get location from URL or database
if(isset($_GET['loc'])){
    $location = $_GET['loc'];
} else {
    // Fetch from database
    $userRes = mysqli_query($conn, "SELECT location FROM users WHERE id='$user_id'");
    $user = mysqli_fetch_assoc($userRes);
    $location = $user && $user['location'] ? $user['location'] : 'JB';
}

$filterActive = !empty($checkin) && !empty($checkout);

$res = mysqli_query($conn, "SELECT * FROM rooms WHERE location='$location' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Rooms</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="LandingStyle.css">
    <style>
        .room-page {
            min-height: 100vh;
        }

        .room-header {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 30px 40px;
        }

        .room-title-bar {
            color: #f5f1e9;
            font-size: 1.6rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            font-weight: 700;
        }

        .room-main {
            position: relative;
            z-index: 1;
            padding: 72px 40px 40px;
            max-width: 1180px;
            margin: 0 auto;
            width: 100%;
        }

        .filter-card {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 28px;
            padding: 28px 32px;
            display: grid;
            gap: 18px;
            box-shadow: 0 18px 60px rgba(0, 0, 0, 0.18);
        }

        .filter-card h2 {
            margin: 0;
            color: #f9f6ef;
            font-size: 1.45rem;
            letter-spacing: 0.04em;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(180px, 1fr));
            gap: 16px;
        }

        .field-group {
            display: grid;
            gap: 10px;
        }

        .field-group label {
            color: rgba(255, 255, 255, 0.84);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-size: 0.85rem;
        }

        .field-group input {
            width: 100%;
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
            font-size: 0.95rem;
        }

        .field-group input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.18);
            border-color: rgba(243, 230, 206, 0.5);
            box-shadow: 0 0 0 6px rgba(243, 230, 206, 0.1);
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
            color: #000;
        }

        .location-select {
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: rgba(255, 255, 255, 0.08);
            color: #000;
            padding: 10px 14px;
            font-size: 0.95rem;
            min-width: 90px;
        }

        .location-select:focus {
            outline: none;
            border-color: rgba(243, 230, 206, 0.6);
            background: rgba(255, 255, 255, 0.14);
        }

        .filter-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: center;
            justify-content: space-between;
            margin-top: 20px;
        }

        .selected-range {
            color: rgba(243, 230, 206, 0.95);
            font-size: 0.98rem;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .action-alert {
            display: none;
            padding: 16px 18px;
            border-radius: 18px;
            background: rgba(235, 94, 94, 0.18);
            border: 1px solid rgba(235, 94, 94, 0.34);
            color: #ffb0b0;
            font-weight: 600;
            margin-top: 4px;
        }

        .action-alert.active {
            display: block;
        }

        .room-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(280px, 1fr));
            gap: 24px;
            margin-top: 32px;
        }

        .room-card {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 28px;
            overflow: hidden;
            display: grid;
            grid-template-rows: auto 1fr auto;
            box-shadow: 0 18px 50px rgba(0, 0, 0, 0.18);
            transition: transform 0.25s ease, background 0.25s ease;
        }

        .room-card:hover {
            transform: translateY(-6px);
            background: rgba(255, 255, 255, 0.12);
        }

        .room-card img {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }

        .room-card-content {
            padding: 24px;
            display: grid;
            gap: 16px;
        }

        .room-card-content h2 {
            margin: 0;
            color: #f5f1e9;
            font-size: 1.4rem;
        }

        .room-price {
            color: #f7f4ee;
            font-weight: 700;
        }

        .room-description {
            color: rgba(247, 244, 238, 0.85);
            line-height: 1.75;
        }

        .book-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 24px;
            border-radius: 999px;
            background: rgba(243, 230, 206, 0.18);
            border: 1px solid rgba(243, 230, 206, 0.28);
            color: #f9f6ef;
            text-decoration: none;
            font-weight: 700;
            transition: transform 0.25s ease, background 0.25s ease;
            gap: 10px;
        }

        .book-btn:hover {
            background: rgba(243, 230, 206, 0.28);
            transform: translateY(-2px);
        }

        .book-btn i,
        .button i {
            display: inline-flex;
            align-items: center;
        }

        .button {
            gap: 12px !important;
        }

        .filter-actions button {
            margin-bottom: 12px;
        }

        .loader-overlay {
            position: fixed;
            inset: 0;
            background: rgba(9, 9, 9, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.25s ease, visibility 0.25s ease;
        }

        .loader-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .spinner {
            width: 90px;
            height: 90px;
            border: 10px solid rgba(255, 255, 255, 0.16);
            border-top: 10px solid #f3e6ce;
            border-radius: 50%;
            animation: spin 1.1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 980px) {
            .room-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 720px) {
            .room-header,
            .room-main {
                padding-left: 24px;
                padding-right: 24px;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }

            .room-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="landing-page room-page">
        <div class="hero-overlay"></div>

        <header class="room-header">
            <div class="room-title-bar"><i class="fas fa-search"></i> Search Rooms</div>
            <div class="location-selector">
                <label for="location-select-room">Location</label>
                <select id="location-select-room" class="location-select" onchange="changeLocation()">
                    <option value="JB" <?php echo $location === 'JB' ? 'selected' : ''; ?>>JB</option>
                    <option value="KL" <?php echo $location === 'KL' ? 'selected' : ''; ?>>KL</option>
                </select>
            </div>
            <div>
                <a href="customer_home.php" class="button secondary-button"><i class="fas fa-arrow-left"></i> Dashboard</a>
                <a href="index.php" class="button primary-button"><i class="fas fa-home"></i> Home</a>
            </div>
        </header>

        <main class="room-main">
            <div class="filter-card">
                <h2>Choose your dates</h2>
                <p style="margin:0;color:rgba(245,241,233,0.82);">Pick a check-in and check-out duration, then click filter to refresh the room list.</p>
                <form id="filterForm" method="GET">
                    <div class="filter-grid">
                        <div class="field-group">
                            <label for="checkin">Check-in Date</label>
                            <input type="date" id="checkin" name="checkin" value="<?php echo htmlspecialchars($checkin); ?>" required>
                        </div>
                        <div class="field-group">
                            <label for="checkout">Check-out Date</label>
                            <input type="date" id="checkout" name="checkout" value="<?php echo htmlspecialchars($checkout); ?>" required>
                        </div>
                    </div>
                    <div class="filter-actions">
                        <div class="selected-range">
                            <?php if($filterActive): ?>
                                <i class="fas fa-calendar-day"></i>
                                Showing rooms for <?php echo htmlspecialchars($checkin); ?> → <?php echo htmlspecialchars($checkout); ?>
                            <?php else: ?>
                                <i class="fas fa-calendar"></i>
                                No duration selected yet.
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="button primary-button">Filter</button>
                    </div>
                </form>
                <div class="action-alert" id="dateAlert">Please choose a check-in and check-out date before booking.</div>
            </div>

            <?php if(mysqli_num_rows($res) == 0): ?>
                <section class="info-panel" style="margin-top: 32px;">
                    <div class="info-card">
                        <span class="info-title">No rooms ready yet</span>
                        <p>There are currently no rooms available. Please check back later or contact support for assistance.</p>
                        <a href="index.php" class="button secondary-button" style="margin-top: 16px; display: inline-flex;">Back to Home</a>
                    </div>
                </section>
            <?php else: ?>
                <div class="room-grid">
                    <?php while($row = mysqli_fetch_assoc($res)): ?>
                        <?php
                        $status = scholarin_room_status($conn, $row, $checkin, $checkout);
                        $roomLink = 'book.php?id=' . urlencode($row['id']);
                        if($filterActive){
                            $roomLink .= '&checkin=' . urlencode($checkin) . '&checkout=' . urlencode($checkout) . '&auto=1';
                        }
                        $isAvailable = $status['availability'] === 'Available';
                        if($filterActive && !$isAvailable){
                            continue;
                        }
                        ?>
                        <article class="room-card">
                            <?php if(!empty($row['image'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
                            <?php endif; ?>
                            <div class="room-card-content">
                                <h2><?php echo htmlspecialchars($row['title']); ?></h2>
                                <div class="room-price">RM <?php echo htmlspecialchars($row['price']); ?></div>
                                <div style="display:inline-flex;align-items:center;gap:8px;padding:10px 12px;border-radius:14px;background:rgba(255,255,255,0.10);border:1px solid rgba(243,230,206,0.18);color:#fff; font-size:0.95rem; font-weight:700; box-shadow:0 8px 18px rgba(0,0,0,0.12);">
                                    <i class="fas fa-layer-group" style="color:#f3e6ce;"></i>
                                    <span>
                                        <?php if($filterActive): ?>
                                            Available units: <strong style="color:#f3e6ce;"><?php echo $status['available_units']; ?>/<?php echo scholarin_room_capacity($row); ?></strong> for your dates
                                        <?php else: ?>
                                            Select dates first to confirm real availability
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <?php if(!empty($row['description'])): ?>
                                    <p class="room-description"><?php echo htmlspecialchars($row['description']); ?></p>
                                <?php endif; ?>
                                <?php if(!$isAvailable): ?>
                                    <span class="room-unavailable" style="display:inline-block;margin-bottom:12px;padding:6px 12px;border-radius:999px;background:rgba(235,94,94,0.16);color:#ffb0b0;font-weight:700;">Unavailable</span>
                                <?php endif; ?>
                                <?php if($isAvailable): ?>
                                    <a href="<?php echo $roomLink; ?>" class="book-btn"><i class="fas fa-bed"></i> Book Now</a>
                                <?php else: ?>
                                    <span class="book-btn disabled" style="opacity:0.5;cursor:not-allowed;"><i class="fas fa-ban"></i> Unavailable</span>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>

            <div class="booking-footer" style="margin-top:32px;text-align:center;">
                <a href="customer_home.php" class="button secondary-button">Back to Dashboard</a>
            </div>
        </main>
    </div>

    <div class="loader-overlay" id="loaderOverlay">
        <div class="spinner"></div>
    </div>

    <script>
        var filterActive = <?php echo $filterActive ? 'true' : 'false'; ?>;
        var dateAlert = document.getElementById('dateAlert');
        var currentLocation = '<?php echo $location; ?>';

        function changeLocation() {
            const newLocation = document.getElementById('location-select-room').value;
            if(newLocation === currentLocation) return;

            currentLocation = newLocation;

            const checkin = document.getElementById('checkin').value;
            const checkout = document.getElementById('checkout').value;
            const url = new URL(window.location.href);
            url.searchParams.set('loc', newLocation);
            if(checkin) url.searchParams.set('checkin', checkin);
            if(checkout) url.searchParams.set('checkout', checkout);
            window.history.replaceState({}, '', url.toString());

            // Change background image
            const pageDiv = document.querySelector('.landing-page');
            const imageUrl = newLocation === 'KL' ? 'SCHOLAR INN KL.webp' : 'Scholar inn image out side.jpg';
            pageDiv.style.backgroundImage = `url('${imageUrl}')`;

            // Save to database
            fetch('save_location.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'location=' + encodeURIComponent(newLocation)
            });

            // Show loading spinner
            showLoadingSpinner(true);

            // Load rooms for the new location
            loadRoomsByLocation(newLocation);
        }

        function loadRoomsByLocation(location) {
            const checkin = document.getElementById('checkin').value;
            const checkout = document.getElementById('checkout').value;
            
            let url = 'get_rooms_by_location.php?location=' + encodeURIComponent(location);
            if(checkin) url += '&checkin=' + encodeURIComponent(checkin);
            if(checkout) url += '&checkout=' + encodeURIComponent(checkout);

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    showLoadingSpinner(false);
                    updateRoomGrid(data, location, checkin, checkout);
                })
                .catch(error => {
                    console.error('Error loading rooms:', error);
                    showLoadingSpinner(false);
                });
        }

        function updateRoomGrid(data, location, checkin, checkout) {
            const roomGrid = document.querySelector('.room-grid');
            const infoPanel = document.querySelector('.info-panel');

            if(!data.success || data.rooms.length === 0) {
                if(roomGrid) roomGrid.remove();
                
                let infoPanelHtml = `
                    <section class="info-panel" style="margin-top: 32px;">
                        <div class="info-card">
                            <span class="info-title">No rooms ready yet</span>
                            <p>There are currently no rooms available in ${location}. Please check back later or try another location.</p>
                            <a href="index.php" class="button secondary-button" style="margin-top: 16px; display: inline-flex;">Back to Home</a>
                        </div>
                    </section>
                `;

                if(infoPanel) {
                    infoPanel.innerHTML = infoPanelHtml;
                } else {
                    const filterCard = document.querySelector('.filter-card');
                    const newPanel = document.createElement('section');
                    newPanel.innerHTML = infoPanelHtml;
                    filterCard.parentNode.insertBefore(newPanel, filterCard.nextSibling);
                }
                return;
            }

            // Build room grid HTML
            let gridHtml = '<div class="room-grid">';
            data.rooms.forEach(room => {
                let roomLink = 'book.php?id=' + encodeURIComponent(room.id);
                if(checkin && checkout) {
                    roomLink += '&checkin=' + encodeURIComponent(checkin) + '&checkout=' + encodeURIComponent(checkout) + '&auto=1';
                }

                gridHtml += `
                    <article class="room-card">
                        ${room.image ? `<img src="uploads/${room.image}" alt="${room.title}">` : ''}
                        <div class="room-card-content">
                            <h2>${room.title}</h2>
                            <div class="room-price">RM ${room.price}</div>
                            ${room.description ? `<p class="room-description">${room.description}</p>` : ''}
                            ${room.availability !== 'Available' ? '<span class="room-unavailable" style="display:inline-block;margin-bottom:12px;padding:6px 12px;border-radius:999px;background:rgba(235,94,94,0.16);color:#ffb0b0;font-weight:700;">Unavailable</span>' : ''}
                    ${room.availability === 'Available' ? `<a href="${roomLink}" class="book-btn"><i class="fas fa-bed"></i> Book Now</a>` : `<span class="book-btn disabled" style="opacity:0.5;cursor:not-allowed;"><i class="fas fa-ban"></i> Unavailable</span>`}
                        </div>
                    </article>
                `;
            });
            gridHtml += '</div>';

            if(roomGrid) {
                roomGrid.outerHTML = gridHtml;
            } else {
                const filterCard = document.querySelector('.filter-card');
                const newGrid = document.createElement('div');
                newGrid.innerHTML = gridHtml;
                filterCard.parentNode.insertBefore(newGrid.firstChild, filterCard.nextSibling);
            }

            if(infoPanel) infoPanel.remove();
        }

        function showLoadingSpinner(show) {
            const loader = document.getElementById('loaderOverlay');
            if(show) {
                loader.classList.add('active');
            } else {
                loader.classList.remove('active');
            }
        }
        
        function applyLocation() {
            const location = '<?php echo $location; ?>';
            const imageUrl = location === 'KL' ? 'SCHOLAR INN KL.webp' : 'Scholar inn image out side.jpg';
            const pageDiv = document.querySelector('.landing-page');
            pageDiv.style.backgroundImage = `url('${imageUrl}')`;
        }

        document.addEventListener('DOMContentLoaded', function() {
            applyLocation();
        });

        document.getElementById('filterForm').addEventListener('submit', function(event) {
            event.preventDefault();
            var checkin = document.getElementById('checkin').value;
            var checkout = document.getElementById('checkout').value;
            if (!checkin || !checkout) return;

            var loader = document.getElementById('loaderOverlay');
            loader.classList.add('active');
            
            const location = document.getElementById('location-select-room').value;

            setTimeout(function() {
                var params = new URLSearchParams();
                params.set('checkin', checkin);
                params.set('checkout', checkout);
                params.set('loc', location);
                window.location.search = params.toString();
            }, 600);
        });

        document.querySelectorAll('.book-btn').forEach(function(button) {
            button.addEventListener('click', function(event) {
                if (!filterActive) {
                    event.preventDefault();
                    dateAlert.classList.add('active');
                    setTimeout(function() {
                        dateAlert.classList.remove('active');
                    }, 3200);
                }
            });
        });
    </script>
</body>
</html>