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
</head>
<body>
<div class="dashboard-container">
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <header class="topbar">
            <div>
                <h1 class="page-title">Analytics</h1>
                <p class="page-subtitle">Explore your spending and income trends.</p>
            </div>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <p class="stat-label">Overview</p>
                <h2 class="stat-value">Real-time data</h2>
            </div>
            <div class="stat-card stat-card--income">
                <p class="stat-label">Insight</p>
                <h2 class="stat-value">Budget health</h2>
            </div>
            <div class="stat-card stat-card--expense">
                <p class="stat-label">Performance</p>
                <h2 class="stat-value">Visual reports</h2>
            </div>
            <div class="stat-card stat-card--alert">
                <p class="stat-label">Next step</p>
                <h2 class="stat-value">Action plan</h2>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Financial Reports</h3>
                <span class="tag">Summary</span>
            </div>
            <p style="margin-top: 16px; color:#64748B; line-height:1.7;">
                Access charts, spending breakdowns, and income trends from your dashboard. This section supports your key financial decisions by bringing all data into one place.
            </p>
        </div>
    </main>
</div>
<?php require 'includes/footer.php'; ?>