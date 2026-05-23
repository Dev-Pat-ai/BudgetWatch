let monthlyChart = null;
let spendingPie = null;

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('monthlyTrendChart')) {
        loadReports();
        document.getElementById('exportCsvBtn').addEventListener('click', () => {
            window.location = 'ajax/export_reports.php';
        });

        document.getElementById('printBtn').addEventListener('click', () => window.print());
        document.getElementById('downloadPdfBtn').addEventListener('click', () => window.print());
    }
});

function loadReports() {
    fetch('ajax/fetch_reports.php')
        .then((res) => res.json())
        .then((data) => {
            if (data.error) {
                console.error(data.error);
                return;
            }

            renderMonthlyChart(data.monthly || []);
            renderSpendingPie(data.byCategory || []);
            renderTransactionsTable(data.transactions || []);
            renderIncomeSummary(data.incomeBy || []);
            renderSpendingInsights(data.byCategory || []);
        })
        .catch((err) => console.error('Error loading reports:', err));
}

function renderMonthlyChart(monthly) {
    const ctx = document.getElementById('monthlyTrendChart');
    if (!ctx) return;

    const labels = monthly.map((m) => m.ym);
    const incomes = monthly.map((m) => parseFloat(m.income));
    const expenses = monthly.map((m) => parseFloat(m.expense));

    if (monthlyChart) monthlyChart.destroy();

    monthlyChart = new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels,
            datasets: [
                { label: 'Income', data: incomes, borderColor: '#10B981', backgroundColor: 'rgba(16,185,129,0.06)', fill:true },
                { label: 'Expenses', data: expenses, borderColor: '#EF4444', backgroundColor: 'rgba(239,68,68,0.06)', fill:true }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
}

function renderSpendingPie(byCategory) {
    const ctx = document.getElementById('spendingPieChart');
    if (!ctx) return;

    const labels = byCategory.map((c) => c.category || 'Uncategorized');
    const values = byCategory.map((c) => parseFloat(c.total));

    if (spendingPie) spendingPie.destroy();

    spendingPie = new Chart(ctx.getContext('2d'), {
        type: 'pie',
        data: { labels, datasets: [{ data: values, backgroundColor: generateColors(values.length) }] },
        options: { responsive: true }
    });
}

function generateColors(n) {
    const base = ['#EF4444','#F59E0B','#10B981','#3B82F6','#8B5CF6','#EF7AB8','#06B6D4'];
    const out = [];
    for (let i=0;i<n;i++) out.push(base[i % base.length]);
    return out;
}

function renderTransactionsTable(transactions) {
    const tbody = document.getElementById('transactionsTbody');
    if (!tbody) return;
    if (!transactions || transactions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#94A3B8;">No transactions</td></tr>';
        return;
    }

    tbody.innerHTML = transactions.map((t) => `
        <tr>
            <td>${t.transaction_date}</td>
            <td>${escapeHtml(t.title)}</td>
            <td>${escapeHtml(t.category || '')}</td>
            <td>${t.type}</td>
            <td style="text-align:right;">₱${Number(t.amount).toFixed(2)}</td>
        </tr>
    `).join('');
}

function renderIncomeSummary(incomeBy) {
    const el = document.getElementById('incomeSummary');
    if (!el) return;
    if (!incomeBy || incomeBy.length === 0) {
        el.innerHTML = '<p style="color:#94A3B8;">No income records yet.</p>';
        return;
    }
    el.innerHTML = '<ul style="padding-left:16px;">' + incomeBy.map(i => `<li>${escapeHtml(i.category || 'Income')}: ₱${Number(i.total).toFixed(2)}</li>`).join('') + '</ul>';
}

function renderSpendingInsights(byCategory) {
    const el = document.getElementById('spendingInsights');
    if (!el) return;
    if (!byCategory || byCategory.length === 0) {
        el.innerHTML = '<p style="color:#94A3B8;">No expenses yet.</p>';
        return;
    }
    const top = byCategory[0];
    el.innerHTML = `<p>Top category: <strong>${escapeHtml(top.category || 'Uncategorized')}</strong> — ₱${Number(top.total).toFixed(2)}</p>`;
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, (s) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));
}
