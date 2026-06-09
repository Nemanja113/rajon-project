<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
 
require_once '../config/database.php';
 
$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;
$district_id = $_GET['district_id'] ?? null;
 
if ($method === 'GET' && !$id) {
    if ($district_id) {
        $stmt = $pdo->prepare("SELECT * FROM streets WHERE district_id = ? ORDER BY id");
        $stmt->execute([$district_id]);
    } else {
        $stmt = $pdo->query("SELECT * FROM streets ORDER BY id");
    }
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}
 
if ($method === 'GET' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM streets WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) { http_response_code(404); echo json_encode(['error' => 'Not found']); exit; }
    echo json_encode($row);
    exit;
}
 
if ($method === 'POST') {
    if ($_SESSION['role'] !== 'admin') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit; }
 
    $name         = $_POST['name']         ?? '';
    $postal_code  = ($_POST['postal_code']  ?? '') !== '' ? $_POST['postal_code']  : null;
    $built_year   = ($_POST['built_year']   ?? '') !== '' ? (int)$_POST['built_year']   : null;
    $length_km    = ($_POST['length_km']    ?? '') !== '' ? (float)$_POST['length_km']  : null;
    $surface_type = ($_POST['surface_type'] ?? '') !== '' ? $_POST['surface_type'] : null;
    
    if (!$name) { http_response_code(400); echo json_encode(['error' => 'Name is required']); exit; }
     if ($id) {
        $stmt = $pdo->prepare("SELECT image FROM streets WHERE id = ?");
        $stmt->execute([$id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing) $image = $existing['image'];
 
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $ext      = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('street_') . '.' . $ext;
            $uploadDir= '../uploads/streets/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename);
            $image = $filename;
        }
 
        $stmt = $pdo->prepare(
            "UPDATE streets SET name=?, postal_code=?, built_year=?, length_km=?, surface_type=?, image=? WHERE id=?"
        );
        $stmt->execute([$name, $postal_code, $built_year, $length_km, $surface_type, $image, $id]);
 
        $stmt2 = $pdo->prepare("SELECT * FROM streets WHERE id = ?");
        $stmt2->execute([$id]);
        echo json_encode($stmt2->fetch(PDO::FETCH_ASSOC));
        exit; // ← BUG FIX: nedostajao exit, kod je padao na 405
    }
 
    $post_district_id = $_POST['district_id'] ?? null;
    if (!$post_district_id) { http_response_code(400); echo json_encode(['error' => 'District ID is required']); exit; }
 
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $ext      = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('street_') . '.' . $ext;
        $uploadDir= '../uploads/streets/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename);
        $image = $filename;
    }
 
    $stmt = $pdo->prepare(
        "INSERT INTO streets (name, district_id, postal_code, built_year, length_km, surface_type, image)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$name, $post_district_id, $postal_code, $built_year, $length_km, $surface_type, $image]);
    $newId = $pdo->lastInsertId();
 
    $stmt2 = $pdo->prepare("SELECT * FROM streets WHERE id = ?");
    $stmt2->execute([$newId]);
    echo json_encode($stmt2->fetch(PDO::FETCH_ASSOC));
    exit;
}
 
if ($method === 'DELETE' && $id) {
    if ($_SESSION['role'] !== 'admin') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit; }
    $stmt = $pdo->prepare("DELETE FROM streets WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
}
 
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);