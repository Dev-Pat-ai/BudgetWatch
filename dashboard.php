<?php
require 'includes/db.php';
require 'includes/auth.php';
requireLogin();

require 'includes/header.php';
?>

<div class="dashboard-container">
    <!-- In-update natin ang pangalan sa Sidebar ng Dashboard -->
    <aside class="sidebar">
        <h2>BudgetWatch</h2>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="active">Dashboard Overview</a>
            <a href="reports.php">Reports & Analytics</a>
            <a href="logout.php" style="margin-top: auto; color: #EF4444;">Sign Out</a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="dash-header">
            <div>
                <h1>Dashboard Overview</h1>
                <span style="color: #64748B;">Mabuhay, <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong>! Welcome to your pocket assistant.</span>
            </div>
        </header>

        <!-- Summary Cards updated via AJAX -->
        <div class="summary-grid">
            <div class="card summary-card">
                <h3>Total Balance</h3>
                <h2 id="total-balance">Loading...</h2>
            </div>
            <div class="card summary-card">
                <h3>Total Income</h3>
                <h2 id="total-income" class="text-green">Loading...</h2>
            </div>
            <div class="card summary-card">
                <h3>Total Expenses</h3>
                <h2 id="total-expense" class="text-red">Loading...</h2>
            </div>
        </div>

        <div class="dashboard-grid">
            <!-- Analytics Chart -->
            <div class="card chart-container">
                <canvas id="expenseChart"></canvas>
            </div>

            <!-- Add Transaction Form -->
            <div class="card form-container">
                <h3 style="margin-bottom: 15px;">Add Transaction</h3>
                <form id="transactionForm">
                    <div class="form-group">
                        <label>Transaction Title</label>
                        <input type="text" name="title" required placeholder="e.g., Grocery bill, Salary">
                    </div>
                    <div style="display: flex; gap: 15px;">
                        <div class="form-group" style="flex: 1;">
                            <label>Amount ($)</label>
                            <input type="number" step="0.01" min="0.01" name="amount" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Type</label>
                            <select name="type" required>
                                <option value="expense">Expense</option>
                                <option value="income">Income</option>
                            </select>
                        </div>
                    </div>
                    <div style="display: flex; gap: 15px;">
                        <div class="form-group" style="flex: 1;">
                            <label>Category</label>
                            <select name="category" required>
                                <option value="Food">Food & Dining</option>
                                <option value="Transportation">Transportation</option>
                                <option value="Bills">Bills & Utilities</option>
                                <option value="Salary">Salary / Wages</option>
                                <option value="Shopping">Shopping</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Date</label>
                            <input type="date" name="date" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-green w-full">Save Transaction</button>
                </form>
            </div>
        </div>

        <!-- Recent Transactions Table -->
        <div class="card">
            <h3>Recent Transactions</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="transaction-tbody">
                    <!-- Populated by AJAX logic in app.js -->
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php require 'includes/footer.php'; ?>