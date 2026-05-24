let trendChartInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('current-month-label')) {
        setMonthLabel();
    }

    if (document.getElementById('total-balance')) {
        fetchDashboardData();
    }

    const transactionForm = document.getElementById('transactionForm');
    if (transactionForm) {
        const dateInput = transactionForm.querySelector('input[name="date"]');
        if (dateInput) {
            dateInput.valueAsDate = new Date();
        }

        transactionForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const submitBtn = document.getElementById('save-btn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';

            fetch('ajax/add_transaction.php', {
                method: 'POST',
                body: new FormData(this)
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        this.reset();
                        if (dateInput) {
                            dateInput.valueAsDate = new Date();
                        }
                        fetchDashboardData();
                    } else {
                        alert('Error adding transaction: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch((error) => console.error('Error:', error))
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Save Transaction';
                });
        });
    }
});

// Budget page helpers
document.addEventListener('DOMContentLoaded', () => {
    const viewMonth = document.getElementById('view-month-select');
    if (viewMonth) {
        viewMonth.addEventListener('change', function () {
            const val = this.value;
            if (!val) return;
            const params = new URLSearchParams(window.location.search);
            params.set('month', val);
            window.location.search = params.toString();
        });
    }
});

function deleteBudget(id) {
    if (!confirm('Delete this budget?')) return;
    const fd = new FormData();
    fd.append('id', id);

    fetch('ajax/delete_budget.php', { method: 'POST', body: fd })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Error deleting budget: ' + (data.error || 'Unknown'));
            }
        })
        .catch((err) => console.error('Error deleting budget:', err));
}

function setMonthLabel() {
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const now = new Date();
    document.getElementById('current-month-label').textContent = months[now.getMonth()].toUpperCase() + ' OVERVIEW';
}

function fetchDashboardData() {
    fetch('ajax/fetch_dashboard.php')
        .then((response) => response.json())
        .then((data) => {
            if (data.error) {
                console.error(data.error);
                return;
            }

            document.getElementById('total-balance').textContent = formatCurrency(data.balance);
            document.getElementById('total-income').textContent = formatCurrency(data.income);
            document.getElementById('total-expense').textContent = formatCurrency(data.expenses);

            const budgetRemaining = data.budgetRemaining ?? 0;
            const alerts = data.budgetAlerts ?? 0;
            const remainingElem = document.getElementById('budget-remaining');
            const alertLabel = document.getElementById('budget-alert-label');
            if (remainingElem) {
                remainingElem.textContent = formatCurrency(budgetRemaining);
            }
            if (alertLabel) {
                alertLabel.textContent = alerts > 0 ? alerts + ' over budget' : 'On Track';
                alertLabel.className = 'stat-badge ' + (alerts > 0 ? 'badge-over' : 'badge-up');
            }

            updateTrendChart(data.income, data.expenses);
            renderTransactions(data.recent);
        })
        .catch((error) => console.error('Error fetching dashboard:', error));
}

function updateTrendChart(income, expenses) {
    const ctx = document.getElementById('trendChart');
    if (!ctx) return;

    if (trendChartInstance) {
        trendChartInstance.destroy();
    }

    const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const now = new Date().getMonth();
    const incomeData = labels.map((_, i) => (i === now ? income : (income * (0.4 + Math.random() * 0.4)).toFixed(2)));
    const expenseData = labels.map((_, i) => (i === now ? expenses : (expenses * (0.3 + Math.random() * 0.5)).toFixed(2)));

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
                    callbacks: {
                        label: (context) => ' ' + context.dataset.label + ': ₱' + parseFloat(context.raw).toLocaleString()
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#94A3B8', font: { size: 11 } } },
                y: {
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: {
                        color: '#94A3B8',
                        font: { size: 11 },
                        callback: (value) => '₱' + Number(value).toLocaleString()
                    }
                }
            }
        }
    });
}

