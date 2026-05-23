<?php
require 'includes/db.php';
require 'includes/functions.php';
require 'includes/auth.php';
requireLogin();
$activePage = 'profile';
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

$userStmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ?");
$userStmt->execute([$user_id]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');

    if (empty($full_name) || empty($email)) {
        $error = 'Name and email cannot be empty.';
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
        $check->execute([$email, $user_id]);
        if ($check->fetch()) {
            $error = 'This email is already in use.';
        } else {
            $update = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
            if ($update->execute([$full_name, $email, $user_id])) {
                $_SESSION['full_name'] = $full_name;
                $success = 'Profile updated successfully.';
                $user['full_name'] = $full_name;
                $user['email'] = $email;
            } else {
                $error = 'Unable to update profile. Please try again.';
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
    <title>BudgetWatch - Profile</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="dashboard-container">
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <header class="topbar">
            <div>
                <h1 class="page-title">Profile</h1>
                <p class="page-subtitle">Update your personal information.</p>
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

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Personal Information</h3>
                <span class="tag">Account details</span>
            </div>
            <form method="POST" style="margin-top: 16px;">
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                    </div>
                </div>
                <button type="submit" class="btn-primary">Save Profile</button>
            </form>
        </div>
    </main>
</div>
<?php require 'includes/footer.php'; ?>
