<?php
// API Endpoint returning JSON data
require '../includes/db.php';
require '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized access.']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // 1. Calculate Totals via SQL Grouping
    $stmt = $pdo->prepare("SELECT type, SUM(amount) as total FROM transactions WHERE user_id = ? GROUP BY type");
    $stmt->execute([$user_id]);
    
    $income = 0;
    $expenses = 0;
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['type'] === 'income') {
            $income = (float)$row['total'];
        } elseif ($row['type'] === 'expense') {
            $expenses = (float)$row['total'];
        }
    }
    
    $balance = $income - $expenses;

    // 2. Fetch Latest 10 Transactions
    $stmt = $pdo->prepare("SELECT id, title, amount, category, type, transaction_date 
                           FROM transactions 
                           WHERE user_id = ? 
                           ORDER BY transaction_date DESC, created_at DESC LIMIT 10");
    $stmt->execute([$user_id]);
    $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Return JSON Response payload
    echo json_encode([
        'balance' => $balance,
        'income' => $income,
        'expenses' => $expenses,
        'recent' => $recent
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error occurred.']);
}
?>