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
if ($method === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'PUT') {
    $method = 'PUT';
}
$id = $_GET['id'] ?? null; 
if ($method === 'GET' && !$id) { 
    $stmt = $pdo->query("SELECT * FROM districts ORDER BY id"); 
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)); 
    exit; 
} 
if ($method === 'GET' && $id) { 
    $stmt = $pdo->prepare("SELECT * FROM districts WHERE id = ?"); 
    $stmt->execute([$id]); 
    $row = $stmt->fetch(PDO::FETCH_ASSOC); 
    if (!$row) { http_response_code(404); echo json_encode(['error' => 'Not found']); 
exit; } 
    echo json_encode($row); 
    exit; 
} 
if ($method === 'POST') { 
    if ($_SESSION['role'] !== 'admin') { http_response_code(403); echo 
json_encode(['error' => 'Forbidden']); exit; } 
    $name = $_POST['name'] ?? ''; 
    $description = $_POST['description'] ?? null; 
    $area = $_POST['area'] ?? null; 
    $population = $_POST['population'] ?? null; 
    $founded_year = $_POST['founded_year'] ?? null; 
    $image = null; 
    if (!$name) { http_response_code(400); echo json_encode(['error' => 'Name is 
required']); exit; } 
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) { 
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION); 
        $filename = uniqid('district_') . '.' . $ext; 
        $uploadDir = '../uploads/districts/'; 
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true); 
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename); 
        $image = $filename; 
    } 
    $stmt = $pdo->prepare("INSERT INTO districts (name, description, area, 
population, founded_year, image) VALUES (?, ?, ?, ?, ?, ?) RETURNING *"); 
    $stmt->execute([$name, $description, $area, $population, $founded_year, 
$image]); 
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC)); 
    exit; 
} 
if ($method === 'PUT') {
    if ($_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Доступ запрещен']);
        exit;
    }
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID не указан']);
        exit;
    }
    $name         = trim($_POST['name'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $area         = $_POST['area']         !== '' ? $_POST['area']         : null;
    $population   = $_POST['population']   !== '' ? $_POST['population']   : null;
    $founded_year = $_POST['founded_year'] !== '' ? $_POST['founded_year'] : null;
    if (empty($name)) {
        http_response_code(400);
        echo json_encode(['error' => 'Название обязательно']);
        exit;
    }
    $image_sql = '';
    $params    = [$name, $description, $area, $population, $founded_year];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $stmt = $pdo->prepare("SELECT image FROM districts WHERE id = ?");
        $stmt->execute([$id]);
        $old = $stmt->fetch();
        if ($old && $old['image']) {
            $old_path = '../uploads/districts/' . $old['image'];
            if (file_exists($old_path)) unlink($old_path);
        }
        $ext        = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = uniqid() . '.' . $ext;
        $upload_dir = '../uploads/districts/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name);
        $image_sql = ', image = ?';
        $params[]  = $image_name;
    }
    $params[] = $id;
    $stmt = $pdo->prepare("UPDATE districts SET name = ?, description = ?, area = ?, population = ?, founded_year = ? {$image_sql}, updated_at = NOW() WHERE id = ?");
    $stmt->execute($params);

    echo json_encode(['success' => true]);
    exit;
}
if ($method === 'DELETE' && $id) { 
    if ($_SESSION['role'] !== 'admin') { http_response_code(403); echo 
json_encode(['error' => 'Forbidden']); exit; } 
    $stmt = $pdo->prepare("DELETE FROM districts WHERE id = ?"); 
    $stmt->execute([$id]); 
    echo json_encode(['success' => true]); 
    exit; 
} 
http_response_code(405); 
echo json_encode(['error' => 'Method not allowed']);