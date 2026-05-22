// Handles all AJAX operations, chart rendering, and dynamic updates

let expenseChartInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    // Only fetch dashboard data if we are on the dashboard page
    if (document.getElementById('total-balance')) {
        fetchDashboardData();
    }

    // Handle Transaction Submission
    const transactionForm = document.getElementById('transactionForm');
    if (transactionForm) {
        transactionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = transactionForm.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';

            const formData = new FormData(this);

            fetch('ajax/add_transaction.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.reset();
                    // Reset date to today
                    this.querySelector('input[name="date"]').valueAsDate = new Date();
                    fetchDashboardData(); // Real-time update without reload
                } else {
                    alert('Error adding transaction: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => console.error('Error:', error))
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Save Transaction';
            });
        });
    }
});

// Fetch Analytics and Table Data via AJAX
function fetchDashboardData() {
    fetch('ajax/fetch_dashboard.php')
        .then(response => response.json())
        .then(data => {
            if(data.error) {
                console.error(data.error);
                return;
            }
            
            // Update Summary Cards
            document.getElementById('total-balance').textContent = formatCurrency(data.balance);
            document.getElementById('total-income').textContent = formatCurrency(data.income);
            document.getElementById('total-expense').textContent = formatCurrency(data.expenses);

            // Update Chart
            updateChart(data.income, data.expenses);

            // Update Recent Transactions Table
            renderTransactionsTable(data.recent);
        })
        .catch(error => console.error('Error fetching dashboard:', error));
}

// Render Chart.js
function updateChart(income, expenses) {
    const ctx = document.getElementById('expenseChart');
    if(!ctx) return;

    if (expenseChartInstance) {
        expenseChartInstance.destroy();
    }

    // Colors matching CSS tokens
    const growthGreen = '#10B981';
    const alertRed = '#EF4444';

    expenseChartInstance = new Chart(ctx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Total Income', 'Total Expenses'],
            datasets: [{
                data: [income, expenses],
                backgroundColor: [growthGreen, alertRed],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
}

// Render Table
function renderTransactionsTable(transactions) {
    const tbody = document.getElementById('transaction-tbody');
    if(!tbody) return;
    
    tbody.innerHTML = ''; // Clear empty state

    if (transactions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 20px;">No recent transactions found.</td></tr>';
        return;
    }

    transactions.forEach(t => {
        const isIncome = t.type === 'income';
        const colorClass = isIncome ? 'text-green' : 'text-red';
        const sign = isIncome ? '+' : '-';
        
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${t.transaction_date}</td>
            <td class="bold">${t.title}</td>
            <td>${t.category}</td>
            <td class="bold ${colorClass}">${sign}${formatCurrency(t.amount)}</td>
            <td>
                <button onclick="deleteTransaction(${t.id})" class="btn btn-sm btn-red">Delete</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// Delete via AJAX
function deleteTransaction(id) {
    if (!confirm('Are you sure you want to delete this transaction?')) return;

    const fd = new FormData();
    fd.append('id', id);

    fetch('ajax/delete_transaction.php', {
        method: 'POST',
        body: fd
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            fetchDashboardData(); // Refresh UI instantly
        } else {
            alert('Failed to delete transaction.');
        }
    });
}

// Currency Formatter Utility
function formatCurrency(amount) {
    return '$' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}