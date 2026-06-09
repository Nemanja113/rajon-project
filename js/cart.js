document.addEventListener('DOMContentLoaded', loadCart);

async function loadCart() {
    try {
        const res = await fetch('/rajon/api/cart.php');
        const items = await res.json();
        const list = document.getElementById('cart-list');

        if (!list) return;

        if (!items || items.length === 0) {
            list.innerHTML = `
                <div class="cart-block empty-cart-block">
                    <p class="no-items">Ваша корзина пуста.</p>
                </div>
            `;
            return;
        }

        let grandTotal = 0;
        let rowsHtml = '';

        items.forEach(i => {
            grandTotal += parseFloat(i.total || 0);
            const detailsText = `${i.days} дн. х ${parseFloat(i.price).toFixed(2)} руб.`;

            rowsHtml += `
                <div class="cart-item-row" id="cart-item-${i.id}">
                    <span class="col-title-val">${escapeHtml(i.title)}</span>
                    <span class="col-type-val">🏠 Аренда квартиры</span>
                    <span class="col-details-val">${detailsText}</span>
                    <span class="col-total-val">${parseFloat(i.total).toFixed(2)} руб.</span>
                    <span class="col-actions-val">
                        <button class="btn-delete-row" data-id="${i.id}" title="Удалить">✕</button>
                    </span>
                </div>
            `;
        });

        list.innerHTML = `
            <div class="cart-block">
                <div class="cart-table-header">
                    <span class="col-title">Наименование</span>
                    <span class="col-type">Тип</span>
                    <span class="col-details">Детали</span>
                    <span class="col-total">Итого</span>
                    <span class="col-actions"></span>
                </div>
                
                <div class="cart-rows-wrapper">
                    ${rowsHtml}
                </div>
                
                <div class="cart-block-footer">
                    <div class="cart-total-inside">
                        <span>Общая сумма:</span>
                        <strong>${grandTotal.toFixed(2)} руб.</strong>
                    </div>
                    <button class="btn btn-pay" onclick="checkout()">Оплатить всё</button>
                </div>
            </div>
        `;

    } catch (error) {
        console.error('Ошибка при загрузке корзины:', error);
    }
}

document.getElementById('cart-list')?.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-delete-row');
    if (btn) removeFromCart(btn.dataset.id);
});

function escapeHtml(val) {
    if (val === null || val === undefined) return '';
    return String(val)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

async function removeFromCart(cartId) {
    if (!confirm('Удалить из корзины?')) return;
    try {
        await fetch('/rajon/api/cart.php?id=' + cartId, { method: 'DELETE' });
        loadCart();
        if (typeof updateCartBadge === 'function') updateCartBadge();
    } catch (error) {
        console.error('Ошибка при удалении:', error);
    }
}

async function checkout() {
    if (!confirm('Оплатить все товары в корзине?')) return;
    const list = document.getElementById('cart-list');
    list.innerHTML = '<p class="no-items">Обработка заказа...</p>';

    try {
        const res = await fetch('/rajon/api/process_payment.php', { method: 'POST' });
        const data = await res.json();

        if (data.success) {
            list.innerHTML = `
                <div class="cart-block" style="text-align:center; padding:40px;">
                    <h2 style="color:#38a169; margin-bottom:14px;">✅ Спасибо за покупку!</h2>
                    <p style="color:#cbd5e1; font-size:16px;">Ваш заказ успешно оформлен.</p>
                </div>
            `;
            if (typeof updateCartBadge === 'function') updateCartBadge();
        } else {
            list.innerHTML = '<p class="no-items" style="color:#ef4444;">Ошибка при оплате. Попробуйте снова.</p>';
            setTimeout(loadCart, 2000);
        }
    } catch (error) {
        console.error('Ошибка оплаты:', error);
        loadCart();
    }
}