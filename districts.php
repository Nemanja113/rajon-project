<?php require_once 'core/auth_check.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Районы – Район</title>
    <link rel="stylesheet" href="/rajon/css/style_districts.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<script>
    const IS_ADMIN = <?= json_encode($_SESSION['role'] === 'admin') ?>;
</script>
<div class="container">
    <h1>Районы</h1>
    <?php if ($_SESSION['role'] === 'admin'): ?>
    <button class="btn" onclick="openAddModal()">Добавить район</button>
    <?php endif; ?>
    <div id="districts-list" class="cards-grid"></div>

    <?php if ($_SESSION['role'] === 'admin'): ?>
    <dialog id="modal">
        <center><h2 id="modal-title">Добавить район</h2></center>
        <br/>
        <hr/>
        <br/>
        <form id="district-form">
            <input type="hidden" id="district-id">
            <label>Название *</label>
            <input type="text" id="district-name-input" required>
            <label>Описание</label>
            <textarea id="district-description"></textarea>
            <label>Площадь (км²)</label>
            <input type="number" id="district-area" step="0.01">
            <label>Население</label>
            <input type="number" id="district-population">
            <label>Год основания</label>
            <input type="number" id="district-founded">
            <label>Фотография</label>
            <input type="file" id="district-image" accept="image/*">
            <div class="form-actions">
                <button type="submit" class="btn">Сохранить</button>
                <button type="button" class="btn-cancel" onclick="closeModal()">Отмена</button>
            </div>
        </form>
    </dialog>
    <?php endif; ?>
</div>
<script src="/rajon/js/districts.js"></script>
</body>
</html>