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

$budgetStmt = $pdo->prepare("SELECT id, category, budget_limit, month FROM budgets WHERE user_id = ? ORDER BY month DESC, category ASC");
$budgetStmt->execute([$user_id]);
$budgets = $budgetStmt->fetchAll(PDO::FETCH_ASSOC);

$spendingStmt = $pdo->prepare("SELECT LOWER(category) as category, SUM(amount) as spent FROM transactions WHERE user_id = ? AND type = 'expense' GROUP BY LOWER(category)");
$spendingStmt->execute([$user_id]);
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
                <p class="stat-label">Total Budget</p>
                <h2 class="stat-value"><?php echo '₱' . number_format($totalBudget, 2); ?></h2>
            </div>
            <div class="stat-card stat-card--expense">
                <p class="stat-label">Total Spent</p>
                <h2 class="stat-value expense-val"><?php echo '₱' . number_format($totalSpent, 2); ?></h2>
            </div>
            <div class="stat-card stat-card--income">
                <p class="stat-label">Remaining Budget</p>
                <h2 class="stat-value income-val"><?php echo '₱' . number_format(max($remainingBudget, 0), 2); ?></h2>
            </div>
            <div class="stat-card stat-card--alert">
                <p class="stat-label">Categories Tracked</p>
                <h2 class="stat-value"><?php echo count($budgets); ?></h2>
            </div>
        </div>

        <div class="content-grid">
            <div class="left-col">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Active Budgets</h3>
                        <span class="tag">Your tracking setup</span>
                    </div>
                    <?php if (empty($budgets)): ?>
                        <p style="color:#64748B; margin-top: 16px;">No budgets defined yet. Add one to get started.</p>
                    <?php else: ?>
                        <div class="budget-list" style="margin-top: 16px;">
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
                                    </div>
                                    <div class="progress-bar"><div class="progress-fill <?php echo $barClass; ?>" style="width: <?php echo $percentage; ?>%"></div></div>
                                    <p style="margin-top:10px;color:#64748B;font-size:0.88rem;">Spent ₱<?php echo number_format($spent,2); ?> of ₱<?php echo number_format($budget['budget_limit'],2); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
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
