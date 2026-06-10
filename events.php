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
    <title>События – <?= htmlspecialchars($district['name']) ?> – Район</title>
    <link rel="stylesheet" href="/rajon/css/style_events.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<script>
    const IS_ADMIN    = <?= json_encode($_SESSION['role'] === 'admin') ?>;
    const DISTRICT_ID = <?= $district_id ?>;
</script>
<div class="container">
    <h1>События — <?= htmlspecialchars($district['name']) ?></h1>
    <?php if ($_SESSION['role'] === 'admin'): ?>
    <button class="btn" onclick="openAddModal()">Добавить событие</button>
    <?php endif; ?>
    <div id="events-list" class="cards-grid"></div>

    <?php if ($_SESSION['role'] === 'admin'): ?>
    <dialog id="modal">
        <center><h2 id="modal-title">Добавить событие</h2></center>
        <br/>
        <hr>
        <br/>
        <form id="event-form">
            <input type="hidden" id="event-id">
            <label>Название *</label>
            <input type="text" id="event-title" required>
            <label>Дата *</label>
            <input type="date" id="event-date" required>
            <label>Место проведения</label>
            <input type="text" id="event-location">
            <label>Организатор</label>
            <input type="text" id="event-organizer">
            <label>Кол-во посетителей</label>
            <input type="number" id="event-visitors">
            <label>Описание</label>
            <textarea id="event-description"></textarea>
            <label>Фотография</label>
            <input type="file" id="event-image" accept="image/*">
            <div class="form-actions">
                <button type="submit" class="btn">Сохранить</button>
                <button type="button" class="btn-cancel" onclick="closeModal()">Отмена</button>
            </div>
        </form>
    </dialog>
    <?php endif; ?>
</div>
<script src="/rajon/js/events.js"></script>
</body>
</html>