<?php
require 'includes/db.php';
require 'includes/functions.php';
require 'includes/auth.php';

if (isLoggedIn()) { header("Location: dashboard.php"); exit; }

$error = '';
$success = isset($_GET['registered']) ? "Registration successful! Please login." : "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        if (!empty($user['avatar'])) $_SESSION['avatar'] = $user['avatar'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BudgetWatch — Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body style="background:#F0F2F5; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0;">

<div style="width:100%; max-width:440px; padding:20px;">

    <div style="text-align:center; margin-bottom:28px;">
        <div style="display:inline-flex; align-items:center; gap:10px; margin-bottom:8px;">
            <div style="width:36px;height:36px;background:#10B981;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
            </div>
            <span style="font-size:1.25rem;font-weight:700;color:#0F172A;">BudgetWatch</span>
        </div>
        <p style="color:#64748B;font-size:0.875rem;margin:0;">Your smart personal finance tracker</p>
    </div>

    <div style="background:#fff;border-radius:16px;padding:36px;box-shadow:0 4px 24px rgba(0,0,0,0.08);border:1px solid #E8ECF0;">
        <h2 style="font-size:1.4rem;font-weight:700;color:#0F172A;margin:0 0 6px;">Welcome back</h2>
        <p style="color:#64748B;font-size:0.875rem;margin:0 0 28px;">Sign in to your account to continue</p>

        <?php if($error): ?>
        <div style="background:#FEE2E2;color:#991B1B;padding:12px 14px;border-radius:8px;margin-bottom:20px;font-size:0.875rem;display:flex;align-items:center;gap:8px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <?php if($success): ?>
        <div style="background:#D1FAE5;color:#065F46;padding:12px 14px;border-radius:8px;margin-bottom:20px;font-size:0.875rem;display:flex;align-items:center;gap:8px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            <?php echo $success; ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div style="margin-bottom:18px;">
                <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.04em;">Email Address</label>
                <input type="email" name="email" required placeholder="demo@example.com"
                    style="width:100%;padding:11px 14px;border:1px solid #E2E8F0;border-radius:8px;font-family:inherit;font-size:0.9rem;color:#0F172A;background:#F8FAFC;box-sizing:border-box;transition:border 0.2s,box-shadow 0.2s;"
                    onfocus="this.style.borderColor='#10B981';this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.12)';this.style.background='#fff'"
                    onblur="this.style.borderColor='#E2E8F0';this.style.boxShadow='none';this.style.background='#F8FAFC'">
            </div>

            <div style="margin-bottom:24px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                    <label style="font-size:0.8rem;font-weight:600;color:#374151;text-transform:uppercase;letter-spacing:0.04em;">Password</label>
                    <a href="#" style="font-size:0.8rem;color:#10B981;font-weight:600;text-decoration:none;">Forgot password?</a>
                </div>
                <input type="password" name="password" required placeholder="••••••••"
                    style="width:100%;padding:11px 14px;border:1px solid #E2E8F0;border-radius:8px;font-family:inherit;font-size:0.9rem;color:#0F172A;background:#F8FAFC;box-sizing:border-box;transition:border 0.2s,box-shadow 0.2s;"
                    onfocus="this.style.borderColor='#10B981';this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.12)';this.style.background='#fff'"
                    onblur="this.style.borderColor='#E2E8F0';this.style.boxShadow='none';this.style.background='#F8FAFC'">
            </div>

            <button type="submit"
                style="width:100%;padding:13px;background:#10B981;color:#fff;border:none;border-radius:8px;font-family:inherit;font-size:0.95rem;font-weight:700;cursor:pointer;transition:background 0.2s,transform 0.15s;letter-spacing:0.01em;"
                onmouseover="this.style.background='#059669'"
                onmouseout="this.style.background='#10B981'">
                Sign In
            </button>
        </form>

        <div style="text-align:center;margin-top:22px;padding-top:22px;border-top:1px solid #F1F5F9;">
            <p style="font-size:0.875rem;color:#64748B;margin:0;">
                Don't have an account?
                <a href="register.php" style="color:#10B981;font-weight:700;text-decoration:none;"> Create one free</a>
            </p>
        </div>
    </div>
</div>

</body>
</html>