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
                <button id="exportCsvBtn" class="btn btn-green">Export CSV</button>
                <button id="printBtn" class="btn">Print</button>
                <button id="downloadPdfBtn" class="btn btn-red">Download PDF</button>
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

        <div id="reportNotice" class="card" style="margin-bottom:16px; display:none; color:#EF4444; background:#FEF2F2; border-color:#FEE2E2;">
            <p style="margin:0;">Unable to load analytics.</p>
        </div>

        <div class="report-summary" id="summaryCards">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Total Income</h3>
                </div>
                <p id="summaryIncome" class="report-metric">Loading...</p>
            </div>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Total Expense</h3>
                </div>
                <p id="summaryExpense" class="report-metric">Loading...</p>
            </div>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Net Balance</h3>
                </div>
                <p id="summaryNet" class="report-metric">Loading...</p>
            </div>
        </div>

        <div class="reports-grid">
            <div>
                <div class="report-card" style="margin-bottom:18px;">
                    <h4>Monthly Income & Expenses</h4>
                    <canvas id="monthlyTrendChart" height="200"></canvas>
                    <div id="monthlyMessage" class="report-metric"></div>
                </div>

                <div class="report-card" style="margin-bottom:18px;">
                    <h4>Spending by Category</h4>
                    <canvas id="spendingPieChart" height="220"></canvas>
                    <div id="pieMessage" class="report-metric"></div>
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