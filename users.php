<?php 
require_once 'core/auth_check.php'; 
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /rajon/index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Пользователи – Район</title>
    <link rel="stylesheet" href="/rajon/css/style_users.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<script>
    const IS_ADMIN = <?= json_encode($_SESSION['role'] === 'admin') ?>;
</script>
<div class="container">
    <h1>Пользователи</h1>
    <?php if ($_SESSION['role'] === 'admin'): ?>
    <button class="btn" onclick="openAddModal()">Добавить пользователя</button>
    <?php endif; ?>
    <div id="users-list" class="cards-grid"></div>
    <?php if ($_SESSION['role'] === 'admin'): ?>
    <dialog id="modal">
        <center><h2 id="modal-title">Добавить пользователя</h2></center>
        <br/>
        <hr/>
        <br/>
        <form id="user-form">
            <input type="hidden" id="user-id">
            <div class="form-group">
                <label>Имя *</label>
                <input type="text" id="user-name" required>
            </div>
            <div class="form-group">
                <label>Фамилия *</label>
                <input type="text" id="user-surname" required>
            </div>
            <div class="form-group">
                <label>Username *</label>
                <input type="text" id="user-username" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" id="user-email" required>
            </div>
            <div class="form-group">
                <label>Телефон</label>
                <input type="text" id="user-phone" placeholder="+7 (999) 000-00-00">
            </div>
            <div class="form-group">
                <label>Пароль *</label>
                <input type="password" id="user-password" required placeholder="Введите пароль">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn">Сохранить</button>
                <button type="button" class="btn-cancel" onclick="closeModal()">Отмена</button>
            </div>
        </form>
    </dialog>
    <?php endif; ?>
</div>
<script src="/rajon/js/users.js"></script>
</body>
</html>