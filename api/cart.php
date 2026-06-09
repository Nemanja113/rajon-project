<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
require_once '../config/database.php';
$method  = $_SERVER['REQUEST_METHOD'];
$user_id = $_SESSION['user_id'];
if ($method === 'GET') {
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$user_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $item_id = isset($data['item_id']) ? (int)$data['item_id'] : 0;
    $type    = $data['type'] ?? 'apartment'; 
    $title   = $data['title'] ?? 'Квартира';
    $price   = isset($data['price']) ? (float)$data['price'] : 0;
    $days    = isset($data['days']) ? (int)$data['days'] : 1;
    $total   = $price * $days;
    try {
        $stmt = $pdo->prepare("
            INSERT INTO cart (user_id, item_id, type, title, price, days, quantity, total) 
            VALUES (?, ?, ?, ?, ?, ?, 1, ?)
        ");
        $stmt->execute([$user_id, $item_id, $type, $title, $price, $days, $total]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Greška u bazi: ' . $e->getMessage()]);
    }
    exit;
}
if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    echo json_encode(['success' => true]);
    exit;
}
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);