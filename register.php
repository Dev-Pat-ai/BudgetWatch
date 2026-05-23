<?php
require 'includes/db.php';
require 'includes/functions.php';
require 'includes/auth.php';

if (isLoggedIn()) { header("Location: dashboard.php"); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email is already registered.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$name, $email, $hash])) {
                header("Location: login.php?registered=1");
                exit;
            } else {
                $error = "Registration failed. Please try again.";
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
    <title>BudgetWatch — Create Account</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body style="background:#F0F2F5; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; padding:20px 0;">

<div style="width:100%; max-width:440px; padding:20px;">

    <div style="text-align:center; margin-bottom:28px;">
        <div style="display:inline-flex; align-items:center; gap:10px; margin-bottom:8px;">
            <div style="width:36px;height:36px;background:#10B981;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
            </div>
            <span style="font-size:1.25rem;font-weight:700;color:#0F172A;">BudgetWatch</span>
        </div>
        <p style="color:#64748B;font-size:0.875rem;margin:0;">Start managing your finances today</p>
    </div>

    <div style="background:#fff;border-radius:16px;padding:36px;box-shadow:0 4px 24px rgba(0,0,0,0.08);border:1px solid #E8ECF0;">
        <h2 style="font-size:1.4rem;font-weight:700;color:#0F172A;margin:0 0 6px;">Create your account</h2>
        <p style="color:#64748B;font-size:0.875rem;margin:0 0 28px;">Free forever. No credit card required.</p>

        <?php if($error): ?>
        <div style="background:#FEE2E2;color:#991B1B;padding:12px 14px;border-radius:8px;margin-bottom:20px;font-size:0.875rem;display:flex;align-items:center;gap:8px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <?php
            $inputStyle = "width:100%;padding:11px 14px;border:1px solid #E2E8F0;border-radius:8px;font-family:inherit;font-size:0.9rem;color:#0F172A;background:#F8FAFC;box-sizing:border-box;transition:border 0.2s;";
            $labelStyle = "display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.04em;";
            $groupStyle = "margin-bottom:18px;";
            $focusJs = "onfocus=\"this.style.borderColor='#10B981';this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.12)';this.style.background='#fff'\" onblur=\"this.style.borderColor='#E2E8F0';this.style.boxShadow='none';this.style.background='#F8FAFC'\"";
            ?>

            <div style="<?php echo $groupStyle; ?>">
                <label style="<?php echo $labelStyle; ?>">Full Name</label>
                <input type="text" name="full_name" required placeholder="Juan dela Cruz"
                    style="<?php echo $inputStyle; ?>"
                    onfocus="this.style.borderColor='#10B981';this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.12)';this.style.background='#fff'"
                    onblur="this.style.borderColor='#E2E8F0';this.style.boxShadow='none';this.style.background='#F8FAFC'">
            </div>

            <div style="<?php echo $groupStyle; ?>">
                <label style="<?php echo $labelStyle; ?>">Email Address</label>
                <input type="email" name="email" required placeholder="you@example.com"
                    style="<?php echo $inputStyle; ?>"
                    onfocus="this.style.borderColor='#10B981';this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.12)';this.style.background='#fff'"
                    onblur="this.style.borderColor='#E2E8F0';this.style.boxShadow='none';this.style.background='#F8FAFC'">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:24px;">
                <div>
                    <label style="<?php echo $labelStyle; ?>">Password</label>
                    <input type="password" name="password" required placeholder="Min. 6 chars"
                        style="<?php echo $inputStyle; ?>"
                        onfocus="this.style.borderColor='#10B981';this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.12)';this.style.background='#fff'"
                        onblur="this.style.borderColor='#E2E8F0';this.style.boxShadow='none';this.style.background='#F8FAFC'">
                </div>
                <div>
                    <label style="<?php echo $labelStyle; ?>">Confirm</label>
                    <input type="password" name="confirm_password" required placeholder="Repeat"
                        style="<?php echo $inputStyle; ?>"
                        onfocus="this.style.borderColor='#10B981';this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.12)';this.style.background='#fff'"
                        onblur="this.style.borderColor='#E2E8F0';this.style.boxShadow='none';this.style.background='#F8FAFC'">
                </div>
            </div>

            <button type="submit"
                style="width:100%;padding:13px;background:#10B981;color:#fff;border:none;border-radius:8px;font-family:inherit;font-size:0.95rem;font-weight:700;cursor:pointer;transition:background 0.2s;letter-spacing:0.01em;"
                onmouseover="this.style.background='#059669'"
                onmouseout="this.style.background='#10B981'">
                Create Account
            </button>
        </form>

        <div style="text-align:center;margin-top:22px;padding-top:22px;border-top:1px solid #F1F5F9;">
            <p style="font-size:0.875rem;color:#64748B;margin:0;">
                Already have an account?
                <a href="login.php" style="color:#10B981;font-weight:700;text-decoration:none;"> Sign in here</a>
            </p>
        </div>
    </div>
</div>

</body>
</html>