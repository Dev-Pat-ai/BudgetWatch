<?php
require 'includes/auth.php';
requireLogin();
require 'includes/header.php';
?>
<div class="dashboard-container">
    <aside class="sidebar">
        <h2>Finance.io</h2>
        <nav class="sidebar-nav">
            <a href="dashboard.php">Dashboard Overview</a>
            <a href="reports.php" class="active">Reports & Analytics</a>
            <a href="logout.php" style="margin-top: auto; color: #EF4444;">Sign Out</a>
        </nav>
    </aside>
    <main class="main-content">
        <header class="dash-header">
            <div>
                <h1>Financial Reports</h1>
                <span style="color: #64748B;">Detailed analysis of your accounts.</span>
            </div>
            <button onclick="window.print()" class="btn btn-green">Print Report</button>
        </header>
        <div class="card">
            <h3>Monthly Spending Analysis</h3>
            <p style="margin-top: 10px; line-height: 1.6;">
                Use the dashboard to track your real-time data. <br>
                CSV exports and deep analytical PDFs can be securely generated from this module.
            </p>
        </div>
    </main>
</div>
<?php require 'includes/footer.php'; ?>