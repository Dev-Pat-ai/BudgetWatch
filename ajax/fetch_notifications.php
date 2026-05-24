<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
requireLogin();
if (session_status() === PHP_SESSION_NONE) session_start();
$userId = $_SESSION['user_id'];

header('Content-Type: application/json');

try {
    // recent 5 transactions
    $stmt = $pdo->prepare("SELECT id, title, amount, category, type, transaction_date, created_at
        FROM transactions
        WHERE user_id = :uid
        ORDER BY created_at DESC
        LIMIT 5");
    $stmt->execute(['uid' => $userId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // unread heuristic: created within last 24 hours
    $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = :uid AND created_at >= (NOW() - INTERVAL 1 DAY)");
    $stmt2->execute(['uid' => $userId]);
    $unread = (int)$stmt2->fetchColumn();

    echo json_encode(['unread' => $unread, 'items' => $items]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

?>
