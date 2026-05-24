<?php
require 'includes/db.php';
require 'includes/functions.php';

session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';
$token = sanitizeInput($_GET['token'] ?? $_POST['token'] ?? '');

// Ensure password reset table exists
$pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

if (empty($token)) {
    $error = 'Invalid or missing reset token.';
} else {
    $stmt = $pdo->prepare('SELECT pr.id AS reset_id, pr.expires_at, u.id AS user_id, u.full_name FROM password_resets pr JOIN users u ON pr.user_id = u.id WHERE pr.token = ?');
    $stmt->execute([$token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset || strtotime($reset['expires_at']) < time()) {
        $error = 'This password reset link is no longer valid. Please request a new one.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($password) || empty($confirm)) {
        $error = 'Please fill in both password fields.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $update = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
        $update->execute([$hash, $reset['user_id']]);

        $delete = $pdo->prepare('DELETE FROM password_resets WHERE id = ?');
        $delete->execute([$reset['reset_id']]);

        header('Location: login.php?reset=1');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BudgetWatch — Reset Password</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body style="background:#F0F2F5; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0;">

<div style="width:100%; max-width:440px; padding:20px;">
    <div style="text-align:center; margin-bottom:28px;">
        <div style="display:inline-flex; align-items:center; gap:10px; margin-bottom:8px;">
            <div style="width:36px;height:36px;background:#10B981;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><path d="M12 3v4"/><path d="M12 17v4"/><path d="M4.22 4.22l2.83 2.83"/><path d="M17.95 17.95l2.83 2.83"/><path d="M1 12h4"/><path d="M19 12h4"/><path d="M4.22 19.78l2.83-2.83"/><path d="M17.95 6.05l2.83-2.83"/><circle cx="12" cy="12" r="3"/></svg>
            </div>
            <span style="font-size:1.25rem;font-weight:700;color:#0F172A;">BudgetWatch</span>
        </div>
        <p style="color:#64748B;font-size:0.875rem;margin:0;">Choose a new password for your account</p>
    </div>

    <div style="background:#fff;border-radius:16px;padding:36px;box-shadow:0 4px 24px rgba(0,0,0,0.08);border:1px solid #E8ECF0;">
        <h2 style="font-size:1.4rem;font-weight:700;color:#0F172A;margin:0 0 6px;">Reset Password</h2>
        <p style="color:#64748B;font-size:0.875rem;margin:0 0 28px;">Enter your new password below.</p>

        <?php if ($error): ?>
            <div style="background:#FEE2E2;color:#991B1B;padding:12px 14px;border-radius:8px;margin-bottom:20px;font-size:0.875rem;display:flex;align-items:center;gap:8px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($reset)): ?>
            <form method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div style="margin-bottom:18px;">
                    <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.04em;">New Password</label>
                    <input type="password" name="password" required placeholder="••••••••"
                        style="width:100%;padding:11px 14px;border:1px solid #E2E8F0;border-radius:8px;font-family:inherit;font-size:0.9rem;color:#0F172A;background:#F8FAFC;box-sizing:border-box;transition:border 0.2s,box-shadow 0.2s;"
                        onfocus="this.style.borderColor='#10B981';this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.12)';this.style.background='#fff'"
                        onblur="this.style.borderColor='#E2E8F0';this.style.boxShadow='none';this.style.background='#F8FAFC'">
                </div>
                <div style="margin-bottom:24px;">
                    <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.04em;">Confirm Password</label>
                    <input type="password" name="confirm_password" required placeholder="••••••••"
                        style="width:100%;padding:11px 14px;border:1px solid #E2E8F0;border-radius:8px;font-family:inherit;font-size:0.9rem;color:#0F172A;background:#F8FAFC;box-sizing:border-box;transition:border 0.2s,box-shadow 0.2s;"
                        onfocus="this.style.borderColor='#10B981';this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.12)';this.style.background='#fff'"
                        onblur="this.style.borderColor='#E2E8F0';this.style.boxShadow='none';this.style.background='#F8FAFC'">
                </div>

                <button type="submit"
                    style="width:100%;padding:13px;background:#10B981;color:#fff;border:none;border-radius:8px;font-family:inherit;font-size:0.95rem;font-weight:700;cursor:pointer;transition:background 0.2s,transform 0.15s;letter-spacing:0.01em;"
                    onmouseover="this.style.background='#059669'"
                    onmouseout="this.style.background='#10B981'">
                    Reset Password
                </button>
            </form>
        <?php endif; ?>

        <div style="text-align:center;margin-top:22px;padding-top:22px;border-top:1px solid #F1F5F9;">
            <p style="font-size:0.875rem;color:#64748B;margin:0;">
                Remembered your password?
                <a href="login.php" style="color:#10B981;font-weight:700;text-decoration:none;">Sign in</a>
            </p>
        </div>
    </div>
</div>

</body>
</html>
