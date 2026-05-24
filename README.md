# BudgetWatch

BudgetWatch is a PHP/MySQL personal budgeting app that tracks income, expenses, budgets, and monthly spending goals.

## Overview

This app provides:

- User authentication with login/register.
- Dashboard showing current month balance, income, expenses, recent transactions, and budget status.
- Budget page for adding monthly budget goals by category.
- Transaction entry for income and expense records.
- AJAX endpoints for faster dashboard updates and transaction actions.

## Key Files

- `index.php` - Landing page or home page entry.
- `login.php`, `register.php`, `logout.php` - Authentication flow.
- `dashboard.php` - Main user dashboard.
- `budgets.php` - Monthly budget management.
- `ajax/add_transaction.php` - Saves new income/expense records.
- `ajax/delete_transaction.php` - Deletes a transaction.
- `ajax/fetch_dashboard.php` - Returns dashboard JSON data used by `assets/js/app.js`.
- `ajax/delete_budget.php` - Deletes a saved budget.
- `includes/db.php` - Database connection using PDO.
- `includes/auth.php` - Session authentication helpers.
- `includes/functions.php` - Input sanitization and formatting helpers.

## Database

The app uses a MySQL database defined by `database/budget_tracker_db.sql`.
Important tables:

- `users` - stores user accounts.
- `transactions` - stores income/expense records with `transaction_date`.
- `budgets` - stores monthly budgets grouped by `category` and `month`.

## How Budget Tracking Works

### Budget Page

On `budgets.php`, the page shows:

- `Active Budget Total` - the sum of all budget limits for the selected month.
- `Budget Used` - the total expense amount for budget categories in the selected month.
- `Remaining Budget` - active budget total minus budget used.
- `Active Budget Categories` - number of budget entries for the selected month.

A small helper message now explains:

> `Budget Used` is not your total account spending — it is the amount spent so far in the categories you are tracking for the selected month.

This is important because the budget page monitors spending only against budgets, not all transactions.

### Budget Matching Logic

Budget expense tracking is based on category and month:

- Budgets are loaded for the active month only.
- Expense transactions are included if they are for the same month and match the budget category.
- The page calculates remaining budget and per-category budget progress.

If the budget category and transaction category do not match exactly, the expense will not count toward that budget.

### Dashboard Budget Summary

The dashboard uses `ajax/fetch_dashboard.php` which now returns:

- `budgetRemaining` - total remaining budget for the current month.
- `budgetAlerts` - number of budget categories that are over the limit.

The dashboard displays this data in the Budget Remaining stat card so the summary reflects actual budget status.

## Running Locally

1. Install a local PHP+MySQL environment such as XAMPP.
2. Import `database/budget_tracker_db.sql` into MySQL.
3. Place the project in your web server folder (for example `htdocs/BudgetWatch`).
4. Open `http://localhost/BudgetWatch/` in your browser.

## Notes for Developers

- The app uses PDO prepared statements to protect against SQL injection.
- Authentication checks are performed with `requireLogin()`.
- `assets/js/app.js` handles dashboard updates and budget/transaction interactions.
- The budget page now supports selecting a month to view active budgets for that month.
