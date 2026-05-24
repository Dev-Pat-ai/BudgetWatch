<?php
$require_db = require 'includes/db.php';
require 'includes/functions.php';
require 'includes/auth.php';
requireLogin();
$activePage = 'profile';
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Check if `avatar` column exists in `users` table
$colCheck = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'avatar'");
$colCheck->execute();
$hasAvatar = (bool)$colCheck->fetchColumn();

$selectSql = $hasAvatar ? "SELECT full_name, email, avatar FROM users WHERE id = ?" : "SELECT full_name, email FROM users WHERE id = ?";
$userStmt = $pdo->prepare($selectSql);
$userStmt->execute([$user_id]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);
if (!$hasAvatar) $user['avatar'] = null;

if (empty($_SESSION['avatar']) && !empty($user['avatar'])) {
    $_SESSION['avatar'] = $user['avatar'];
}

// Ensure avatar column exists (best-effort)
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) DEFAULT NULL");
} catch (Throwable $e) {
    // ignore if ALTER not supported; we'll proceed without fatal error
}

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
            // Handle avatar upload if provided
            $avatarFilename = $user['avatar'] ?? null;
            if (!empty($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
                $file = $_FILES['avatar'];
                if ($file['error'] === UPLOAD_ERR_OK) {
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($file['tmp_name']);
                    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
                    if (!isset($allowed[$mime])) {
                        $error = 'Invalid image type. Allowed: JPG, PNG, GIF, WEBP.';
                    } elseif ($file['size'] > 5 * 1024 * 1024) {
                        $error = 'File too large. Max 5 MB.';
                    } else {
                        $ext = $allowed[$mime];
                        $dir = __DIR__ . '/assets/images/avatars';
                        if (!is_dir($dir)) mkdir($dir, 0755, true);
                        $avatarFilename = 'avatar_' . $user_id . '_' . time() . '.' . $ext;
                        $dest = $dir . '/' . $avatarFilename;
                        if (!move_uploaded_file($file['tmp_name'], $dest)) {
                            $error = 'Unable to save uploaded file.';
                        }
                    }
                } else {
                    $error = 'File upload error.';
                }
            }

            if (!$error) {
                $updateSql = "UPDATE users SET full_name = ?, email = ?";
                $params = [$full_name, $email];
                if (!empty($avatarFilename)) {
                    $updateSql .= ", avatar = ?";
                    $params[] = $avatarFilename;
                }
                $updateSql .= " WHERE id = ?";
                $params[] = $user_id;

                $update = $pdo->prepare($updateSql);
                if ($update->execute($params)) {
                    $_SESSION['full_name'] = $full_name;
                    if (!empty($avatarFilename)) $_SESSION['avatar'] = $avatarFilename;
                    $success = 'Profile updated successfully.';
                    $user['full_name'] = $full_name;
                    $user['email'] = $email;
                    if (!empty($avatarFilename)) $user['avatar'] = $avatarFilename;
                } else {
                    $error = 'Unable to update profile. Please try again.';
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
            <form method="POST" enctype="multipart/form-data" style="margin-top: 16px;">
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
                <div class="form-row" style="align-items:center; gap:12px;">
                    <div style="display:flex;flex-direction:column;align-items:flex-start;">
                        <label>Profile Image (JPG, PNG, GIF, WEBP)</label>
                        <input type="file" name="avatar" accept="image/*">
                    </div>
                    <div>
                        <?php if (!empty($user['avatar'])): ?>
                            <img src="assets/images/avatars/<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar" style="width:64px;height:64px;border-radius:8px;object-fit:cover;border:1px solid #E6E6E6;">
                        <?php else: ?>
                            <div style="width:64px;height:64px;border-radius:8px;background:#F1F5F9;display:flex;align-items:center;justify-content:center;color:#64748B;font-weight:600;"><?php echo strtoupper(substr($user['full_name'] ?? 'U',0,1)); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <button type="submit" class="btn-primary">Save Profile</button>
            </form>
        </div>
    </main>
</div>
<?php require 'includes/footer.php'; ?>
