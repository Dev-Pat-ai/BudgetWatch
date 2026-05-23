<?php
require 'includes/db.php';
require 'includes/auth.php';
requireLogin();
$activePage = 'dashboard';
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
                <button class="icon-btn" title="Notifications">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    <span class="notif-dot"></span>
                </button>
                <div class="user-avatar">
                    <?php if (!empty($_SESSION['avatar'])): ?>
                        <img src="assets/images/avatars/<?php echo htmlspecialchars($_SESSION['avatar']); ?>" alt="avatar" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
                    <?php else: ?>
                        <?php echo strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1)); ?>
                    <?php endif; ?>
                </div>
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
                <p class="stat-label">Budget Alerts</p>
                <h2 class="stat-value" id="budget-alert-count">0</h2>
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
                    <div class="budget-list" id="budget-list">
                        <div class="budget-item">
                            <div class="budget-meta">
                                <span class="budget-name">Groceries</span>
                                <span class="budget-pct">65%</span>
                            </div>
                            <div class="progress-bar"><div class="progress-fill progress-ok" style="width: 65%"></div></div>
                        </div>
                        <div class="budget-item">
                            <div class="budget-meta">
                                <span class="budget-name">Dining Out</span>
                                <span class="budget-pct pct-over">110%</span>
                            </div>
                            <div class="progress-bar"><div class="progress-fill progress-over" style="width: 100%"></div></div>
                        </div>
                        <div class="budget-item">
                            <div class="budget-meta">
                                <span class="budget-name">Transportation</span>
                                <span class="budget-pct">42%</span>
                            </div>
                            <div class="progress-bar"><div class="progress-fill progress-ok" style="width: 42%"></div></div>
                        </div>
                        <div class="budget-item">
                            <div class="budget-meta">
                                <span class="budget-name">Utilities</span>
                                <span class="budget-pct pct-warn">88%</span>
                            </div>
                            <div class="progress-bar"><div class="progress-fill progress-warn" style="width: 88%"></div></div>
                        </div>
                    </div>
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