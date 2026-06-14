<?php
session_start();
if($_SESSION['role'] != 'customer'){
    header("Location: login.php");
    exit();
}

include "db.php";

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Get user's location
$userRes = mysqli_query($conn, "SELECT location FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($userRes);
$location = $user && $user['location'] ? $user['location'] : 'JB';

if(isset($_POST['submit'])){
    $room_id = $_POST['room_id'];
    $rating = $_POST['rating'];
    $cleanliness = $_POST['cleanliness'];
    $services = $_POST['services'];
    $staff = $_POST['staff'];
    $review = trim($_POST['review']);

    if($room_id && $rating && $cleanliness && $services && $staff && $review){
        mysqli_query($conn,
        "INSERT INTO reviews (user_id, room_id, rating, cleanliness, services, staff, review)
        VALUES ('$user_id', '$room_id', '$rating', '$cleanliness', '$services', '$staff', '$review')");
        $message = 'Review submitted successfully';
    } else {
        $error = 'Please complete all fields before submitting.';
    }
}

$rooms = mysqli_query($conn, "SELECT * FROM rooms WHERE location='$location'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rating & Review</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="LandingStyle.css">
    <style>
        .review-page {
            min-height: 100vh;
        }

        .review-header {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 30px 40px;
        }

        .review-title {
            font-size: 1.6rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            font-weight: 700;
            color: #f5f1e9;
        }

        .review-main {
            position: relative;
            z-index: 1;
            padding: 72px 40px 40px;
            max-width: 980px;
            margin: 0 auto;
            width: 100%;
        }

        .review-intro {
            max-width: 650px;
        }

        .review-intro h1 {
            margin: 0;
            font-size: clamp(2.4rem, 4vw, 3.6rem);
            line-height: 1.02;
            color: #ffffff;
            max-width: 12ch;
        }

        .review-intro p {
            margin-top: 24px;
            max-width: 620px;
            color: rgba(245, 241, 233, 0.88);
            line-height: 1.8;
            font-size: 1.05rem;
        }

        .review-panel {
            margin-top: 40px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 28px;
            padding: 32px;
            box-shadow: 0 18px 50px rgba(0, 0, 0, 0.18);
        }

        .review-form {
            display: grid;
            gap: 20px;
        }

        .form-group {
            display: grid;
            gap: 10px;
        }

        .form-group label {
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
            letter-spacing: 0.05em;
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        select,
        textarea {
            width: 100%;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.92);
            color: #111;
            padding: 14px 16px;
            font-size: 0.95rem;
            transition: all 0.25s ease;
        }

        select option {
            color: #111;
            background: #fff;
        }

        select:focus,
        textarea:focus {
            outline: none;
            border-color: rgba(243, 230, 206, 0.8);
            box-shadow: 0 0 0 3px rgba(243, 230, 206, 0.18);
            background: #fff;
        }

        textarea {
            min-height: 180px;
            resize: vertical;
        }

        .submit-btn {
            width: 100%;
            padding: 16px 24px;
            background: linear-gradient(135deg, rgba(74, 95, 176, 0.95), rgba(111, 140, 236, 0.98));
            color: #fff;
            border: none;
            border-radius: 16px;
            font-weight: 700;
            letter-spacing: 0.08em;
            cursor: pointer;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(111, 140, 236, 0.35);
        }

        .message {
            padding: 16px 20px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            border: 1px solid;
        }

        .message.success {
            background: rgba(86, 181, 102, 0.16);
            border-color: rgba(86, 181, 102, 0.35);
            color: #b8f3b8;
        }

        .message.error {
            background: rgba(235, 94, 94, 0.14);
            border-color: rgba(235, 94, 94, 0.35);
            color: #ffb0b0;
        }

        .review-footer {
            margin-top: 28px;
            text-align: center;
        }

        .review-footer .button {
            padding: 16px 32px;
            font-size: 0.95rem;
        }

        @media (max-width: 820px) {
            .review-main {
                padding-left: 24px;
                padding-right: 24px;
            }
        }

        @media (max-width: 640px) {
            .review-header,
            .review-main {
                padding-left: 18px;
                padding-right: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="landing-page review-page">
        <div class="hero-overlay"></div>

        <header class="review-header">
            <div class="review-title"><i class="fas fa-star"></i> Rating & Review</div>
            <div>
                <a href="customer_home.php" class="button secondary-button"><i class="fas fa-arrow-left"></i> Dashboard</a>
                <a href="index.php" class="button primary-button"><i class="fas fa-home"></i> Home</a>
            </div>
        </header>

        <main class="review-main">
            <div class="review-intro">
                <p class="eyebrow">Share your stay</p>
                <h1>Leave a review for your Scholar Inn room.</h1>
                <p class="hero-text">Your feedback helps us improve service, refine comfort, and make every guest feel welcome. Submit room ratings and comments with ease.</p>
            </div>

            <section class="review-panel">
                <?php if($message): ?>
                    <div class="message success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="message error"><i class="fas fa-times-circle"></i> <?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" class="review-form">
                    <div class="form-group">
                        <label for="room_id"><i class="fas fa-door-open"></i> Select Room</label>
                        <select name="room_id" id="room_id" required>
                            <option value="">-- Select a Room --</option>
                            <?php while($room = mysqli_fetch_assoc($rooms)): ?>
                                <option value="<?php echo htmlspecialchars($room['id']); ?>"><?php echo htmlspecialchars($room['title']); ?> — RM<?php echo htmlspecialchars($room['price']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="rating"><i class="fas fa-star"></i> Overall Rating</label>
                        <select name="rating" id="rating" required>
                            <option value="">-- Select Rating --</option>
                            <option value="1">1 — Poor</option>
                            <option value="2">2 — Fair</option>
                            <option value="3">3 — Good</option>
                            <option value="4">4 — Very Good</option>
                            <option value="5">5 — Excellent</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="cleanliness"><i class="fas fa-broom"></i> Cleanliness Rating</label>
                        <select name="cleanliness" id="cleanliness" required>
                            <option value="">-- Select Rating --</option>
                            <option value="1">1 — Poor</option>
                            <option value="2">2 — Fair</option>
                            <option value="3">3 — Good</option>
                            <option value="4">4 — Very Good</option>
                            <option value="5">5 — Excellent</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="services"><i class="fas fa-concierge-bell"></i> Services Rating</label>
                        <select name="services" id="services" required>
                            <option value="">-- Select Rating --</option>
                            <option value="1">1 — Poor</option>
                            <option value="2">2 — Fair</option>
                            <option value="3">3 — Good</option>
                            <option value="4">4 — Very Good</option>
                            <option value="5">5 — Excellent</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="staff"><i class="fas fa-users"></i> Staff Rating</label>
                        <select name="staff" id="staff" required>
                            <option value="">-- Select Rating --</option>
                            <option value="1">1 — Poor</option>
                            <option value="2">2 — Fair</option>
                            <option value="3">3 — Good</option>
                            <option value="4">4 — Very Good</option>
                            <option value="5">5 — Excellent</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="review"><i class="fas fa-comment-dots"></i> Review</label>
                        <textarea id="review" name="review" placeholder="Tell us what you liked about your stay..." required></textarea>
                    </div>

                    <button type="submit" name="submit" class="submit-btn"><i class="fas fa-paper-plane"></i> Submit Review</button>
                </form>
            </section>

            <div class="review-footer">
                <a href="customer_home.php" class="button secondary-button">Back to Dashboard</a>
            </div>
        </main>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const location = '<?php echo $location; ?>';
            const imageUrl = location === 'KL' ? 'SCHOLAR INN KL.webp' : 'Scholar inn image out side.jpg';
            const pageDiv = document.querySelector('.landing-page');
            pageDiv.style.backgroundImage = `url('${imageUrl}')`;
        });
    </script>
</body>
</html>
