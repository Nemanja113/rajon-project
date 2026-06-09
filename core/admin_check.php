<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: /rajon/login.php');
    exit;
}
if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    die(json_encode(['error' => 'Forbidden']));
}