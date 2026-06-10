<?php
if (session_status() === PHP_SESSION_NONE) {
    if (!is_writable(ini_get('session.save_path'))) {
        session_save_path(sys_get_temp_dir());
    }
    session_start();
}
if (isset($_SESSION['user_id'])) {
    header('Location: /rajon/index.php');
    exit;
}
require_once 'config/database.php';
require_once 'core/logger.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    if ($username && $name && $surname && $email && $password) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Неверный формат email';
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = 'Пользователь с таким логином уже существует';
            } else {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = 'Пользователь с таким email уже существует';
                } else {
                    try {
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("INSERT INTO users (username, name, surname, email, phone, password_hash, role) VALUES (?, ?, ?, ?, ?, ?, 'user')");
                        $stmt->execute([$username, $name, $surname, $email, $phone, $hash]);
                        writeLog($username, 'REGISTER');
                        header('Location: /rajon/login.php');
                        exit;
                    } catch (PDOException $e) {
                        if ($e->getCode() == '23505') {
                            $error = 'Логин или Email уже заняты';
                        } else {
                            $error = 'Ошибка базы данных: ' . $e->getMessage();
                        }
                    }
                }
            }
        }
    } else {
        $error = 'Заполните все обязательные поля';
    }
}
?>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация – Район</title>
    <link rel="stylesheet" href="/rajon/css/style_register.css">
</head>
<body>
<div class="auth-container">
    <h2>Регистрация</h2>
    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST">
        <label>Логин *</label>
        <input type="text" name="username" required>
        <label>Имя *</label>
        <input type="text" name="name" required>
        <label>Фамилия *</label>
        <input type="text" name="surname" required>
        <label>Email *</label>
        <input type="email" name="email" required>
        <label>Телефон</label>
        <input type="text" name="phone">
        <label>Пароль *</label>
        <input type="password" name="password" required>
        <button type="submit">Создать аккаунт</button>
    </form>
    <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
</div>
</body>
</html>