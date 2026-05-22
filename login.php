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

    // Verify Password Hash
    if ($user && password_verify($password, $user['password'])) {
        // Secure Session Handling
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
require 'includes/header.php';
?>

<div class="auth-wrapper">
    <div class="card auth-card">
        <h2 style="text-align: center; margin-bottom: 20px;">Welcome Back</h2>
        
        <?php if($error): ?>
            <div style="background: #fee2e2; color: #EF4444; padding: 10px; border-radius: 6px; margin-bottom: 15px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div style="background: #d1fae5; color: #10B981; padding: 10px; border-radius: 6px; margin-bottom: 15px;">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required placeholder="demo@example.com">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="password123">
            </div>
            <button type="submit" class="btn w-full" style="margin-top: 10px;">Secure Login</button>
        </form>
        <p style="text-align: center; margin-top: 15px; font-size: 0.9rem;">
            Don't have an account? <a href="register.php" class="text-green bold">Register</a>
        </p>
    </div>
</div>

<?php require 'includes/footer.php'; ?>