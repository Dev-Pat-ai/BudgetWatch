<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
requireLogin();
if (session_status() === PHP_SESSION_NONE) session_start();
$userId = $_SESSION['user_id'];

header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("SELECT DATE_FORMAT(transaction_date, '%Y-%m') AS ym,
        SUM(CASE WHEN type='income' THEN amount ELSE 0 END) AS income,
        SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS expense
        FROM transactions
        WHERE user_id = :uid
        GROUP BY ym
        ORDER BY ym DESC
        LIMIT 12");
    $stmt->execute(['uid' => $userId]);
    $rawMonthly = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $monthlyIndex = [];
    foreach ($rawMonthly as $row) {
        $monthlyIndex[$row['ym']] = $row;
    }
    $monthly = [];
    $now = new DateTime('first day of this month');
    for ($i = 11; $i >= 0; $i--) {
        $month = clone $now;
        $month->modify("-{$i} months");
        $ym = $month->format('Y-m');
        if (isset($monthlyIndex[$ym])) {
            $monthly[] = $monthlyIndex[$ym];
        } else {
            $monthly[] = ['ym' => $ym, 'income' => 0, 'expense' => 0];
        }
    }

    $stmt = $pdo->prepare("SELECT category, SUM(amount) AS total FROM transactions
        WHERE user_id = :uid AND type='expense'
        GROUP BY category
        ORDER BY total DESC
        LIMIT 12");
    $stmt->execute(['uid' => $userId]);
    $byCategory = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT category, SUM(amount) AS total FROM transactions
        WHERE user_id = :uid AND type='income'
        GROUP BY category
        ORDER BY total DESC");
    $stmt->execute(['uid' => $userId]);
    $incomeBy = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
