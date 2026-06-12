<?php
session_start();
if($_SESSION['role'] != 'customer'){
    header("Location: login.php");
    exit();
}

include "db.php";

$user_id = $_SESSION['user_id'];
$flashMessage = '';
$flashType = '';
if(isset($_SESSION['flash_message'])){
    $flashMessage = $_SESSION['flash_message'];
    $flashType = isset($_SESSION['flash_type']) ? $_SESSION['flash_type'] : 'success';
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
}

// Get user's location
$userRes = mysqli_query($conn, "SELECT location FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($userRes);
$location = $user && $user['location'] ? $user['location'] : 'JB';

$res = mysqli_query($conn, "SELECT * FROM bookings WHERE user_id='$user_id'");
$bookings = [];
while($row = mysqli_fetch_assoc($res)){
    $bookings[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Status</title>
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
            padding: 80px 40px 40px;
            max-width: 1100px;
            margin: 0 auto;
            width: 100%;
        }

        .status-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(250px, 1fr));
            gap: 24px;
        }

        .booking-card {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 28px;
            padding: 28px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            box-shadow: 0 18px 50px rgba(0, 0, 0, 0.18);
            transition: transform 0.25s ease, background 0.25s ease;
        }

        .booking-card:hover {
            transform: translateY(-6px);
            background: rgba(255, 255, 255, 0.12);
        }

        .booking-card h2 {
            margin: 0;
            color: #f9f6ef;
            font-size: 1.2rem;
            letter-spacing: 0.04em;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 14px;
            border-radius: 999px;
            font-weight: 700;
            letter-spacing: 0.06em;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        .status-approved { background: rgba(86, 181, 102, 0.18); color: #b8f3b8; border: 1px solid rgba(86, 181, 102, 0.35); }
        .status-pending { background: rgba(243, 169, 18, 0.18); color: #ffe9a0; border: 1px solid rgba(243, 169, 18, 0.35); }
        .status-rejected { background: rgba(235, 94, 94, 0.18); color: #ffb0b0; border: 1px solid rgba(235, 94, 94, 0.35); }

        .booking-meta {
            display: grid;
            gap: 10px;
            color: rgba(247, 244, 238, 0.92);
            line-height: 1.8;
            font-size: 0.98rem;
        }

        .booking-meta span {
            display: block;
        }

        .booking-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: auto;
        }

        .booking-actions a,
        .booking-actions span {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 999px;
            font-size: 0.9rem;
            text-decoration: none;
        }

        .action-button {
            background: rgba(255, 255, 255, 0.16);
            color: #f7f4ee;
            border: 1px solid rgba(255, 255, 255, 0.12);
            transition: transform 0.25s ease, background 0.25s ease;
        }

        .action-button:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.24);
        }

        .payment-done { background: rgba(86, 181, 102, 0.18); color: #b8f3b8; }
        .receipt-done { background: rgba(52, 152, 219, 0.18); color: #b0dfff; }

        .receipt-warning {
            color: #ff6b6b;
        }

        .booking-empty {
            text-align: center;
            color: rgba(247, 244, 238, 0.9);
            font-size: 1.05rem;
            padding: 40px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 24px;
        }

        .flash-banner {
            border-radius: 22px;
            padding: 18px 22px;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 28px;
            border: 1px solid transparent;
            font-weight: 600;
        }

        .flash-banner.success {
            background: rgba(86, 181, 102, 0.16);
            color: #c8f2c8;
            border-color: rgba(86, 181, 102, 0.4);
        }

        .flash-banner.error {
            background: rgba(235, 94, 94, 0.16);
            color: #ffb0b0;
            border-color: rgba(235, 94, 94, 0.4);
        }

        .booking-footer {
            margin-top: 40px;
            text-align: center;
        }

        .booking-footer .button {
            padding: 16px 32px;
            font-size: 0.95rem;
            gap: 12px !important;
        }

        .button i {
            display: inline-flex;
            align-items: center;
        }

        @media (max-width: 900px) {
            .status-grid {
                grid-template-columns: 1fr;
            }

            .booking-header,
            .booking-main {
                padding-left: 24px;
                padding-right: 24px;
            }
        }
    </style>
</head>

<body>
    <div class="landing-page booking-page">
        <div class="hero-overlay"></div>

        <header class="booking-header">
            <div class="booking-title">Booking Status</div>
            <a href="customer_home.php" class="button login-button"><i class="fas fa-arrow-left"></i>  Back to Dashboard</a>
        </header>

        <main class="booking-main">
            <p class="eyebrow">Stay on top of your reservation progress</p>
            <h1>Track your current bookings and payment status.</h1>
            <p class="hero-text">Manage deposits, full payments, cancellations, and receipts from one elegant view.</p>

            <?php if(!empty($flashMessage)): ?>
                <div class="flash-banner <?php echo htmlspecialchars($flashType); ?>">
                    <i class="fas fa-info-circle"></i>
                    <?php echo htmlspecialchars($flashMessage); ?>
                </div>
            <?php endif; ?>

            <section class="info-panel booking-panel">
                <?php if(empty($bookings)): ?>
                    <div class="booking-empty">
                        <p><strong>No bookings found yet.</strong></p>
                        <p>Start by reserving a room and your booking status will appear here.</p>
                        <a href="view_rooms.php" class="button secondary-button">Search Rooms</a>
                    </div>
                <?php else: ?>
                    <div class="status-grid">
                        <?php foreach($bookings as $booking): ?>
                            <?php
                                $statusClass = 'status-pending';
                                if($booking['status'] == 'Approved') {
                                    $statusClass = 'status-approved';
                                } elseif($booking['status'] == 'Rejected') {
                                    $statusClass = 'status-rejected';
                                }
                            ?>
                            <article class="booking-card">
                                <h2>Booking #<?php echo htmlspecialchars($booking['id']); ?></h2>
                                <div class="booking-meta">
                                    <span><strong>Status:</strong> <span class="status-pill <?php echo $statusClass; ?>"><?php echo htmlspecialchars($booking['status']); ?></span></span>
                                    <span><strong>Deposit Status:</strong> <?php echo htmlspecialchars($booking['payment']); ?></span>
                                    <?php if($booking['status'] == 'Approved'): ?>
                                        <span><strong>Full Payment:</strong> <?php echo htmlspecialchars($booking['full_payment']); ?></span>
                                        <?php if(!empty($booking['receipt'])): ?>
                                            <span><strong>Receipt:</strong> Uploaded</span>
                                        <?php elseif(strpos($booking['full_payment'], 'Paid') !== false): ?>
                                            <span class="receipt-warning"><strong>Receipt:</strong> Not uploaded yet. Please upload your receipt for staff final confirmation.</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>

                                <div class="booking-actions">
                                    <?php if($booking['status'] == 'Approved' && $booking['full_payment'] == 'Unpaid'): ?>
                                        <a href="full_payment.php?id=<?php echo urlencode($booking['id']); ?>" class="action-button"><i class="fas fa-money-bill-wave"></i> Pay Full Amount</a>
                                    <?php elseif($booking['status'] == 'Approved' && $booking['full_payment'] != 'Unpaid'): ?>
                                        <span class="payment-done"><i class="fas fa-check-circle"></i> Full Payment Done</span>
                                        <?php if(empty($booking['receipt'])): ?>
                                            <a href="full_payment.php?id=<?php echo urlencode($booking['id']); ?>" class="action-button"><i class="fas fa-upload"></i> Upload Receipt</a>
                                        <?php else: ?>
                                            <span class="receipt-done"><i class="fas fa-file-invoice"></i> Receipt Uploaded</span>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if($booking['payment'] != 'Unpaid'): ?>
                                        <span class="payment-done"><i class="fas fa-wallet"></i> Deposit Paid</span>
                                    <?php elseif($booking['status'] != 'Rejected'): ?>
                                        <a href="payment.php?id=<?php echo urlencode($booking['id']); ?>" class="action-button"><i class="fas fa-credit-card"></i> Pay Deposit</a>
                                    <?php endif; ?>

                                    <a href="cancel.php?id=<?php echo urlencode($booking['id']); ?>" class="action-button"><i class="fas fa-ban"></i> Cancel Booking</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <div class="booking-footer">
                <a href="index.php" class="button secondary-button">Back to Home</a>
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