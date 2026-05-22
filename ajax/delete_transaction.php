<?php
require '../includes/db.php';
require '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false]);
    exit;
}

$id = (int)$_POST['id'];
$user_id = $_SESSION['user_id'];

try {
    // Security: ensure the user can only delete THEIR OWN transactions
    $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
    $success = $stmt->execute([$id, $user_id]);
    
    echo json_encode(['success' => $success]);
} catch (PDOException $e) {
    echo json_encode(['success' => false]);
}
?>