<?php
header('Content-Type: application/json');
require_once '../config/database.php'; 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Доступ запрещен (Необходимы права администратора)']);
    exit;
}
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
    $stmt = $pdo->query("SELECT id, username, name, surname, email, phone, role FROM users ORDER BY created_at DESC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['username']) || empty($data['name']) || empty($data['surname']) || empty($data['email']) || empty($data['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Обязательные поля не заполнены']);
        exit;
    }
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, name, surname, email, phone, password_hash, role) VALUES (:username, :name, :surname, :email, :phone, :password_hash, 'user')");
        $stmt->execute([
            'username'      => $data['username'],
            'name'          => $data['name'],
            'surname'       => $data['surname'],
            'email'         => $data['email'],
            'phone'         => $data['phone'] ?? null,
            'password_hash' => $hashedPassword
        ]);
        http_response_code(201);
        echo json_encode(['message' => 'Пользователь успешно создан']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Ошибка базы данных: ' . $e->getMessage()]);
    }
    exit;
}
if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Отсутствует ID пользователя']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);

        http_response_code(200);
        echo json_encode(['message' => 'Пользователь успешно удален']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Ошибка базы данных при удалении: ' . $e->getMessage()]);
    }
    exit;
}
http_response_code(405);
echo json_encode(['error' => 'Метод не поддерживается']);
exit;
?>