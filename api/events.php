<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Не авторизован']);
    exit;
}
require_once '../config/database.php';
$method      = $_SERVER['REQUEST_METHOD'];
$id          = $_GET['id'] ?? null;
$district_id = $_GET['district_id'] ?? null;
if ($method === 'GET' && !$id) {
    if ($district_id) {
        $stmt = $pdo->prepare("SELECT * FROM events WHERE district_id = ? ORDER BY event_date DESC");
        $stmt->execute([$district_id]);
    } else {
        $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date DESC");
    }
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}
if ($method === 'GET' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) { http_response_code(404); echo json_encode(['error' => 'Не найдено']); exit; }
    echo json_encode($row);
    exit;
}
if ($method === 'POST') {
    if ($_SESSION['role'] !== 'admin') { http_response_code(403); echo json_encode(['error' => 'Запрещено']); exit; }
    $title          = $_POST['title']      ?? '';
    $event_date     = $_POST['event_date'] ?? '';
    $location       = ($_POST['location']       ?? '') !== '' ? $_POST['location']       : null;
    $organizer      = ($_POST['organizer']      ?? '') !== '' ? $_POST['organizer']      : null;
    $visitors_count = ($_POST['visitors_count'] ?? '') !== '' ? (int)$_POST['visitors_count'] : null;
    $description    = ($_POST['description']    ?? '') !== '' ? $_POST['description']    : null;
    $image          = null;
    if (!$title || !$event_date) {
        http_response_code(400);
        echo json_encode(['error' => 'Название и дата обязательны']);
        exit;
    }
    if ($id) {
        $stmt = $pdo->prepare("SELECT image FROM events WHERE id = ?");
        $stmt->execute([$id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing) $image = $existing['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $ext      = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('event_') . '.' . $ext;
            $uploadDir= '../uploads/events/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename);
            $image = $filename;
        }
        $stmt = $pdo->prepare(
            "UPDATE events SET title=?, event_date=?, location=?, organizer=?, visitors_count=?, description=?, image=? WHERE id=?"
        );
        $stmt->execute([$title, $event_date, $location, $organizer, $visitors_count, $description, $image, $id]);
        $stmt2 = $pdo->prepare("SELECT * FROM events WHERE id = ?");
        $stmt2->execute([$id]);
        echo json_encode($stmt2->fetch(PDO::FETCH_ASSOC));
        exit;
    }
    $post_district_id = ($_POST['district_id'] ?? '') !== '' ? (int)$_POST['district_id'] : null;
    if (!$post_district_id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID района обязателен']);
        exit;
    }
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $ext      = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('event_') . '.' . $ext;
        $uploadDir= '../uploads/events/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename);
        $image = $filename;
    }
    $stmt = $pdo->prepare(
        "INSERT INTO events (title, event_date, district_id, location, organizer, visitors_count, description, image)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?) RETURNING *"
    );
    $stmt->execute([$title, $event_date, $post_district_id, $location, $organizer, $visitors_count, $description, $image]);
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    exit;
}
if ($method === 'DELETE' && $id) {
    if ($_SESSION['role'] !== 'admin') { http_response_code(403); echo json_encode(['error' => 'Запрещено']); exit; }
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
}
http_response_code(405);
echo json_encode(['error' => 'Метод не поддерживается']);