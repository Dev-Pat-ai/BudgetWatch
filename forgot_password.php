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

// Ensure password reset table exists
$pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $pdo->prepare('SELECT id, full_name FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $message = 'If an account with that email exists, a password reset link has been sent.';
        $success = $message;
        
        if ($user) {
            $token = bin2hex(random_bytes(16));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $delete = $pdo->prepare('DELETE FROM password_resets WHERE user_id = ?');
            $delete->execute([$user['id']]);
            
            $insert = $pdo->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)');
            $insert->execute([$user['id'], $token, $expiresAt]);

            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'];
            $path = dirname($_SERVER['REQUEST_URI']);
            $path = rtrim($path, '/\\');
            $resetLink = $protocol . $host . $path . '/reset_password.php?token=' . $token;

            $subject = 'BudgetWatch Password Reset';
            $body = "Hello {$user['full_name']},\n\n" .
                    "A password reset request was received for your BudgetWatch account.\n" .
                    "Click the link below to reset your password:\n\n" .
                    "$resetLink\n\n" .
                    "If you did not request this, please ignore this message.\n";
            $headers = "From: noreply@budgetwatch.local\r\n" .
                       "Reply-To: noreply@budgetwatch.local\r\n" .
                       "X-Mailer: PHP/" . phpversion();

            if (!@mail($email, $subject, $body, $headers)) {
                $success .= ' Use the link below if email delivery is not configured:';
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
    <title>BudgetWatch — Forgot Password</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body style="background:#F0F2F5; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0;">

<div style="width:100%; max-width:440px; padding:20px;">
    <div style="text-align:center; margin-bottom:28px;">
        <div style="display:inline-flex; align-items:center; gap:10px; margin-bottom:8px;">
            <div style="width:36px;height:36px;background:#10B981;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><path d="M12 5v2"/><path d="M12 17v2"/><path d="M4.93 4.93l1.41 1.41"/><path d="M17.66 17.66l1.41 1.41"/><path d="M5 12h2"/><path d="M17 12h2"/><path d="M4.93 19.07l1.41-1.41"/><path d="M17.66 6.34l1.41-1.41"/><circle cx="12" cy="12" r="4"/></svg>
            </div>
            <span style="font-size:1.25rem;font-weight:700;color:#0F172A;">BudgetWatch</span>
        </div>
        <p style="color:#64748B;font-size:0.875rem;margin:0;">Reset your password securely</p>
    </div>

    <div style="background:#fff;border-radius:16px;padding:36px;box-shadow:0 4px 24px rgba(0,0,0,0.08);border:1px solid #E8ECF0;">
        <h2 style="font-size:1.4rem;font-weight:700;color:#0F172A;margin:0 0 6px;">Forgot Password</h2>
        <p style="color:#64748B;font-size:0.875rem;margin:0 0 28px;">Enter your email and we’ll send you a link to reset your password.</p>

        <?php if ($error): ?>
            <div style="background:#FEE2E2;color:#991B1B;padding:12px 14px;border-radius:8px;margin-bottom:20px;font-size:0.875rem;display:flex;align-items:center;gap:8px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="background:#D1FAE5;color:#065F46;padding:12px 14px;border-radius:8px;margin-bottom:20px;font-size:0.875rem;display:flex;flex-direction:column;gap:8px;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    <span><?php echo $success; ?></span>
                </div>
                <?php if (!empty($resetLink)): ?>
                    <div style="font-size:0.82rem;word-break:break-all;color:#475569;">Reset link: <a href="<?php echo htmlspecialchars($resetLink); ?>" style="color:#10B981; text-decoration:none;">Open reset page</a></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div style="margin-bottom:24px;">
                <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.04em;">Email Address</label>
                <input type="email" name="email" required placeholder="demo@example.com"
                    style="width:100%;padding:11px 14px;border:1px solid #E2E8F0;border-radius:8px;font-family:inherit;font-size:0.9rem;color:#0F172A;background:#F8FAFC;box-sizing:border-box;transition:border 0.2s,box-shadow 0.2s;"
                    onfocus="this.style.borderColor='#10B981';this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.12)';this.style.background='#fff'"
                    onblur="this.style.borderColor='#E2E8F0';this.style.boxShadow='none';this.style.background='#F8FAFC'">
            </div>

            <button type="submit"
                style="width:100%;padding:13px;background:#10B981;color:#fff;border:none;border-radius:8px;font-family:inherit;font-size:0.95rem;font-weight:700;cursor:pointer;transition:background 0.2s,transform 0.15s;letter-spacing:0.01em;"
                onmouseover="this.style.background='#059669'"
                onmouseout="this.style.background='#10B981'">
                Send Reset Link
            </button>
        </form>

        <div style="text-align:center;margin-top:22px;padding-top:22px;border-top:1px solid #F1F5F9;">
            <p style="font-size:0.875rem;color:#64748B;margin:0;">
                Remember your password?
                <a href="login.php" style="color:#10B981;font-weight:700;text-decoration:none;">Sign in</a>
            </p>
        </div>
    </div>
</div>

</body>
</html>
