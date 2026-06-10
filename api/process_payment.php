<?php
require_once __DIR__ . '/../config/database.php';
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Не авторизован']);
    exit;
}
$userId = $_SESSION['user_id'];
try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($items)) {
        throw new Exception('Корзина пуста');
    }
    foreach ($items as $item) {
        if ($item['type'] === 'apartment') {
            $start_date = date('Y-m-d');
            $end_date = date('Y-m-d', strtotime('+1 day'));
            $stmt = $pdo->prepare("INSERT INTO apartment_reservations 
                (user_id, apartment_id, start_date, end_date, total_price, status) 
                VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$userId, $item['item_id'], $start_date, $end_date, $item['price']]);
        }
    }
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);
    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>