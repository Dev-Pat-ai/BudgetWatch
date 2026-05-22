<?php
require '../includes/db.php';
require '../includes/auth.php';
require '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$user_id = $_SESSION['user_id'];
$title = sanitizeInput($_POST['title']);
$amount = (float)$_POST['amount'];
$category = sanitizeInput($_POST['category']);
$type = $_POST['type'] === 'income' ? 'income' : 'expense';
$date = $_POST['date'];

if (empty($title) || $amount <= 0 || empty($date)) {
    echo json_encode(['success' => false, 'error' => 'Valid Title, Amount, and Date are required']);
    exit;
}

try {
    // Prepared Statement to Prevent SQL Injection
    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, title, amount, category, type, transaction_date) 
                           VALUES (?, ?, ?, ?, ?, ?)");
    $success = $stmt->execute([$user_id, $title, $amount, $category, $type, $date]);
    
    echo json_encode(['success' => $success]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>