<?php require_once 'core/auth_check.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Район</title>
    <link rel="stylesheet" href="/rajon/css/style_index.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="main-content">
    <div class="welcome-card">
        <h1>Добро пожаловать, <?= htmlspecialchars($_SESSION['username'] ?? 'Пользователь') ?>!</h1>
    </div>
</div>
</body>
</html>