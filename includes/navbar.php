<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentDistrict = $_GET['rajon'] ?? '';
$brandName = !empty($currentDistrict) ? htmlspecialchars($currentDistrict) : 'Арбат';
?>
<link rel="stylesheet" href="/rajon/css/style_navbar.css">
<nav class="navbar">
    <div class="navbar-brand">
        <a href="/rajon/districts.php"><?php echo mb_convert_case($brandName, MB_CASE_TITLE, "UTF-8"); ?></a>
    </div>
    <div class="navbar-links">
        <div class="nav-item">
            <a href="/rajon/index.php">Главная</a>
        </div>
        <div class="dropdown">
            <a class="dropbtn">Район</a>
            <div class="dropdown-menu">
                <a href="/rajon/districts.php?rajon=Арбат">Арбат</a>
            </div>
        </div>
        <div class="dropdown">
            <a href="/rajon/streets.php?district_id=5">Улицы</a>
            <div class="dropdown-menu" id="streets-dropdown">
                <a href="/rajon/streets.php?district_id=5">Все улицы</a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="/rajon/streets.php?district_id=5&add=1" class="dropdown-add">Добавить улицу</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="dropdown">
            <a href="/rajon/institutions.php?district_id=5">Учреждения</a>
            <div class="dropdown-menu" id="institutions-dropdown">
                <a href="/rajon/institutions.php?district_id=5">Все учреждения</a>
                <div id="institutions-list-dropdown"></div>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="/rajon/institutions.php?district_id=5&add=1" class="dropdown-add">Добавить учреждение</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="dropdown">
            <a href="/rajon/events.php?district_id=5">События</a>
            <div class="dropdown-menu" id="events-dropdown">
                <a href="/rajon/events.php?district_id=5">Все события</a>
                <div id="events-list-dropdown"></div>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="/rajon/events.php?district_id=5&add=1" class="dropdown-add">Добавить событие</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="dropdown">
            <a href="/rajon/apartments.php?district_id=5">Квартиры</a>
            <div class="dropdown-menu" id="apartments-dropdown">
                <a href="/rajon/apartments.php?district_id=5">Все квартиры</a>
                <div id="apartments-list-dropdown"></div>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="/rajon/apartments.php?district_id=5&add=1" class="dropdown-add">Добавить квартиру</a>
                <?php endif; ?>
            </div>
        </div>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <div class="nav-item">
            <a href="/rajon/users.php">Пользователи</a>
        </div>
        <?php endif; ?>
    </div>
    <div class="navbar-actions">
        <a href="/rajon/cart.php" class="cart-link" title="Корзина">
            🛒 <span id="cart-badge" class="cart-badge">0</span>
        </a>
        <a href="/rajon/logout.php" class="btn-logout">Выйти</a>
    </div>
</nav>
<script>
async function updateCartBadge() {
    try {
        const response = await fetch('/rajon/api/cart.php');
        const items = await response.json();
        const badge = document.getElementById('cart-badge');
        if (items && items.length > 0) {
            badge.textContent = items.length;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    } catch (error) {
        console.error('Ошибка при загрузке данных корзины:', error);
    }
}
document.addEventListener('DOMContentLoaded', updateCartBadge);
</script>