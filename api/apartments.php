<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
require_once '../config/database.php';
$method      = $_SERVER['REQUEST_METHOD'];
$id          = $_GET['id'] ?? null;
$district_id = $_GET['district_id'] ?? null;
if ($method === 'GET' && !$id) {
    if ($district_id) {
        $stmt = $pdo->prepare("SELECT * FROM apartments WHERE district_id = ? ORDER BY id");
        $stmt->execute([$district_id]);
    } else {
        $stmt = $pdo->query("SELECT * FROM apartments ORDER BY id");
    }
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}
if ($method === 'GET' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM apartments WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) { http_response_code(404); echo json_encode(['error' => 'Not found']); exit; }
    echo json_encode($row);
    exit;
}
if ($method === 'POST') {
    if ($_SESSION['role'] !== 'admin') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit; }
    $floor         = ($_POST['floor']         ?? '') !== '' ? (int)$_POST['floor']             : null;
    $rooms         = ($_POST['rooms']         ?? '') !== '' ? (int)$_POST['rooms']             : null;
    $area_m2       = ($_POST['area_m2']       ?? '') !== '' ? (float)$_POST['area_m2']         : null;
    $price_per_day = ($_POST['price_per_day'] ?? '') !== '' ? (float)$_POST['price_per_day']   : null;
    $description   = ($_POST['description']   ?? '') !== '' ? $_POST['description']            : null;
    $image         = null;
    if (!$price_per_day) { http_response_code(400); echo json_encode(['error' => 'Price per day is required']); exit; }
    if ($id) {
        $stmt = $pdo->prepare("SELECT image FROM apartments WHERE id = ?");
        $stmt->execute([$id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing) $image = $existing['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $ext       = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename  = uniqid('apartment_') . '.' . $ext;
            $uploadDir = '../uploads/apartments/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename);
            $image = $filename;
        }
        $stmt = $pdo->prepare(
            "UPDATE apartments SET floor=?, rooms=?, area_m2=?, price_per_day=?, description=?, image=? WHERE id=?"
        );
        $stmt->execute([$floor, $rooms, $area_m2, $price_per_day, $description, $image, $id]);
        $stmt2 = $pdo->prepare("SELECT * FROM apartments WHERE id = ?");
        $stmt2->execute([$id]);
        echo json_encode($stmt2->fetch(PDO::FETCH_ASSOC));
        exit;
    }
    $post_district_id = ($_POST['district_id'] ?? '') !== '' ? (int)$_POST['district_id'] : null;
    if (!$post_district_id) { http_response_code(400); echo json_encode(['error' => 'District ID is required']); exit; }
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $ext       = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename  = uniqid('apartment_') . '.' . $ext;
        $uploadDir = '../uploads/apartments/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename);
        $image = $filename;
    }
    $stmt = $pdo->prepare(
        "INSERT INTO apartments (district_id, floor, rooms, area_m2, price_per_day, description, image)
        VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$post_district_id, $floor, $rooms, $area_m2, $price_per_day, $description, $image]);
    $newId = $pdo->lastInsertId();
    $stmt2 = $pdo->prepare("SELECT * FROM apartments WHERE id = ?");
    $stmt2->execute([$newId]);
    echo json_encode($stmt2->fetch(PDO::FETCH_ASSOC));
    exit;
}
if ($method === 'DELETE' && $id) {
    if ($_SESSION['role'] !== 'admin') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit; }
    $stmt = $pdo->prepare("DELETE FROM apartments WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
}
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);