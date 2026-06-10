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
    <title>Квартиры – <?= htmlspecialchars($district['name']) ?> – Район</title>
    <link rel="stylesheet" href="/rajon/css/style_apartments.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<script>
    const IS_ADMIN    = <?= json_encode($_SESSION['role'] === 'admin') ?>;
    const DISTRICT_ID = <?= $district_id ?>;
</script>
<div class="container">
    <h1>Квартиры — <?= htmlspecialchars($district['name']) ?></h1>
    <?php if ($_SESSION['role'] === 'admin'): ?>
    <button class="btn" onclick="openAddModal()">Добавить квартиру</button>
    <?php endif; ?>
    <div id="apartments-list" class="cards-grid"></div>
    <?php if ($_SESSION['role'] === 'admin'): ?>
    <dialog id="modal">
        <center><h2 id="modal-title">Добавить квартиру</h2></center>
        <hr>
        <form id="apartment-form">
            <input type="hidden" id="apartment-id">
            <label>Этаж</label>
            <input type="number" id="apartment-floor">
            <label>Количество комнат</label>
            <input type="number" id="apartment-rooms">
            <label>Площадь (м²)</label>
            <input type="number" id="apartment-area" step="0.01">
            <label>Цена за день *</label>
            <input type="number" id="apartment-price" step="0.01" required>
            <label>Описание</label>
            <textarea id="apartment-description"></textarea>
            <label>Фотография</label>
            <input type="file" id="apartment-image" accept="image/*">
            <div class="form-actions">
                <button type="submit" class="btn">Сохранить</button>
                <button type="button" class="btn-cancel" onclick="closeModal()">Отмена</button>
            </div>
        </form>
    </dialog>
    <?php endif; ?>
</div>
<dialog id="reservation-modal">
        <center><h2>Бронирование квартиры</h2></center>
        <hr>
        <form id="reservation-form">
            <input type="hidden" id="reserve-apartment-id">
            <input type="hidden" id="reserve-apartment-price">
            <input type="hidden" id="reserve-apartment-title">
            <label>Количество дней аренды:</label>
            <input type="number" id="reserve-days" min="1" value="1" required>
            <div class="form-actions">
                <button type="submit" class="btn">В корзину</button>
                <button type="button" class="btn-cancel" onclick="closeReservationModal()">Отмена</button>
            </div>
        </form>
    </dialog>
<script src="/rajon/js/apartments.js"></script>
</body>
</html>