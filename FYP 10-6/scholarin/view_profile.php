<?php
session_start();
if(!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['customer','staff'])){
    header("Location: login.php");
    exit();
}

include "db.php";

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle edit profile
if(isset($_POST['update'])){
    $name = $_POST['name'];
    $email = $_POST['email'];

    mysqli_query($conn,
    "UPDATE users SET name='$name', email='$email' WHERE id='$user_id'");

    $message = 'Profile updated successfully!';
}

// Handle change password
if(isset($_POST['change'])){
    $old = $_POST['old_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if($new !== $confirm){
        $error = 'New password and confirmation do not match';
    } elseif($new === $old) {
        $error = 'New password cannot be the same as the old password';
    } else {
        // check old password
        $check = mysqli_query($conn,
            "SELECT * FROM users WHERE id='$user_id' AND password='$old'");

        if(mysqli_num_rows($check) > 0){
            mysqli_query($conn,
            "UPDATE users SET password='$new' WHERE id='$user_id'");

            $message = 'Password changed successfully!';
        } else {
            $error = 'Old password incorrect';
        }
    }
}

$result = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($result);

$dashboard_link = $_SESSION['role'] == 'staff' ? 'staff_home.php' : 'customer_home.php';
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'view';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="LandingStyle.css">
    <style>
        .profile-page {
            position: relative;
            min-height: 100vh;
            background-image: url('Scholar inn image out side.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            flex-direction: column;
        }

        .profile-header {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 30px 40px;
        }

        .profile-title {
            font-size: 1.4rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-weight: 700;
            color: #f5f1e9;
        }

        .profile-content {
            position: relative;
            z-index: 1;
            flex: 1;
            padding: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .profile-card {
            width: 100%;
            max-width: 700px;
            background: rgba(14, 17, 32, 0.88);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 32px;
            backdrop-filter: blur(20px);
            padding: 44px;
            box-shadow: 0 35px 90px rgba(0, 0, 0, 0.45);
        }

        .profile-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 32px;
            pointer-events: none;
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.08), transparent 60%);
        }

        .profile-card > * {
            position: relative;
            z-index: 1;
        }

        .profile-card h1 {
            margin: 0 0 32px;
            font-size: 2.2rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #ffffff;
            text-align: center;
        }

        .tab-navigation {
            display: flex;
            gap: 12px;
            margin-bottom: 32px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .tab-btn {
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: rgba(255, 255, 255, 0.8);
            cursor: pointer;
            border-radius: 999px;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.25s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .tab-btn:hover {
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
            transform: translateY(-2px);
        }

        .tab-btn.active {
            background: rgba(243, 230, 206, 0.25);
            border-color: rgba(243, 230, 206, 0.5);
            color: #f9f6ef;
            box-shadow: 0 10px 25px rgba(243, 230, 206, 0.15);
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 600;
            letter-spacing: 0.06em;
            font-size: 0.95rem;
        }

        .form-group input {
            width: 100%;
            padding: 14px 18px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            font-size: 0.95rem;
            transition: all 0.25s ease;
        }

        .form-group input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.14);
            border-color: rgba(243, 230, 206, 0.5);
            box-shadow: 0 0 0 3px rgba(243, 230, 206, 0.15);
        }

        .message {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-weight: 600;
            border: 1px solid;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.success {
            background: rgba(76, 175, 80, 0.15);
            color: #90ee90;
            border-color: rgba(76, 175, 80, 0.4);
        }

        .message.error {
            background: rgba(244, 67, 54, 0.15);
            color: #ff9999;
            border-color: rgba(244, 67, 54, 0.4);
        }

        .submit-btn {
            width: 100%;
            padding: 14px 24px;
            background: linear-gradient(135deg, rgba(74, 95, 176, 0.95), rgba(111, 140, 236, 0.98));
            color: #fff;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            cursor: pointer;
            transition: all 0.25s ease;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(111, 140, 236, 0.35);
        }

        .submit-btn:active {
            transform: translateY(-1px);
        }

        .info-display {
            background: rgba(255, 255, 255, 0.06);
            padding: 28px;
            border-radius: 18px;
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .info-item {
            margin-bottom: 24px;
            padding-bottom: 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .info-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .info-label {
            color: rgba(255, 255, 255, 0.65);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .info-value {
            color: #f9f6ef;
            font-weight: 600;
            font-size: 1.15rem;
            letter-spacing: 0.03em;
        }

        .profile-footer {
            position: relative;
            z-index: 1;
            padding: 0 40px 40px;
            text-align: center;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 28px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: rgba(255, 255, 255, 0.85);
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.25s ease;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .profile-content {
                padding: 20px;
            }

            .profile-card {
                padding: 32px 24px;
            }

            .profile-card h1 {
                font-size: 1.8rem;
            }

            .tab-navigation {
                gap: 8px;
            }

            .tab-btn {
                padding: 10px 16px;
                font-size: 0.85rem;
            }

            .profile-header {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

    <div class="profile-page">
        <div class="hero-overlay"></div>

        <header class="profile-header">
            <div class="profile-title">Scholar Inn - Profile</div>
            <a href="<?php echo $dashboard_link; ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </header>

        <div class="profile-content">
            <div class="profile-card">
                <h1><i class="fas fa-user-circle"></i> My Profile</h1>

                <div class="tab-navigation">
                    <a href="?tab=view" class="tab-btn <?php echo $tab == 'view' ? 'active' : ''; ?>">
                        <i class="fas fa-eye"></i> View
                    </a>
                    <a href="?tab=edit" class="tab-btn <?php echo $tab == 'edit' ? 'active' : ''; ?>">
                        <i class="fas fa-pen"></i> Edit
                    </a>
                    <a href="?tab=change-password" class="tab-btn <?php echo $tab == 'change-password' ? 'active' : ''; ?>">
                        <i class="fas fa-lock"></i> Password
                    </a>
                </div>

                <!-- View Profile Tab -->
                <div class="tab-content <?php echo $tab == 'view' ? 'active' : ''; ?>">
                    <div class="info-display">
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-user"></i> Full Name</div>
                            <div class="info-value"><?php echo htmlspecialchars($user['name']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-envelope"></i> Email Address</div>
                            <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-id-card"></i> IC Number</div>
                            <div class="info-value"><?php echo htmlspecialchars($user['ic']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-badge"></i> Account Type</div>
                            <div class="info-value"><?php echo ucfirst(htmlspecialchars($user['role'])); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Edit Profile Tab -->
                <div class="tab-content <?php echo $tab == 'edit' ? 'active' : ''; ?>">
                    <?php if($message): ?>
                        <div class="message success"><i class="fas fa-check-circle"></i> <?php echo $message; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <p style="color:#f5f1e9; margin-bottom: 18px; font-size:0.95rem; line-height:1.5;">
                            IC changes are restricted. Please contact staff if you need to update your IC number.
                        </p>
                        <div class="form-group">
                            <label for="name"><i class="fas fa-user"></i> Full Name</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <button type="submit" name="update" class="submit-btn">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </form>
                </div>

                <!-- Change Password Tab -->
                <div class="tab-content <?php echo $tab == 'change-password' ? 'active' : ''; ?>">
                    <?php if($message): ?>
                        <div class="message success"><i class="fas fa-check-circle"></i> <?php echo $message; ?></div>
                    <?php endif; ?>
                    <?php if($error): ?>
                        <div class="message error"><i class="fas fa-times-circle"></i> <?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label for="old_password"><i class="fas fa-lock"></i> Current Password</label>
                            <input type="password" id="old_password" name="old_password" required>
                        </div>

                        <div class="form-group">
                            <label for="new_password"><i class="fas fa-key"></i> New Password</label>
                            <input type="password" id="new_password" name="new_password" required>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password"><i class="fas fa-key"></i> Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>

                        <button type="submit" name="change" class="submit-btn">
                            <i class="fas fa-lock"></i> Update Password
                        </button>
                    </form>
                </div>

            </div>
        </div>

        <div class="profile-footer">
            <a href="<?php echo $dashboard_link; ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

</body>

</html>