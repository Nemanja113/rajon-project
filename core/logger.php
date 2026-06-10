<?php
function writeLog($login, $action) {
    $dir = __DIR__ . '/../logs';
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    $file = $dir . '/auth.log';
    $time = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'неизвестно';
    $line = "$time | ip=$ip | login=$login | action=$action" . PHP_EOL;
    file_put_contents($file, $line, FILE_APPEND);
}