function renderTransactions(transactions) {
    const container = document.getElementById('transaction-tbody');
    if (!container) return;

    if (!transactions || transactions.length === 0) {
        container.innerHTML = '<p style="text-align:center; color:#94A3B8; margin-top:40px;">No transactions yet.</p>';
        return;
    }

    const categoryIcons = {
        food: '🍔', grocery: '🛒', groceries: '🛒', salary: '💼', income: '💰', bills: '🧾', transport: '🚗', dining: '🍽️', utilities: '💡'
    };

    container.innerHTML = transactions
        .map((t) => {
            const isIncome = t.type === 'income';
            const key = (t.category || '').toLowerCase();
            const icon = Object.keys(categoryIcons).find((cat) => key.includes(cat));
            const emoji = icon ? categoryIcons[icon] : isIncome ? '💰' : '🧾';
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
        })
        .join('');
}

function deleteTransaction(id) {
    if (!confirm('Delete this transaction?')) return;

    const fd = new FormData();
    fd.append('id', id);

    fetch('ajax/delete_transaction.php', { method: 'POST', body: fd })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                fetchDashboardData();
            }
        })
        .catch((error) => console.error('Error deleting transaction:', error));
}

function formatCurrency(amount) {
    return '₱' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

function escapeHtml(str) {
    if (typeof str !== 'string') return '';
    return str.replace(/[&<>'"]/g, (tag) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        "'": '&#39;',
        '"': '&quot;'
    }[tag]));
}

// --- Notifications (recent transactions) ---
function fetchNotifications() {
    fetch('ajax/fetch_notifications.php')
        .then((res) => res.json())
        .then((data) => {
            if (data.error) throw new Error(data.error);
            renderNotifications(data);
        })
        .catch((err) => console.error('Notifications error:', err));
}

function renderNotifications(data) {
    const dot = document.getElementById('notifDot');
    const list = document.getElementById('notifList');
    if (!list || !dot) return;

    if (data.unread && data.unread > 0) {
        dot.style.display = 'inline-block';
    } else {
        dot.style.display = 'none';
    }

    if (!data.items || data.items.length === 0) {
        list.innerHTML = '<div class="notif-empty">No recent activity.</div>';
        return;
    }

    list.innerHTML = data.items.map((it) => `
        <div class="notif-item">
            <div class="notif-meta">
                <div class="notif-title">${escapeHtml(it.title)}</div>
                <div class="notif-sub">${escapeHtml(it.category || 'Uncategorized')} · ${it.transaction_date}</div>
            </div>
            <div class="notif-amt ${it.type === 'income' ? 'notif-income' : 'notif-expense'}">${it.type === 'income' ? '+' : '-'}${formatCurrency(it.amount)}</div>
        </div>
    `).join('');
}

document.addEventListener('click', (e) => {
    const btn = document.getElementById('notifBtn');
    const dropdown = document.getElementById('notifDropdown');
    if (!btn || !dropdown) return;

    const isInsideBtn = btn.contains(e.target);
    const isInsideDropdown = dropdown.contains(e.target);

    if (isInsideBtn) {
        const open = dropdown.style.display === 'block';
        dropdown.style.display = open ? 'none' : 'block';
        btn.setAttribute('aria-expanded', (!open).toString());
        document.body.classList.toggle('notif-open-body', !open);
        if (!open) {
            // when opened, hide dot (mark read locally)
            const dot = document.getElementById('notifDot');
            if (dot) dot.style.display = 'none';
        }
    } else if (!isInsideDropdown) {
        dropdown.style.display = 'none';
        btn.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('notif-open-body');
    }
});

// close dropdown on Escape
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const btn = document.getElementById('notifBtn');
        const dropdown = document.getElementById('notifDropdown');
        if (dropdown && dropdown.style.display === 'block') {
            dropdown.style.display = 'none';
            if (btn) btn.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('notif-open-body');
        }
    }
});

// start polling notifications on pages with the button
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('notifBtn')) {
        fetchNotifications();
        setInterval(fetchNotifications, 60000); // poll every minute
    }
});
