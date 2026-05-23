<?php
require 'includes/db.php';
require 'includes/auth.php';
requireLogin();
$activePage = 'payments';
$user_id = $_SESSION['user_id'];

$paymentStmt = $pdo->prepare("SELECT id, title, amount, category, transaction_date FROM transactions WHERE user_id = ? AND type = 'expense' ORDER BY transaction_date DESC, created_at DESC");
$paymentStmt->execute([$user_id]);
$payments = $paymentStmt->fetchAll(PDO::FETCH_ASSOC);

$totalStmt = $pdo->prepare("SELECT SUM(amount) as total FROM transactions WHERE user_id = ? AND type = 'expense'");
$totalStmt->execute([$user_id]);
$total = $totalStmt->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BudgetWatch - Payments</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="dashboard-container">
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <header class="topbar">
            <div>
                <h1 class="page-title">Payments</h1>
                <p class="page-subtitle">Review your expense payments and transaction history.</p>
            </div>
        </header>

        <div class="stats-grid">
            <div class="stat-card stat-card--expense">
                <p class="stat-label">Total Expenses</p>
                <h2 class="stat-value expense-val"><?php echo '₱' . number_format($total, 2); ?></h2>
            </div>
            <div class="stat-card">
                <p class="stat-label">Expense Entries</p>
                <h2 class="stat-value"><?php echo count($payments); ?></h2>
            </div>
            <div class="stat-card stat-card--income">
                <p class="stat-label">Most Recent Payment</p>
                <h2 class="stat-value"><?php echo count($payments) ? htmlspecialchars($payments[0]['title']) : 'N/A'; ?></h2>
            </div>
            <div class="stat-card stat-card--alert">
                <p class="stat-label">Latest Payment Date</p>
                <h2 class="stat-value"><?php echo count($payments) ? date('M j, Y', strtotime($payments[0]['transaction_date'])) : '-'; ?></h2>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Payment History</h3>
                <span class="tag">All expenses</span>
            </div>
            <?php if (empty($payments)): ?>
                <p style="color:#64748B; margin-top: 16px;">No expense transactions found yet. Add expenses from the dashboard.</p>
            <?php else: ?>
                <div class="budget-list" style="margin-top: 16px;">
                    <?php foreach ($payments as $payment): ?>
                        <div class="budget-item" style="padding: 18px 16px; background: #F8FAFC; border-radius: 16px;">
                            <div class="budget-meta">
                                <span class="budget-name"><?php echo htmlspecialchars($payment['title']); ?></span>
                                <span class="budget-pct pct-over">-₱<?php echo number_format($payment['amount'], 2); ?></span>
                            </div>
                            <p style="margin-top: 8px; color:#64748B; font-size:0.9rem;"><?php echo htmlspecialchars($payment['category'] ?: 'Uncategorized'); ?> • <?php echo date('M j, Y', strtotime($payment['transaction_date'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
<?php require 'includes/footer.php'; ?>
