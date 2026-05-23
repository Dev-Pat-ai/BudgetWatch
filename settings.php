<?php
require 'includes/db.php';
require 'includes/functions.php';
require 'includes/auth.php';
requireLogin();
$activePage = 'settings';
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

$userStmt = $pdo->prepare("SELECT full_name, email, password FROM users WHERE id = ?");
$userStmt->execute([$user_id]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_password'])) {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'All password fields are required.';
        } elseif (!password_verify($currentPassword, $user['password'])) {
            $error = 'Current password is incorrect.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'The new passwords do not match.';
        } elseif (strlen($newPassword) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } else {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($update->execute([$hash, $user_id])) {
                $success = 'Password updated successfully.';
            } else {
                $error = 'Unable to update password. Please try again.';
            }
        }
    } elseif (isset($_POST['update_email'])) {
        $currentPassword = $_POST['current_password_email'] ?? '';
        $newEmail = sanitizeInput($_POST['email'] ?? '');

        if (empty($currentPassword) || empty($newEmail)) {
            $error = 'Current password and new email are required.';
        } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (!password_verify($currentPassword, $user['password'])) {
            $error = 'Current password is incorrect.';
        } else {
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
            $check->execute([$newEmail, $user_id]);
            if ($check->fetch()) {
                $error = 'This email is already in use.';
            } else {
                $update = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
                if ($update->execute([$newEmail, $user_id])) {
                    $_SESSION['email'] = $newEmail;
                    $success = 'Email updated successfully.';
                    $user['email'] = $newEmail;
                } else {
                    $error = 'Unable to update email. Please try again.';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BudgetWatch - Settings</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="dashboard-container">
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <header class="topbar">
            <div>
                <h1 class="page-title">Settings</h1>
                <p class="page-subtitle">Keep your account secure and tailored to your needs.</p>
            </div>
        </header>

        <?php if ($error): ?>
            <div class="card" style="border-left:4px solid #EF4444;color:#991B1B;">
                <?php echo $error; ?>
            </div>
        <?php elseif ($success): ?>
            <div class="card" style="border-left:4px solid #10B981;color:#065F46;">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <div class="content-grid settings-grid">
            <div class="left-col">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Update Email</h3>
                        <span class="tag">Account</span>
                    </div>
                    <form method="POST" style="margin-top: 16px;">
                        <div class="form-row">
                            <div class="form-group">
                                <label>New Email Address</label>
                                <input type="email" name="email" required value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" placeholder="you@example.com">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" name="current_password_email" required placeholder="Your current password">
                            </div>
                        </div>
                        <button type="submit" name="update_email" class="btn-primary">Update Email</button>
                    </form>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Change Password</h3>
                        <span class="tag">Security</span>
                    </div>
                    <form method="POST" style="margin-top: 16px;">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" name="current_password" required placeholder="Current password">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="new_password" required placeholder="New password">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <input type="password" name="confirm_password" required placeholder="Confirm new password">
                            </div>
                        </div>
                        <button type="submit" name="update_password" class="btn-primary">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>
<?php require 'includes/footer.php'; ?>
