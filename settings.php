<?php
require 'includes/db.php';
require 'includes/functions.php';
require 'includes/auth.php';
requireLogin();
$activePage = 'settings';
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

$userStmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
$userStmt->execute([$user_id]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'All fields are required.';
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

        <div class="content-grid">
            <div class="left-col">
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
                        <button type="submit" class="btn-primary">Update Password</button>
                    </form>
                </div>
            </div>
            <div class="right-col">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Account Preferences</h3>
                        <span class="tag">Coming soon</span>
                    </div>
                    <p style="color:#64748B; margin-top: 16px; line-height: 1.6;">In future releases, you can personalize notification settings, connected accounts, and user preferences from this page.</p>
                </div>
            </div>
        </div>
    </main>
</div>
<?php require 'includes/footer.php'; ?>
