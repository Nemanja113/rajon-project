<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'core/logger.php';
$username = $_SESSION['username'] ?? 'unknown';
writeLog($username, 'LOGOUT');
session_destroy();
header('Location: /rajon/login.php');
exit;