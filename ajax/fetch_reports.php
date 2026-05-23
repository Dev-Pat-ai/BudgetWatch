<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
requireLogin();
session_start();
$userId = $_SESSION['user_id'];

header('Content-Type: application/json');

try {
    // Monthly totals (last 12 months)
    $stmt = $pdo->prepare("SELECT DATE_FORMAT(transaction_date, '%Y-%m') AS ym,
        SUM(CASE WHEN type='income' THEN amount ELSE 0 END) AS income,
        SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS expense
        FROM transactions
        WHERE user_id = :uid
        GROUP BY ym
        ORDER BY ym DESC
        LIMIT 12");
    $stmt->execute(['uid' => $userId]);
    $monthly = array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));

    // Spending by category
    $stmt = $pdo->prepare("SELECT category, SUM(amount) AS total FROM transactions
        WHERE user_id = :uid AND type='expense'
        GROUP BY category
        ORDER BY total DESC
        LIMIT 12");
    $stmt->execute(['uid' => $userId]);
    $byCategory = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Income by category/source
    $stmt = $pdo->prepare("SELECT category, SUM(amount) AS total FROM transactions
        WHERE user_id = :uid AND type='income'
        GROUP BY category
        ORDER BY total DESC");
    $stmt->execute(['uid' => $userId]);
    $incomeBy = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Recent transactions (limit 200)
    $stmt = $pdo->prepare("SELECT id, title, amount, category, type, transaction_date FROM transactions
        WHERE user_id = :uid
        ORDER BY transaction_date DESC, created_at DESC
        LIMIT 200");
    $stmt->execute(['uid' => $userId]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'monthly' => $monthly,
        'byCategory' => $byCategory,
        'incomeBy' => $incomeBy,
        'transactions' => $transactions
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

?>
