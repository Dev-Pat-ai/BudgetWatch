<?php
require 'includes/db.php';
require 'includes/functions.php';
require 'includes/auth.php';
requireLogin();
$activePage = 'budgets';
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = sanitizeInput($_POST['category'] ?? '');
    $limit = (float)($_POST['budget_limit'] ?? 0);
    $month = sanitizeInput($_POST['month'] ?? date('Y-m'));

    if (empty($category) || $limit <= 0) {
        $error = 'Please provide a category and a valid budget amount.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO budgets (user_id, category, budget_limit, month) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $category, $limit, $month])) {
            $success = 'Budget added successfully.';
        } else {
            $error = 'Unable to save the budget. Please try again.';
        }
    }
}

$currentMonth = sanitizeInput($_GET['month'] ?? date('Y-m'));

// Only load budgets for the current month to treat them as "active"
$budgetStmt = $pdo->prepare("SELECT id, category, budget_limit, month FROM budgets WHERE user_id = ? AND month = ? ORDER BY month DESC, category ASC");
$budgetStmt->execute([$user_id, $currentMonth]);
$budgets = $budgetStmt->fetchAll(PDO::FETCH_ASSOC);

// Compute spending per category for the same month as the budgets
$spendingStmt = $pdo->prepare("SELECT LOWER(category) as category, SUM(amount) as spent FROM transactions WHERE user_id = ? AND type = 'expense' AND DATE_FORMAT(transaction_date, '%Y-%m') = ? GROUP BY LOWER(category)");
$spendingStmt->execute([$user_id, $currentMonth]);
$spentByCategory = [];
while ($row = $spendingStmt->fetch(PDO::FETCH_ASSOC)) {
    $spentByCategory[$row['category']] = (float)$row['spent'];
}

$totalBudget = array_sum(array_column($budgets, 'budget_limit'));
$totalSpent = array_sum(array_values($spentByCategory));
$remainingBudget = $totalBudget - $totalSpent;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BudgetWatch - Budgets</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="dashboard-container">
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <header class="topbar">
            <div>
                <h1 class="page-title">Budgets</h1>
                <p class="page-subtitle">Manage your monthly spending limits.</p>
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

        <div class="stats-grid">
            <div class="stat-card">
                <p class="stat-label">Active Budget Total</p>
                <h2 class="stat-value"><?php echo '₱' . number_format($totalBudget, 2); ?></h2>
            </div>
            <div class="stat-card stat-card--expense">
                <p class="stat-label">Budget Used</p>
                <h2 class="stat-value expense-val"><?php echo '₱' . number_format($totalSpent, 2); ?></h2>
            </div>
            <div class="stat-card stat-card--income">
                <p class="stat-label">Remaining Budget</p>
                <h2 class="stat-value income-val"><?php echo '₱' . number_format(max($remainingBudget, 0), 2); ?></h2>
            </div>
            <div class="stat-card stat-card--alert">
                <p class="stat-label">Active Budget Categories</p>
                <h2 class="stat-value"><?php echo count($budgets); ?></h2>
            </div>
        </div>

        <div class="content-grid">
            <div class="left-col">
                <div class="card">
                    <div class="card-header" style="align-items:center; gap:12px;">
                        <div>
                            <h3 class="card-title">Active Budgets</h3>
                            <span class="tag">Your tracking setup</span>
                        </div>
                        <div style="margin-left:auto; display:flex; align-items:center; gap:8px;">
                            <div class="month-control">
                                <label class="month-label">Showing month</label>
                                <div class="month-picker">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon-calendar" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                    <input id="view-month-select" class="month-input" type="month" value="<?php echo htmlspecialchars($currentMonth); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body" style="padding-top:12px;">
                    <?php if (empty($budgets)): ?>
                        <div style="color:#64748B; padding:28px 12px; text-align:center;">No budgets defined yet. Add one to get started.</div>
                    <?php else: ?>
                        <div class="budget-list" style="margin-top: 8px;">
                            <?php foreach ($budgets as $budget):
                                $key = strtolower($budget['category']);
                                $spent = $spentByCategory[$key] ?? 0;
                                $percentage = $budget['budget_limit'] > 0 ? min(100, ($spent / $budget['budget_limit']) * 100) : 0;
                                $barClass = $percentage > 90 ? 'progress-over' : ($percentage > 70 ? 'progress-warn' : 'progress-ok');
                            ?>
                                <div class="budget-item">
                                    <div class="budget-meta">
                                        <span class="budget-name"><?php echo htmlspecialchars($budget['category']); ?> — <?php echo htmlspecialchars($budget['month']); ?></span>
                                        <span class="budget-pct<?php echo $barClass === 'progress-over' ? ' pct-over' : ($barClass === 'progress-warn' ? ' pct-warn' : ''); ?>"><?php echo round($percentage); ?>%</span>
                                        <button class="btn-delete" onclick="deleteBudget(<?php echo (int)$budget['id']; ?>)" title="Delete budget" style="margin-left:12px;">×</button>
                                    </div>
                                    <div class="progress-bar"><div class="progress-fill <?php echo $barClass; ?>" style="width: <?php echo $percentage; ?>%"></div></div>
                                    <p style="margin-top:10px;color:#64748B;font-size:0.88rem;">Spent ₱<?php echo number_format($spent,2); ?> of ₱<?php echo number_format($budget['budget_limit'],2); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="right-col">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Add New Budget</h3>
                        <span class="tag">Monthly goal</span>
                    </div>
                    <form method="POST" style="margin-top: 16px;">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Category</label>
                                <input type="text" name="category" required placeholder="e.g. Groceries">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Monthly Limit</label>
                                <input type="number" name="budget_limit" step="0.01" min="0" required placeholder="0.00">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Month</label>
                                <input type="month" name="month" value="<?php echo date('Y-m'); ?>" required>
                            </div>
                        </div>
                        <button type="submit" class="btn-primary">Save Budget</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>
<?php require 'includes/footer.php'; ?>
