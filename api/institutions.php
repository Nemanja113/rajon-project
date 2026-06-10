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
        $stmt = $pdo->prepare("SELECT * FROM institutions WHERE district_id = ? ORDER BY id");
        $stmt->execute([$district_id]);
    } else {
        $stmt = $pdo->query("SELECT * FROM institutions ORDER BY id");
    }
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}
if ($method === 'GET' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM institutions WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) { http_response_code(404); echo json_encode(['error' => 'Не найдено']); exit; }
    echo json_encode($row);
    exit;
}
if ($method === 'POST') {
    if ($_SESSION['role'] !== 'admin') { http_response_code(403); echo json_encode(['error' => 'Запрещено']); exit; }
    $name             = $_POST['name']             ?? '';
    $institution_type = ($_POST['institution_type'] ?? '') !== '' ? $_POST['institution_type'] : null;
    $address          = ($_POST['address']          ?? '') !== '' ? $_POST['address']          : null;
    $phone            = ($_POST['phone']            ?? '') !== '' ? $_POST['phone']            : null;
    $working_hours    = ($_POST['working_hours']    ?? '') !== '' ? $_POST['working_hours']    : null;
    $image            = null;
    if (!$name) { http_response_code(400); echo json_encode(['error' => 'Название обязательно']); exit; }
    if ($id) {
        $stmt = $pdo->prepare("SELECT image FROM institutions WHERE id = ?");
        $stmt->execute([$id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing) $image = $existing['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $ext      = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('institution_') . '.' . $ext;
            $uploadDir= '../uploads/institutions/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename);
            $image = $filename;
        }
        $stmt = $pdo->prepare(
            "UPDATE institutions SET name=?, institution_type=?, address=?, phone=?, working_hours=?, image=? WHERE id=?"
        );
        $stmt->execute([$name, $institution_type, $address, $phone, $working_hours, $image, $id]);
        $stmt2 = $pdo->prepare("SELECT * FROM institutions WHERE id = ?");
        $stmt2->execute([$id]);
        echo json_encode($stmt2->fetch(PDO::FETCH_ASSOC));
        exit;
    }
    $post_district_id = ($_POST['district_id'] ?? '') !== '' ? (int)$_POST['district_id'] : null;
    if (!$post_district_id) { http_response_code(400); echo json_encode(['error' => 'ID района обязателен']); exit; }
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $ext      = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('institution_') . '.' . $ext;
        $uploadDir= '../uploads/institutions/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename);
        $image = $filename;
    }
    $stmt = $pdo->prepare(
        "INSERT INTO institutions (name, district_id, institution_type, address, phone, working_hours, image)
         VALUES (?, ?, ?, ?, ?, ?, ?) RETURNING *"
    );
    $stmt->execute([$name, $post_district_id, $institution_type, $address, $phone, $working_hours, $image]);
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    exit;
}
if ($method === 'DELETE' && $id) {
    if ($_SESSION['role'] !== 'admin') { http_response_code(403); echo json_encode(['error' => 'Запрещено']); exit; }
    $stmt = $pdo->prepare("DELETE FROM institutions WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
}
http_response_code(405);
echo json_encode(['error' => 'Метод не поддерживается']);