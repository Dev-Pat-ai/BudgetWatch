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

            const alerts = data.recent.filter((t) => t.type === 'expense').length > 5 ? 3 : 0;
            const alertCount = document.getElementById('budget-alert-count');
            const alertLabel = document.getElementById('budget-alert-label');
            if (alertCount) {
                alertCount.textContent = alerts;
            }
            if (alertLabel) {
                alertLabel.textContent = alerts > 0 ? alerts + ' Overspending' : 'On Track';
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
