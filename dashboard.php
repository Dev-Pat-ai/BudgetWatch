<?php
require 'includes/db.php';
require 'includes/auth.php';
requireLogin();
$activePage = 'dashboard';
$user_id = $_SESSION['user_id'];
$currentMonth = date('Y-m');

$budgetStmt = $pdo->prepare("SELECT id, category, budget_limit, month FROM budgets WHERE user_id = ? AND month = ? ORDER BY category ASC");
$budgetStmt->execute([$user_id, $currentMonth]);
$budgets = $budgetStmt->fetchAll(PDO::FETCH_ASSOC);

$spendingStmt = $pdo->prepare("SELECT LOWER(category) as category, SUM(amount) as spent FROM transactions WHERE user_id = ? AND type = 'expense' AND DATE_FORMAT(transaction_date, '%Y-%m') = ? GROUP BY LOWER(category)");
$spendingStmt->execute([$user_id, $currentMonth]);
$spentByCategory = [];
while ($row = $spendingStmt->fetch(PDO::FETCH_ASSOC)) {
    $spentByCategory[$row['category']] = (float)$row['spent'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BudgetWatch - Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="dashboard-container">

    <?php include 'includes/sidebar.php'; ?>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="main-content">

        <!-- Top Bar -->
        <header class="topbar">
            <div>
                <h1 class="page-title">Dashboard</h1>
                <p class="page-subtitle" id="current-month-label">Loading...</p>
            </div>
            <div class="topbar-right">
                <div class="notif-wrapper">
                    <button id="notifBtn" class="icon-btn" title="Notifications" aria-haspopup="true" aria-expanded="false">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        <span id="notifDot" class="notif-dot" style="display:none;"></span>
                    </button>
                    <div id="notifDropdown" class="notif-dropdown" style="display:none;">
                        <div class="notif-header">Notifications</div>
                        <div id="notifList" class="notif-list">Loading...</div>
                        <a href="dashboard.php" class="notif-footer">View all</a>
                    </div>
                </div>
                <a href="profile.php" class="user-avatar" title="Go to Profile">
                    <?php if (!empty($_SESSION['avatar'])): ?>
                        <img src="assets/images/avatars/<?php echo htmlspecialchars($_SESSION['avatar']); ?>" alt="avatar" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
                    <?php else: ?>
                        <?php echo strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1)); ?>
                    <?php endif; ?>
                </a>
            </div>
        </header>

        <!-- Stat Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <p class="stat-label">Total Balance</p>
                <h2 class="stat-value" id="total-balance">₱0.00</h2>
            </div>
            <div class="stat-card stat-card--income">
                <p class="stat-label">Total Income</p>
                <h2 class="stat-value income-val" id="total-income">₱0.00</h2>
                <span class="stat-badge badge-up">▲ This Month</span>
            </div>
            <div class="stat-card stat-card--expense">
                <p class="stat-label">Total Expenses</p>
                <h2 class="stat-value expense-val" id="total-expense">₱0.00</h2>
                <span class="stat-badge badge-down">▼ This Month</span>
            </div>
            <div class="stat-card stat-card--alert">
                <p class="stat-label">Budget Remaining</p>
                <h2 class="stat-value" id="budget-remaining">₱0.00</h2>
                <span class="stat-badge badge-warn" id="budget-alert-label">On Track</span>
            </div>
        </div>

        <!-- Charts + Transactions Row -->
        <div class="content-grid">

            <!-- Left: Chart + Add Form + Budgets -->
            <div class="left-col">

                <!-- Spending Trend Chart -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Spending Trend</h3>
                        <span class="tag">This Month</span>
                    </div>
                    <div style="position: relative; height: 220px; margin-top: 10px;">
                        <canvas id="trendChart" role="img" aria-label="Line chart showing income vs expenses over months">Income and expense trend chart</canvas>
                    </div>
                </div>

                <!-- Quick Add Transaction -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Add Transaction</h3>
                    </div>
                    <form id="transactionForm" class="quick-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Description</label>
                                <input type="text" name="title" required placeholder="e.g. Groceries">
                            </div>
                            <div class="form-group">
                                <label>Amount (₱)</label>
                                <input type="number" step="0.01" name="amount" required placeholder="0.00">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Category</label>
                                <input type="text" name="category" placeholder="e.g. Food">
                            </div>
                            <div class="form-group">
                                <label>Date</label>
                                <input type="date" name="date" required>
                            </div>
                            <div class="form-group">
                                <label>Type</label>
                                <select name="type">
                                    <option value="income">Income</option>
                                    <option value="expense" selected>Expense</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn-primary" id="save-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Save Transaction
                        </button>
                    </form>
                </div>

                <!-- Budget Progress -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Remaining Budgets</h3>
                        <a href="budgets.php" class="link-sm">Manage →</a>
                    </div>
                    <?php if (empty($budgets)): ?>
                        <p style="color:#64748B; margin-top: 16px;">No budgets set for <?php echo date('F Y'); ?>. Add a budget in the Budgets page to track your spending.</p>
                    <?php else: ?>
                        <?php
                            $totalRemaining = 0;
                            $overspentCount = 0;
                            foreach ($budgets as $budget) {
                                $key = strtolower($budget['category']);
                                $spent = $spentByCategory[$key] ?? 0;
                                $remaining = $budget['budget_limit'] - $spent;
                                if ($remaining < 0) {
                                    $overspentCount++;
                                }
                                $totalRemaining += $remaining;
                            }
                        ?>
                        <div style="display:flex;gap:16px;flex-wrap:wrap;margin-top:10px;">
                            <div style="flex:1 1 180px; background:#F8FAFC; border:1px solid #E2E8F0; padding:14px 16px; border-radius:12px;">
                                <p style="margin:0;font-size:0.78rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#64748B;">Total remaining</p>
                                <h3 style="margin:8px 0 0; color:#0F172A;">₱<?php echo number_format(max($totalRemaining, 0), 2); ?></h3>
                            </div>
                            <div style="flex:1 1 180px; background:#F8FAFC; border:1px solid #E2E8F0; padding:14px 16px; border-radius:12px;">
                                <p style="margin:0;font-size:0.78rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#64748B;">Overspent categories</p>
                                <h3 style="margin:8px 0 0; color:<?php echo $overspentCount > 0 ? '#EF4444' : '#10B981'; ?>;"><?php echo $overspentCount; ?></h3>
                            </div>
                        </div>
                        <div class="budget-list">
                            <?php foreach ($budgets as $budget):
                                $key = strtolower($budget['category']);
                                $spent = $spentByCategory[$key] ?? 0;
                                $remaining = $budget['budget_limit'] - $spent;
                                $percentage = $budget['budget_limit'] > 0 ? ($spent / $budget['budget_limit']) * 100 : 0;
                                $width = min(100, max(0, $percentage));
                                if ($percentage > 100) {
                                    $barClass = 'progress-over';
                                } elseif ($percentage > 80) {
                                    $barClass = 'progress-warn';
                                } else {
                                    $barClass = 'progress-ok';
                                }
                                $statusText = $remaining >= 0 ? '₱' . number_format($remaining, 2) . ' left' : 'Over by ₱' . number_format(abs($remaining), 2);
                                $valueClass = $remaining >= 0 ? '' : 'pct-over';
                            ?>
                                <div class="budget-item">
                                    <div class="budget-meta">
                                        <div>
                                            <span class="budget-name"><?php echo htmlspecialchars($budget['category']); ?> </span>
                                            <span class="budget-sub"><?php echo htmlspecialchars($budget['month']); ?> • <?php echo $statusText; ?></span>
                                        </div>
                                        <span class="budget-pct <?php echo $valueClass; ?>"><?php echo round(min(100, max(0, $percentage))); ?>%</span>
                                    </div>
                                    <div class="progress-bar"><div class="progress-fill <?php echo $barClass; ?>" style="width: <?php echo $width; ?>%"></div></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right: Recent Transactions -->
            <div class="right-col">
                <div class="card" style="height: 100%;">
                    <div class="card-header">
                        <h3 class="card-title">Recent Transactions</h3>
                    </div>
                    <div class="txn-list" id="transaction-tbody">
                        <p style="text-align:center; color: #94A3B8; margin-top: 40px;">Loading...</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require 'includes/footer.php'; ?>
</body>
</html>