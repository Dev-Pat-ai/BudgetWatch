<?php
if (!isset($activePage)) {
    $activePage = '';
}

function navClass($page) {
    global $activePage;
    return $activePage === $page ? 'nav-item active' : 'nav-item';
}
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
        </div>
        <span class="brand-name">BudgetWatch</span>
    </div>

    <nav class="sidebar-nav">
        <a href="dashboard.php" class="<?= navClass('dashboard') ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            Dashboard
        </a>
        <a href="budgets.php" class="<?= navClass('budgets') ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
            Budgets
        </a>
        <a href="reports.php" class="<?= navClass('reports') ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            Analytics
        </a>
        <a href="payments.php" class="<?= navClass('payments') ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            Payments
        </a>
        <a href="settings.php" class="<?= navClass('settings') ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l0 0a2 2 0 0 1-2.83 2.83l0 0a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v0a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l0 0a2 2 0 0 1-2.83-2.83l0 0a1.65 1.65 0 0 0 .33-1.82A1.65 1.65 0 0 0 3 12a1.65 1.65 0 0 0-.33-1.82l0 0a2 2 0 0 1 2.83-2.83l0 0a1.65 1.65 0 0 0 1.82.33 1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v0a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l0 0a2 2 0 0 1 2.83 2.83l0 0a1.65 1.65 0 0 0-.33 1.82A1.65 1.65 0 0 0 21 12a1.65 1.65 0 0 0-.33 1.82Z"/></svg>
            Settings
        </a>
        <a href="profile.php" class="<?= navClass('profile') ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Profile
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php" class="nav-item nav-logout">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Sign Out
        </a>
    </div>
</aside>
