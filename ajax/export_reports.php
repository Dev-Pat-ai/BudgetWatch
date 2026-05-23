<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
requireLogin();
if (session_status() === PHP_SESSION_NONE) session_start();
$userId = $_SESSION['user_id'];

header('Content-Type: text/csv; charset=utf-8');
$filename = 'budgetwatch_reports_' . date('Ymd_His') . '.csv';
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
fputcsv($out, ['Date', 'Title', 'Category', 'Type', 'Amount']);

try {
    $stmt = $pdo->prepare("SELECT transaction_date, title, category, type, amount FROM transactions
        WHERE user_id = :uid
        ORDER BY transaction_date DESC");
    $stmt->execute(['uid' => $userId]);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($out, [$row['transaction_date'], $row['title'], $row['category'], $row['type'], $row['amount']]);
    }
} catch (Exception $e) {
    fputcsv($out, ['ERROR', $e->getMessage()]);
}

fclose($out);
exit;

?>
