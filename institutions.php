<?php
require_once 'core/auth_check.php';
$district_id = isset($_GET['district_id']) ? (int)$_GET['district_id'] : 0;
if (!$district_id) {
    header('Location: /rajon/districts.php');
    exit;
}
require_once 'config/database.php';
$stmt = $pdo->prepare("SELECT name FROM districts WHERE id = ?");
$stmt->execute([$district_id]);
$district = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$district) {
    header('Location: /rajon/districts.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Учреждения – <?= htmlspecialchars($district['name']) ?> – Район</title>
    <link rel="stylesheet" href="/rajon/css/style_institutions.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<script>
    const IS_ADMIN    = <?= json_encode($_SESSION['role'] === 'admin') ?>;
    const DISTRICT_ID = <?= $district_id ?>;
</script>
<div class="container">
    <h1>Учреждения — <?= htmlspecialchars($district['name']) ?></h1>
    <?php if ($_SESSION['role'] === 'admin'): ?>
    <button class="btn" onclick="openAddModal()">Добавить учреждение</button>
    <?php endif; ?>
    <div id="institutions-list" class="cards-grid"></div>
    <?php if ($_SESSION['role'] === 'admin'): ?>
    <dialog id="modal">
        <center><h2 id="modal-title">Добавить учреждение</h2></center>
        <br/>
        <hr>
        <br/>
        <form id="institution-form">
            <input type="hidden" id="institution-id">
            <label>Название *</label>
            <input type="text" id="institution-name" required>
            <label>Тип учреждения</label>
            <input type="text" id="institution-type">
            <label>Адрес</label>
            <input type="text" id="institution-address">
            <label>Телефон</label>
            <input type="text" id="institution-phone">
            <label>Часы работы</label>
            <input type="text" id="institution-hours">
            <label>Фотография</label>
            <input type="file" id="institution-image" accept="image/*">
            <div class="form-actions">
                <button type="submit" class="btn">Сохранить</button>
                <button type="button" class="btn-cancel" onclick="closeModal()">Отмена</button>
            </div>
        </form>
    </dialog>
    <?php endif; ?>
</div>
<script src="/rajon/js/institutions.js"></script>
</body>
</html>