let monthlyChart = null;
let spendingPie = null;

document.addEventListener('DOMContentLoaded', () => {
    const chartRoot = document.getElementById('monthlyTrendChart');
    if (!chartRoot) return;

    document.getElementById('exportCsvBtn')?.addEventListener('click', () => {
        window.location.href = 'ajax/export_reports.php';
    });
    document.getElementById('printBtn')?.addEventListener('click', () => window.print());
    document.getElementById('downloadPdfBtn')?.addEventListener('click', () => window.print());

    loadReports();
});

function loadReports() {
    const notice = document.getElementById('reportNotice');
    notice && (notice.style.display = 'none');

    fetch('ajax/fetch_reports.php')
        .then((res) => {
            if (!res.ok) throw new Error('Server error ' + res.status);
            return res.json();
        })
        .then((data) => {
            if (data.error) throw new Error(data.error);

            renderReportSummary(data);
            renderMonthlyChart(data.monthly || []);
            renderSpendingPie(data.byCategory || []);
            renderTransactionsTable(data.transactions || []);
            renderIncomeSummary(data.incomeBy || []);
            renderSpendingInsights(data.byCategory || []);
        })
        .catch((err) => {
            console.error('Error loading reports:', err);
            const notice = document.getElementById('reportNotice');
            if (notice) {
                notice.textContent = 'Unable to load analytics: ' + err.message;
                notice.style.display = 'block';
            }
        });
}

function renderReportSummary(data) {
    const transactions = data.transactions || [];
    const totalIncome = transactions.reduce((sum, txn) => sum + (txn.type === 'income' ? Number(txn.amount) : 0), 0);
    const totalExpense = transactions.reduce((sum, txn) => sum + (txn.type === 'expense' ? Number(txn.amount) : 0), 0);
    const net = totalIncome - totalExpense;

    document.getElementById('summaryIncome').textContent = totalIncome > 0 ? formatCurrency(totalIncome) : 'No income yet';
    document.getElementById('summaryExpense').textContent = totalExpense > 0 ? formatCurrency(totalExpense) : 'No expenses yet';
    document.getElementById('summaryNet').textContent = formatCurrency(net);
}

function renderMonthlyChart(monthly) {
    const ctx = document.getElementById('monthlyTrendChart');
    const message = document.getElementById('monthlyMessage');
    if (!ctx) return;

    const labels = monthly.map((m) => m.ym);
    let incomes = monthly.map((m) => Number(m.income || 0));
    let expenses = monthly.map((m) => Number(m.expense || 0));

    // sanitize values: convert non-finite to 0 to avoid Chart.js scale issues
    incomes = incomes.map((v) => (Number.isFinite(v) ? v : 0));
    expenses = expenses.map((v) => (Number.isFinite(v) ? v : 0));

    if (labels.length === 0 || (incomes.every((v) => v === 0) && expenses.every((v) => v === 0))) {
        monthlyChart?.destroy();
        if (message) message.textContent = 'No monthly data available yet.';
        return;
    }

    if (message) message.textContent = '';
    monthlyChart?.destroy();

    // compute sensible y-axis bounds to avoid charts stretching infinitely
    const allVals = incomes.concat(expenses);
    const maxVal = Math.max(...allVals, 0);
    const minVal = Math.min(...allVals, 0);
    const padding = Math.max(10, Math.ceil((maxVal - minVal) * 0.1));
    console.log('Monthly chart data:', { labels, incomes, expenses, minVal, maxVal, padding });

    monthlyChart = new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Income',
                    data: incomes,
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16,185,129,0.16)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                },
                {
                    label: 'Expenses',
                    data: expenses,
                    borderColor: '#EF4444',
                    backgroundColor: 'rgba(239,68,68,0.16)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true, labels: { color: '#334155' } },
                tooltip: {
                    callbacks: {
                        label: (context) => `${context.dataset.label}: ${formatCurrency(context.raw)}`,
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    min: 0,
                    suggestedMax: Math.max(maxVal + padding, 10),
                    ticks: {
                        callback: (value) => formatCurrency(value),
                        maxTicksLimit: 6
                    }
                }
            }
        }
    });
}

function renderSpendingPie(byCategory) {
    const ctx = document.getElementById('spendingPieChart');
    const message = document.getElementById('pieMessage');
    if (!ctx) return;

    const labels = byCategory.map((c) => c.category || 'Uncategorized');
    const values = byCategory.map((c) => Number(c.total || 0));

    if (values.length === 0 || values.every((v) => v === 0)) {
        spendingPie?.destroy();
        if (message) message.textContent = 'No spending category data found.';
        return;
    }

    if (message) message.textContent = '';
    spendingPie?.destroy();

    spendingPie = new Chart(ctx.getContext('2d'), {
        type: 'pie',
        data: {
            labels,
            datasets: [{ data: values, backgroundColor: generateColors(values.length) }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
}

function renderTransactionsTable(transactions) {
    const tbody = document.getElementById('transactionsTbody');
    if (!tbody) return;

    if (!transactions || transactions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#94A3B8;padding:18px;">No transactions yet.</td></tr>';
        return;
    }

    tbody.innerHTML = transactions.map((t) => `
        <tr>
            <td>${escapeHtml(t.transaction_date)}</td>
            <td>${escapeHtml(t.title)}</td>
            <td>${escapeHtml(t.category || 'Uncategorized')}</td>
            <td>${escapeHtml(t.type)}</td>
            <td style="text-align:right;">${formatCurrency(t.amount)}</td>
        </tr>
    `).join('');
}

function renderIncomeSummary(incomeBy) {
    const el = document.getElementById('incomeSummary');
    if (!el) return;

    if (!incomeBy || incomeBy.length === 0) {
        el.innerHTML = '<p class="caption-small">No income records yet.</p>';
        return;
    }

    el.innerHTML = '<ul style="padding-left:16px; margin:0;">' + incomeBy.map((i) => `
        <li>${escapeHtml(i.category || 'Income')}: ${formatCurrency(i.total)}</li>`).join('') + '</ul>';
}

function renderSpendingInsights(byCategory) {
    const el = document.getElementById('spendingInsights');
    if (!el) return;

    if (!byCategory || byCategory.length === 0) {
        el.innerHTML = '<p class="caption-small">No expenses yet.</p>';
        return;
    }

    const top = byCategory[0];
    const spendCount = byCategory.length;
    el.innerHTML = `
        <p class="caption-small">You have ${spendCount} expense categories.</p>
        <p><strong>Top spending category:</strong> ${escapeHtml(top.category || 'Uncategorized')} (${formatCurrency(top.total)})</p>
    `;
}

function generateColors(n) {
    const base = ['#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#8B5CF6', '#EC4899', '#06B6D4'];
    return Array.from({ length: n }, (_, i) => base[i % base.length]);
}

function formatCurrency(amount) {
    const value = Number(amount || 0);
    return '₱' + value.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, (s) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[s]));
}
