<?php
session_start();
include "db.php";
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$section = isset($_GET['section']) ? $_GET['section'] : 'overview';
$message = '';
$error = '';

function escape($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Ensure a simple activities table exists and provide a helper to log actions
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS system_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    user_name VARCHAR(255) NULL,
    role VARCHAR(50) NULL,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

function log_activity($conn, $user_id = null, $user_name = null, $role = null, $action = '', $details = '') {
    $uid = $user_id ? (int)$user_id : 'NULL';
    $uname = mysqli_real_escape_string($conn, $user_name ?: '');
    $role = mysqli_real_escape_string($conn, $role ?: '');
    $action = mysqli_real_escape_string($conn, $action ?: '');
    $details = mysqli_real_escape_string($conn, $details ?: '');
    mysqli_query($conn, "INSERT INTO system_activities (user_id,user_name,role,action,details) VALUES ($uid,'$uname','$role','$action','$details')");
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if($action === 'add_account') {
        $name = mysqli_real_escape_string($conn, trim($_POST['name']));
        $email = mysqli_real_escape_string($conn, trim($_POST['email']));
        $password = mysqli_real_escape_string($conn, trim($_POST['password']));
        $ic = mysqli_real_escape_string($conn, trim($_POST['ic']));
        $role = isset($_POST['role']) && $_POST['role'] === 'staff' ? 'staff' : 'customer';

        if(!$name || !$email || !$password || !$ic) {
            $error = 'Please complete all fields to add account.';
        } else {
            $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
            if(mysqli_num_rows($check) > 0) {
                $error = 'That email is already used. Please choose another.';
            } else {
                mysqli_query($conn, "INSERT INTO users (name,email,password,ic,role) VALUES ('$name','$email','$password','$ic','$role')");
                log_activity($conn, $admin_id, 'Admin', 'admin', 'Created ' . ucfirst($role) . ' account', "Name: $name, Email: $email, IC: $ic");
                $message = ucfirst($role) . ' account created successfully.';
            }
        }
    }

    if($action === 'edit_customer') {
        $user_id = (int)$_POST['user_id'];
        $name = mysqli_real_escape_string($conn, trim($_POST['name']));
        $email = mysqli_real_escape_string($conn, trim($_POST['email']));
        $ic = mysqli_real_escape_string($conn, trim($_POST['ic']));

        if(!$name || !$email || !$ic) {
            $error = 'Please complete all fields to update user.';
        } else {
            mysqli_query($conn, "UPDATE users SET name='$name', email='$email', ic='$ic' WHERE id='$user_id' AND role='customer'");
            $message = 'User details updated successfully.';
        }
    }

    if($action === 'edit_staff') {
        $staff_id = (int)$_POST['staff_id'];
        $name = mysqli_real_escape_string($conn, trim($_POST['name']));
        $email = mysqli_real_escape_string($conn, trim($_POST['email']));
        $ic = mysqli_real_escape_string($conn, trim($_POST['ic']));

        if(!$name || !$email || !$ic) {
            $error = 'Please complete all fields to update staff.';
        } else {
            mysqli_query($conn, "UPDATE users SET name='$name', email='$email', ic='$ic' WHERE id='$staff_id' AND role='staff'");
            $message = 'Staff details updated successfully.';
        }
    }

    if($action === 'update_profile') {
        $name = mysqli_real_escape_string($conn, trim($_POST['name']));
        $email = mysqli_real_escape_string($conn, trim($_POST['email']));

        if(!$name || !$email) {
            $error = 'Please provide both name and email.';
        } else {
            mysqli_query($conn, "UPDATE users SET name='$name', email='$email' WHERE id='$admin_id'");
            log_activity($conn, $admin_id, 'Admin', 'admin', 'Updated own profile', "New name: $name, New email: $email");
            $message = 'Profile updated successfully.';
        }
    }

    if($action === 'change_password') {
        $old = mysqli_real_escape_string($conn, trim($_POST['old_password']));
        $new = mysqli_real_escape_string($conn, trim($_POST['new_password']));
        $confirm = mysqli_real_escape_string($conn, trim($_POST['confirm_password']));

        if(!$old || !$new || !$confirm) {
            $error = 'Please fill in all password fields.';
        } elseif($new !== $confirm) {
            $error = 'New password and confirmation do not match.';
        } else {
            $check = mysqli_query($conn, "SELECT id FROM users WHERE id='$admin_id' AND password='$old'");
            if(mysqli_num_rows($check) === 0) {
                $error = 'Old password is incorrect.';
            } else {
                mysqli_query($conn, "UPDATE users SET password='$new' WHERE id='$admin_id'");
                log_activity($conn, $admin_id, 'Admin', 'admin', 'Changed own password', 'Password reset');
                $message = 'Password changed successfully.';
            }
        }
    }

    if($action === 'recover_password') {
        $name = mysqli_real_escape_string($conn, trim($_POST['recover_name']));
        $email = mysqli_real_escape_string($conn, trim($_POST['recover_email']));

        if(!$name || !$email) {
            $error = 'Please enter both name and email to recover the password.';
        } else {
            $check = mysqli_query($conn, "SELECT password FROM users WHERE name='$name' AND email='$email' LIMIT 1");
            if(mysqli_num_rows($check) === 0) {
                $error = 'No matching account found.';
            } else {
                $user = mysqli_fetch_assoc($check);
                $message = 'Password recovered successfully: ' . escape($user['password']);
            }
        }
    }
    if($action === 'edit_account') {
        $account_id = (int)$_POST['account_id'];
        $name = mysqli_real_escape_string($conn, trim($_POST['name']));
        $email = mysqli_real_escape_string($conn, trim($_POST['email']));
        $ic = mysqli_real_escape_string($conn, trim($_POST['ic']));
        $role = isset($_POST['role']) && $_POST['role'] === 'staff' ? 'staff' : 'customer';

        if(!$name || !$email || !$ic) {
            $error = 'Please complete all fields to update account.';
        } else {
            mysqli_query($conn, "UPDATE users SET name='$name', email='$email', ic='$ic', role='$role' WHERE id='$account_id'");
            log_activity($conn, $admin_id, 'Admin', 'admin', 'Edited ' . ucfirst($role) . ' account', "Account ID: $account_id, Name: $name, Email: $email");
            $message = 'Account updated successfully.';
        }
    }
}

if(isset($_GET['delete_account'])) {
    $delete_id = (int)$_GET['delete_account'];
    $delUser = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name, role FROM users WHERE id='$delete_id'"));
    mysqli_query($conn, "DELETE FROM users WHERE id='$delete_id'");
    log_activity($conn, $admin_id, 'Admin', 'admin', 'Deleted account', "Account ID: $delete_id, Name: " . ($delUser['name'] ?? 'Unknown') . ", Role: " . ($delUser['role'] ?? 'unknown'));
    $redirectRole = isset($_GET['role']) ? $_GET['role'] : 'customer';
    header('Location: admin_home.php?section=accounts&role=' . urlencode($redirectRole));
    exit();
}

$admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$admin_id'"));
$customers = mysqli_query($conn, "SELECT * FROM users WHERE role='customer' ORDER BY id DESC");
$staffList = mysqli_query($conn, "SELECT * FROM users WHERE role='staff' ORDER BY id DESC");
$bookings = mysqli_query($conn, "SELECT b.*, u.name AS user_name, u.email AS user_email, r.title AS room_title, r.location AS room_location FROM bookings b LEFT JOIN users u ON b.user_id=u.id LEFT JOIN rooms r ON b.room_id=r.id ORDER BY b.id DESC");
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS admin_report_seen (id INT AUTO_INCREMENT PRIMARY KEY, admin_id INT NOT NULL, room_id INT NOT NULL, review_count INT NOT NULL DEFAULT 0, seen_at DATETIME NOT NULL, UNIQUE KEY uniq_admin_room (admin_id, room_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$rooms = mysqli_query($conn, "SELECT r.*, (SELECT COUNT(*) FROM reviews rv WHERE rv.room_id=r.id) AS review_count, (SELECT COALESCE(ars.review_count, 0) FROM admin_report_seen ars WHERE ars.admin_id='" . (int)$admin_id . "' AND ars.room_id=r.id) AS seen_review_count FROM rooms r ORDER BY r.id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Scholar Inn</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0b111e;
            color: #e9ecf2;
        }
        .admin-shell {
            display: grid;
            grid-template-columns: 300px 1fr;
            min-height: 100vh;
        }
        .admin-sidebar {
            background: #111827;
            padding: 32px 24px;
            border-right: 1px solid rgba(255,255,255,0.08);
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .admin-brand {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: #f5c26f;
        }
        .admin-section {
            display: block;
            padding: 14px 16px;
            border-radius: 16px;
            color: #d7dbe5;
            text-decoration: none;
            transition: background 0.2s ease, transform 0.2s ease;
        }
        .admin-section:hover,
        .admin-section.active {
            background: rgba(243,230,206,0.12);
            color: #fff;
            transform: translateX(2px);
        }
        .admin-sidebar h2 {
            margin: 0 0 16px;
            font-size: 1rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.65);
        }
        .admin-main {
            padding: 32px 40px;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }
        .admin-header h1 {
            margin: 0;
            font-size: clamp(2rem, 2.5vw, 2.8rem);
        }
        .status-box {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            padding: 18px 22px;
            border-radius: 24px;
            display: inline-flex;
            gap: 12px;
            align-items: center;
        }
        .status-box i {
            color: #f3e6ce;
        }
        .card {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 28px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 24px 60px rgba(0,0,0,0.18);
        }
        .card h2 {
            margin-top: 0;
            font-size: 1.5rem;
        }
        .message {
            margin-bottom: 18px;
            padding: 16px 18px;
            border-radius: 18px;
        }
        .message.success {
            background: rgba(40, 132, 67, 0.16);
            border: 1px solid rgba(40, 132, 67, 0.24);
            color: #d4ffe1;
        }
        .message.error {
            background: rgba(220, 38, 38, 0.14);
            border: 1px solid rgba(220, 38, 38, 0.24);
            color: #ffd7d7;
        }
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 28px;
        }
        .form-group {
            margin-bottom: 18px;
            padding: 6px 2px;
        }
        .form-group input,
        .form-group select {
            margin-top: 6px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: rgba(255,255,255,0.78);
            font-weight: 600;
        }
        .form-group input {
            width: 100%;
            max-width: 380px;
            padding: 14px 16px;
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,0.12);
            background: rgba(255,255,255,0.06);
            color: #f6f6f8;
            font-size: 1rem;
        }
        /* Slightly smaller inputs for Add/Edit account forms */
        .card.add-account .form-group input,
        .card.add-account .form-group select,
        .card.edit-account .form-group input,
        .card.edit-account .form-group select {
            max-width: 100%;
            padding: 9px 10px;
            font-size: 0.92rem;
        }
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: rgba(243,230,206,0.65);
            background: rgba(255,255,255,0.12);
        }
        /* Role toggle styles */
        .role-group .role-toggle {
            display: inline-flex;
            gap: 8px;
            margin-top: 6px;
        }

        .role-btn {
            padding: 10px 14px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.03);
            color: #e9ecf2;
            cursor: pointer;
            font-weight: 700;
            display: inline-flex;
            gap: 8px;
            align-items: center;
            transition: background 0.18s ease, transform 0.12s ease, border-color 0.18s ease;
        }

        .role-btn i { color: #f3e6ce; }

        .role-btn.active {
            background: linear-gradient(90deg, rgba(243,230,206,0.14), rgba(243,230,206,0.06));
            border-color: rgba(243,230,206,0.28);
            color: #111;
            transform: translateY(-1px);
        }
        .form-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .btn,
        .secondary-btn {
            padding: 14px 20px;
            border-radius: 999px;
            border: none;
            cursor: pointer;
            font-weight: 700;
            letter-spacing: 0.02em;
        }
        .btn {
            background: #f3e6ce;
            color: #111;
        }
        .secondary-btn {
            background: rgba(255,255,255,0.08);
            color: #f2f4f8;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
        }
        th, td {
            padding: 14px 12px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            text-align: left;
            color: #e5e7eb;
        }
        th {
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.08em;
            color: rgba(255,255,255,0.75);
        }
        .small-link {
            color: #f3e6ce;
            text-decoration: none;
            font-size: 0.95rem;
        }
        .small-link:hover {
            text-decoration: underline;
        }
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            background: rgba(255,255,255,0.08);
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,0.1);
            font-size: 0.95rem;
        }
        @media (max-width: 980px) {
            .admin-shell {
                grid-template-columns: 1fr;
            }
            .admin-sidebar {
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: center;
            }
            .admin-sidebar h2 {
                width: 100%;
                text-align: center;
            }
            .admin-main {
                padding: 24px;
            }
        }
        @media (max-width: 720px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
            .admin-sidebar {
                gap: 10px;
                padding: 18px;
            }
        }
        .password-toggle-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: #007bff;
            padding: 4px 8px;
            font-size: 0.9rem;
            transition: color 0.2s;
        }
        .password-toggle-btn:hover {
            color: #0056b3;
        }
        .password-hidden {
            font-family: monospace;
            letter-spacing: 2px;
        }
        .password-card-grid {
            gap: 18px;
            align-items: stretch;
        }
        .password-card-grid > div {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 14px;
            padding: 12px;
            max-width: 100%;
        }
        .password-card-grid h3 {
            margin-bottom: 14px;
        }
        table td {
            word-break: break-word;
        }
    </style>
