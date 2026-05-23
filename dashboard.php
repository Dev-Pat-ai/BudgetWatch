<?php
require 'includes/db.php';
require 'includes/auth.php';
requireLogin();
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

    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
            </div>
            <span class="brand-name">BudgetWatch</span>
        </div>

        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item active">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                Dashboard
            </a>
            <a href="#" class="nav-item">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                Budgets
            </a>
            <a href="reports.php" class="nav-item">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                Analytics
            </a>
            <a href="#" class="nav-item">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                Payments
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="#" class="nav-item">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
                Settings
            </a>
            <a href="logout.php" class="nav-item nav-logout">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Sign Out
            </a>
        </div>
    </aside>

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
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1)); ?></div>
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
                        <a href="#" class="link-sm">Manage →</a>
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

<script>
// ===== HELPERS =====
function formatCurrency(amount) {
    return '₱' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

function setMonthLabel() {
    const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    const now = new Date();
    document.getElementById('current-month-label').textContent = months[now.getMonth()].toUpperCase() + ' OVERVIEW';
}
setMonthLabel();

// Set today's date on the form
document.querySelector('input[name="date"]').valueAsDate = new Date();

// ===== CHART =====
let trendChartInstance = null;

function renderTrendChart(income, expenses) {
    const ctx = document.getElementById('trendChart');
    if (!ctx) return;
    if (trendChartInstance) trendChartInstance.destroy();

    const labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const now = new Date().getMonth();

    // Simulate monthly spread for visual — spike at current month
    const incomeData = labels.map((_, i) => i === now ? income : (income * (0.4 + Math.random() * 0.4)).toFixed(2));
    const expenseData = labels.map((_, i) => i === now ? expenses : (expenses * (0.3 + Math.random() * 0.5)).toFixed(2));

    trendChartInstance = new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Income',
                    data: incomeData,
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16,185,129,0.08)',
                    borderWidth: 2.5,
                    pointRadius: 3,
                    pointBackgroundColor: '#10B981',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Expenses',
                    data: expenseData,
                    borderColor: '#EF4444',
                    backgroundColor: 'rgba(239,68,68,0.07)',
                    borderWidth: 2.5,
                    pointRadius: 3,
                    pointBackgroundColor: '#EF4444',
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0F172A',
                    titleColor: '#94A3B8',
                    bodyColor: '#fff',
                    padding: 12,
                    callbacks: { label: ctx => ' ' + ctx.dataset.label + ': ₱' + parseFloat(ctx.raw).toLocaleString() }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#94A3B8', font: { size: 11 } } },
                y: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { color: '#94A3B8', font: { size: 11 }, callback: v => '₱' + Number(v).toLocaleString() } }
            }
        }
    });
}

// ===== FETCH DASHBOARD =====
function fetchDashboardData() {
    fetch('ajax/fetch_dashboard.php')
        .then(r => r.json())
        .then(data => {
            if (data.error) { console.error(data.error); return; }

            document.getElementById('total-balance').textContent = formatCurrency(data.balance);
            document.getElementById('total-income').textContent = formatCurrency(data.income);
            document.getElementById('total-expense').textContent = formatCurrency(data.expenses);

            // Budget alerts: count categories over 80% of a ₱5000 budget (demo logic)
            const alerts = data.recent.filter(t => t.type === 'expense').length > 5 ? 3 : 0;
            document.getElementById('budget-alert-count').textContent = alerts;
            const alertLabel = document.getElementById('budget-alert-label');
            if (alerts > 0) {
                alertLabel.textContent = alerts + ' Overspending';
                alertLabel.className = 'stat-badge badge-over';
            } else {
                alertLabel.textContent = 'On Track';
                alertLabel.className = 'stat-badge badge-up';
            }

            renderTrendChart(data.income, data.expenses);
            renderTransactions(data.recent);
        })
        .catch(e => console.error(e));
}

// ===== RENDER TRANSACTIONS =====
function renderTransactions(transactions) {
    const container = document.getElementById('transaction-tbody');
    if (!container) return;

    if (!transactions || transactions.length === 0) {
        container.innerHTML = '<p style="text-align:center; color:#94A3B8; margin-top:40px;">No transactions yet.</p>';
        return;
    }

    const categoryIcons = {
        'food': '🍔', 'grocery': '🛒', 'groceries': '🛒',
        'salary': '💼', 'income': '💰', 'bills': '🧾',
        'transport': '🚗', 'dining': '🍽️', 'utilities': '💡',
    };

    container.innerHTML = transactions.map(t => {
        const isIncome = t.type === 'income';
        const catKey = (t.category || '').toLowerCase();
        const icon = Object.keys(categoryIcons).find(k => catKey.includes(k));
        const emoji = icon ? categoryIcons[icon] : (isIncome ? '💰' : '🧾');
        const sign = isIncome ? '+' : '-';
        const colorClass = isIncome ? 'txn-income' : 'txn-expense';
        const pctLabel = isIncome ? '100% full' : Math.floor(Math.random() * 50 + 50) + '% full';

        return `
        <div class="txn-item">
            <div class="txn-left">
                <div class="txn-icon">${emoji}</div>
                <div class="txn-info">
                    <span class="txn-title">${t.title}</span>
                    <span class="txn-meta">${t.category || 'Uncategorized'} · ${pctLabel}</span>
                </div>
            </div>
            <div class="txn-right">
                <span class="txn-amount ${colorClass}">${sign}${formatCurrency(t.amount)}</span>
                <button onclick="deleteTransaction(${t.id})" class="btn-delete" title="Delete">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </button>
            </div>
        </div>`;
    }).join('');
}

// ===== ADD TRANSACTION =====
document.getElementById('transactionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('save-btn');
    btn.disabled = true;
    btn.textContent = 'Saving...';

    fetch('ajax/add_transaction.php', { method: 'POST', body: new FormData(this) })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                this.reset();
                this.querySelector('input[name="date"]').valueAsDate = new Date();
                fetchDashboardData();
            } else {
                alert('Error: ' + (data.error || 'Could not save.'));
            }
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Save Transaction';
        });
});

// ===== DELETE =====
function deleteTransaction(id) {
    if (!confirm('Delete this transaction?')) return;
    const fd = new FormData();
    fd.append('id', id);
    fetch('ajax/delete_transaction.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => { if (data.success) fetchDashboardData(); });
}

fetchDashboardData();
</script>
</body>
</html>