<?php
require 'includes/db.php';
require 'includes/auth.php';
requireLogin();
$activePage = 'reports';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BudgetWatch - Analytics</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* small overrides for reports layout */
        .reports-grid { display: grid; grid-template-columns: 1fr 360px; gap: 20px; align-items: start; }
        .report-card { padding: 18px; background: #fff; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.03); }
        .report-actions { display:flex; gap:8px; align-items:center; }
        .report-actions .btn { padding:8px 12px; border-radius:6px; cursor:pointer; }
    </style>
</head>
<body>
<div class="dashboard-container">
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <header class="topbar">
            <div>
                <h1 class="page-title">Analytics</h1>
                <p class="page-subtitle">Monthly reports, spending and income analysis.</p>
            </div>
            <div class="report-actions">
                <button id="exportCsvBtn" class="btn">Export CSV</button>
                <button id="printBtn" class="btn">Print</button>
                <button id="downloadPdfBtn" class="btn">Download PDF</button>
            </div>
        </header>

        <div class="card" style="margin-bottom:16px;">
            <div class="card-header">
                <h3 class="card-title">Financial Reports</h3>
                <span class="tag">Summary</span>
            </div>
            <p style="margin-top: 12px; color:#64748B; line-height:1.6;">
                View monthly trends and breakdowns. Use export or print options to share or save reports.
            </p>
        </div>

        <div class="reports-grid">
            <div>
                <div class="report-card" style="margin-bottom:18px;">
                    <h4>Monthly Income & Expenses</h4>
                    <canvas id="monthlyTrendChart" height="160"></canvas>
                </div>

                <div class="report-card" style="margin-bottom:18px;">
                    <h4>Spending by Category</h4>
                    <canvas id="spendingPieChart" height="200"></canvas>
                </div>

                <div class="report-card">
                    <h4>Transactions</h4>
                    <div style="overflow:auto; max-height:280px;">
                        <table class="table" id="transactionsTable" style="width:100%; border-collapse:collapse;">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Type</th>
                                    <th class="text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody id="transactionsTbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <aside>
                <div class="report-card">
                    <h4>Income Analysis</h4>
                    <div id="incomeSummary"></div>
                </div>

                <div class="report-card" style="margin-top:12px;">
                    <h4>Spending Insights</h4>
                    <div id="spendingInsights"></div>
                </div>
            </aside>
        </div>

    </main>
</div>

<!-- include reports-specific JS -->
<script src="assets/js/reports.js"></script>
<?php require 'includes/footer.php'; ?>