</head>
<body>
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <div class="admin-brand">Scholar Inn Admin</div>
            <h2>Controls</h2>
            <a class="admin-section <?php echo $section === 'overview' ? 'active' : ''; ?>" href="admin_home.php?section=overview">Dashboard</a>
            <a class="admin-section <?php echo $section === 'add_account' ? 'active' : ''; ?>" href="admin_home.php?section=add_account">Add Account</a>
            <a class="admin-section <?php echo $section === 'accounts' ? 'active' : ''; ?>" href="admin_home.php?section=accounts&role=customer">View Accounts</a>
            <a class="admin-section <?php echo $section === 'staff_activities' ? 'active' : ''; ?>" href="admin_home.php?section=staff_activities">Staff Activities</a>
            <a class="admin-section <?php echo $section === 'bookings' ? 'active' : ''; ?>" href="admin_home.php?section=bookings">View Booking Records</a>
            <a class="admin-section <?php echo $section === 'reports' ? 'active' : ''; ?>" href="admin_home.php?section=reports">Room Reports</a>
            <a class="admin-section <?php echo $section === 'profile' ? 'active' : ''; ?>" href="admin_home.php?section=profile">Edit Profile</a>
            <a class="admin-section <?php echo $section === 'password' ? 'active' : ''; ?>" href="admin_home.php?section=password">Password</a>
            <a class="admin-section" href="logout.php">Sign Out</a>
        </aside>

        <main class="admin-main">
            <div class="admin-header">
                <div>
                    <h1>Admin Dashboard</h1>
                    <p class="pill"><i class="fas fa-user-shield"></i> Logged in as <?php echo escape($admin['name']); ?></p>
                </div>
                <div class="status-box">
                    <i class="fas fa-circle"></i>
                    <span>Admin access enabled</span>
                </div>
            </div>

            <?php if($message): ?>
                <div class="message success"><?php echo escape($message); ?></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="message error"><?php echo escape($error); ?></div>
            <?php endif; ?>

            <?php if($section === 'overview'): ?>
                <div class="card">
                    <h2>Quick overview</h2>
                    <div class="grid-2">
                        <div class="pill">Customers: <?php echo mysqli_num_rows($customers); ?></div>
                        <div class="pill">Staff: <?php echo mysqli_num_rows($staffList); ?></div>
                    </div>
                    <p style="margin-top:18px;">Use the sidebar to manage staff, users, booking records, and your admin profile.</p>
                </div>
            <?php elseif($section === 'add_account'): ?>
                <div class="card add-account">
                    <h2>Add Account</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="add_account">
                        <div class="grid-2">
                            <div class="form-group"><label>Name</label><input type="text" name="name" required></div>
                            <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
                        </div>
                        <div class="grid-2">
                            <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
                            <div class="form-group"><label>IC Number</label><input type="text" name="ic" required></div>
                        </div>
                        <div class="form-group role-group"><label>Role</label>
                            <div class="role-toggle" role="tablist" aria-label="Account role">
                                <button type="button" class="role-btn active" data-role="customer"><i class="fas fa-user"></i> Customer</button>
                                <button type="button" class="role-btn" data-role="staff"><i class="fas fa-user-tie"></i> Staff</button>
                            </div>
                            <input type="hidden" name="role" value="customer" id="role-input">
                        </div>
                        <div class="form-actions"><button class="btn" type="submit">Create Account</button></div>
                    </form>
                </div>
            <?php elseif($section === 'accounts'): ?>
                <?php $viewRole = isset($_GET['role']) && $_GET['role'] === 'staff' ? 'staff' : 'customer'; ?>
                <div class="card">
                    <h2>Accounts: <?php echo ucfirst($viewRole); ?></h2>
                    <p>Manage accounts. Use the selector to switch between Customers and Staff.</p>
                    <div style="margin-bottom:12px">
                        <a class="small-link" href="admin_home.php?section=accounts&role=customer">View Customers</a> |
                        <a class="small-link" href="admin_home.php?section=accounts&role=staff">View Staff</a>
                    </div>
                    <table>
                        <thead>
                            <tr><th>ID</th><th>Name</th><th>Email</th><th>IC</th><th>Password</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php if($viewRole === 'customer'):
                                mysqli_data_seek($customers, 0);
                                while($user = mysqli_fetch_assoc($customers)): ?>
                                <tr>
                                    <td><?php echo escape($user['id']); ?></td>
                                    <td><?php echo escape($user['name']); ?></td>
                                    <td><?php echo escape($user['email']); ?></td>
                                    <td><?php echo escape($user['ic']); ?></td>
                                    <td>
                                        <span class="password-hidden" data-password="<?php echo escape($user['password']); ?>" id="pwd-<?php echo escape($user['id']); ?>">••••••••</span>
                                        <button type="button" class="password-toggle-btn" data-user-id="<?php echo escape($user['id']); ?>"><i class="fas fa-eye"></i></button>
                                    </td>
                                    <td>
                                        <a class="small-link" href="admin_home.php?section=accounts&role=customer&edit_account_id=<?php echo escape($user['id']); ?>">Edit</a>
                                        |
                                        <a class="small-link" href="admin_home.php?delete_account=<?php echo escape($user['id']); ?>&role=customer" onclick="return confirm('Delete this account?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; else:
                                mysqli_data_seek($staffList, 0);
                                while($staff = mysqli_fetch_assoc($staffList)): ?>
                                <tr>
                                    <td><?php echo escape($staff['id']); ?></td>
                                    <td><?php echo escape($staff['name']); ?></td>
                                    <td><?php echo escape($staff['email']); ?></td>
                                    <td><?php echo escape($staff['ic']); ?></td>
                                    <td>
                                        <span class="password-hidden" data-password="<?php echo escape($staff['password']); ?>" id="pwd-<?php echo escape($staff['id']); ?>">••••••••</span>
                                        <button type="button" class="password-toggle-btn" data-user-id="<?php echo escape($staff['id']); ?>"><i class="fas fa-eye"></i></button>
                                    </td>
                                    <td>
                                        <a class="small-link" href="admin_home.php?section=accounts&role=staff&edit_account_id=<?php echo escape($staff['id']); ?>">Edit</a>
                                        |
                                        <a class="small-link" href="admin_home.php?delete_account=<?php echo escape($staff['id']); ?>&role=staff" onclick="return confirm('Delete this account?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if(isset($_GET['edit_account_id'])):
                    $editId = (int)$_GET['edit_account_id'];
                    $editResult = mysqli_query($conn, "SELECT * FROM users WHERE id='$editId'");
                    if($editResult && mysqli_num_rows($editResult) > 0):
                        $editUser = mysqli_fetch_assoc($editResult);
                ?>
                <div class="card edit-account">
                    <h2>Edit Account</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="edit_account">
                        <input type="hidden" name="account_id" value="<?php echo escape($editUser['id']); ?>">
                        <div class="grid-2">
                            <div class="form-group"><label>Name</label><input type="text" name="name" value="<?php echo escape($editUser['name']); ?>" required></div>
                            <div class="form-group"><label>Email</label><input type="email" name="email" value="<?php echo escape($editUser['email']); ?>" required></div>
                        </div>
                        <div class="grid-2">
                            <div class="form-group"><label>IC Number</label><input type="text" name="ic" value="<?php echo escape($editUser['ic']); ?>" required></div>
                            <div class="form-group"><label>Role</label>
                                <select name="role">
                                    <option value="customer" <?php echo $editUser['role']==='customer' ? 'selected' : ''; ?>>Customer</option>
                                    <option value="staff" <?php echo $editUser['role']==='staff' ? 'selected' : ''; ?>>Staff</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-actions"><button class="btn" type="submit">Save Changes</button><a class="secondary-btn" href="admin_home.php?section=accounts&role=<?php echo $viewRole; ?>">Cancel</a></div>
                    </form>
                </div>
                <?php endif; endif; ?>
            <?php elseif($section === 'staff'): ?>
                <div class="card">
                    <h2>Staff List</h2>
                    <p>Manage staff accounts, edit details, or delete a staff profile.</p>
                    <table>
                        <thead>
                            <tr><th>ID</th><th>Name</th><th>Email</th><th>IC</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php while($staff = mysqli_fetch_assoc($staffList)): ?>
                                <tr>
                                    <td><?php echo escape($staff['id']); ?></td>
                                    <td><?php echo escape($staff['name']); ?></td>
                                    <td><?php echo escape($staff['email']); ?></td>
                                    <td><?php echo escape($staff['ic']); ?></td>
                                    <td>
                                        <a class="small-link" href="admin_home.php?section=staff&edit_staff_id=<?php echo escape($staff['id']); ?>">Edit</a>
                                        |
                                        <a class="small-link" href="admin_home.php?section=staff&delete_staff=<?php echo escape($staff['id']); ?>" onclick="return confirm('Delete this staff account?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php if(isset($_GET['edit_staff_id'])):
                    $editId = (int)$_GET['edit_staff_id'];
                    $editStaffResult = mysqli_query($conn, "SELECT * FROM users WHERE id='$editId' AND role='staff'");
                    if($editStaffResult && mysqli_num_rows($editStaffResult) > 0):
                        $editStaff = mysqli_fetch_assoc($editStaffResult);
                ?>
                <div class="card">
                    <h2>Edit Staff Account</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="edit_staff">
                        <input type="hidden" name="staff_id" value="<?php echo escape($editStaff['id']); ?>">
                        <div class="grid-2">
                            <div class="form-group"><label>Name</label><input type="text" name="name" value="<?php echo escape($editStaff['name']); ?>" required></div>
                            <div class="form-group"><label>Email</label><input type="email" name="email" value="<?php echo escape($editStaff['email']); ?>" required></div>
                        </div>
                        <div class="form-group"><label>IC Number</label><input type="text" name="ic" value="<?php echo escape($editStaff['ic']); ?>" required></div>
                        <div class="form-actions"><button class="btn" type="submit">Save Changes</button><a class="secondary-btn" href="admin_home.php?section=staff">Cancel</a></div>
                    </form>
                </div>
                <?php endif; endif; ?>
            <?php elseif($section === 'staff_activities'): ?>
                <?php $staffActivities = mysqli_query($conn, "SELECT * FROM system_activities WHERE role='staff' ORDER BY created_at DESC LIMIT 500"); ?>
                <div class="card">
                    <h2>Staff Activities</h2>
                    <p>Track all actions performed by staff: room management, booking approvals, and profile updates.</p>
                    <table>
                        <thead>
                            <tr><th>ID</th><th>Staff Member</th><th>Action</th><th>Details</th><th>When</th></tr>
                        </thead>
                        <tbody>
                            <?php while($act = mysqli_fetch_assoc($staffActivities)): ?>
                                <tr>
                                    <td><?php echo escape($act['id']); ?></td>
                                    <td><?php if($act['user_id']): ?><a class="small-link" href="admin_home.php?section=accounts&role=staff&edit_account_id=<?php echo escape($act['user_id']); ?>"><?php echo escape($act['user_name'] ?: 'Staff'); ?></a><?php else: echo escape($act['user_name'] ?: 'System'); endif; ?></td>
                                    <td><?php echo escape($act['action']); ?></td>
                                    <td><?php echo nl2br(escape(strlen($act['details']) > 200 ? substr($act['details'],0,200) . '...' : $act['details'])); ?></td>
                                    <td><?php echo escape($act['created_at']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif($section === 'bookings'): ?>
                <div class="card">
                    <h2>Booking Records</h2>
                    <p>Review all booking activity and payment details.</p>
                    <table>
                        <thead>
                            <tr><th>ID</th><th>User</th><th>Room</th><th>Location</th><th>Status</th><th>Deposit</th><th>Full Payment</th><th>Check-in</th><th>Room Report</th></tr>
                        </thead>
                        <tbody>
                            <?php while($booking = mysqli_fetch_assoc($bookings)): ?>
                                <tr>
                                    <td><?php echo escape($booking['id']); ?></td>
                                    <td><?php echo escape($booking['user_name'] ?: 'Unknown'); ?><br><small><?php echo escape($booking['user_email']); ?></small></td>
                                    <td><?php echo escape($booking['room_title'] ?: 'Unknown'); ?></td>
                                    <td><?php echo escape($booking['room_location'] ?: 'N/A'); ?></td>
                                    <td><?php echo escape($booking['status']); ?></td>
                                    <td><?php echo escape($booking['payment']); ?></td>
                                    <td><?php echo escape($booking['full_payment']); ?></td>
                                    <td><?php echo escape($booking['checkin_date'] ?? ''); ?></td>
                                    <td><a class="small-link" href="generate_report_view.php?room_id=<?php echo escape($booking['room_id'] ?? 0); ?>">View Report</a></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif($section === 'profile'): ?>
                <div class="card">
                    <h2>Edit Profile</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="grid-2">
                            <div class="form-group"><label>Name</label><input type="text" name="name" value="<?php echo escape($admin['name']); ?>" required></div>
                            <div class="form-group"><label>Email</label><input type="email" name="email" value="<?php echo escape($admin['email']); ?>" required></div>
                        </div>
                        <div class="form-actions"><button class="btn" type="submit">Save Profile</button></div>
                    </form>
                </div>
            <?php elseif($section === 'password'): ?>
                <div class="card">
                    <h2>Password</h2>
                    <p>Change your own password, or recover another account password from this page.</p>
                    <div class="grid-2 password-card-grid">
                        <div>
                            <h3 style="margin-top:0;">Change Your Password</h3>
                            <form method="POST">
                                <input type="hidden" name="action" value="change_password">
                                <div class="form-group"><label>Current Password</label><input type="password" name="old_password" required></div>
                                <div class="form-group"><label>New Password</label><input type="password" name="new_password" required></div>
                                <div class="form-group"><label>Confirm New Password</label><input type="password" name="confirm_password" required></div>
                                <div class="form-actions"><button class="btn" type="submit">Change Password</button></div>
                            </form>
                        </div>
                        <div>
                            <h3 style="margin-top:0;">Recover Password</h3>
                            <form method="POST">
                                <input type="hidden" name="action" value="recover_password">
                                <div class="form-group"><label>Full Name / Username</label><input type="text" name="recover_name" required></div>
                                <div class="form-group"><label>Email Address</label><input type="email" name="recover_email" required></div>
                                <div class="form-actions"><button class="btn" type="submit">Recover Password</button></div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <h2>Section not found</h2>
                    <p>Please choose a valid admin section from the sidebar.</p>
                </div>
            <?php endif; ?>
            <?php if($section === 'reports'): ?>
                <div class="card">
                    <h2>Room Reports</h2>
                    <p>Generate a booking report (CSV) for each room.</p>
                    <table>
                        <thead>
                            <tr><th>Room ID</th><th>Title</th><th>Location</th><th>Price</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php while($room = mysqli_fetch_assoc($rooms)): ?>
                                <tr>
                                    <td><?php echo escape($room['id']); ?></td>
                                    <td><?php echo escape($room['title']); ?></td>
                                    <td><?php echo escape($room['location'] ?? 'N/A'); ?></td>
                                    <td><?php echo escape($room['price']); ?></td>
                                    <td>
                                        <?php
                                            $reviewCount = (int)($room['review_count'] ?? 0);
                                            $seenCount = (int)($room['seen_review_count'] ?? 0);
                                            $newReviewCount = max(0, $reviewCount - $seenCount);
                                        ?>
                                        <?php if($newReviewCount > 0): ?>
                                            <span style="display:inline-block;padding:4px 8px;border-radius:999px;background:#f59e0b;color:#111827;font-size:12px;font-weight:700;margin-right:8px;">New review: <?php echo $newReviewCount; ?></span>
                                        <?php endif; ?>
                                        <a class="small-link" href="generate_report_view.php?room_id=<?php echo escape($room['id']); ?>">View Report</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function(){
            // Role toggle functionality
            var roleBtns = document.querySelectorAll('.role-btn');
            var roleInput = document.getElementById('role-input');
            if(roleBtns.length && roleInput) {
                roleBtns.forEach(function(btn){
                    btn.addEventListener('click', function(){
                        roleBtns.forEach(function(b){ b.classList.remove('active'); });
                        btn.classList.add('active');
                        roleInput.value = btn.getAttribute('data-role');
                    });
                });
            }
            
            // Password toggle functionality
            var passwordToggles = document.querySelectorAll('.password-toggle-btn');
            passwordToggles.forEach(function(btn){
                btn.addEventListener('click', function(){
                    var userId = btn.getAttribute('data-user-id');
                    var pwdSpan = document.getElementById('pwd-' + userId);
                    if(pwdSpan) {
                        if(pwdSpan.textContent === '••••••••') {
                            var password = pwdSpan.getAttribute('data-password');
                            pwdSpan.textContent = password;
                            btn.innerHTML = '<i class="fas fa-eye-slash"></i>';
                        } else {
                            pwdSpan.textContent = '••••••••';
                            btn.innerHTML = '<i class="fas fa-eye"></i>';
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>