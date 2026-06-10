document.addEventListener('DOMContentLoaded', function() {
    loadApartments(DISTRICT_ID);
    if (IS_ADMIN && window.location.search.includes('add=1')) {
        openAddModal();
    }
    document.getElementById('apartments-list').addEventListener('click', function(e) {
        const editBtn = e.target.closest('.btn-edit');
        if (editBtn) {
            openEditModal(
                editBtn.dataset.id,
                editBtn.dataset.floor,
                editBtn.dataset.rooms,
                editBtn.dataset.area,
                editBtn.dataset.price,
                editBtn.dataset.description
            );
        }
        const deleteBtn = e.target.closest('.btn-delete');
        if (deleteBtn) {
            deleteApartment(deleteBtn.dataset.id);
        }
        const reserveBtn = e.target.closest('.btn-reserve');
        if (reserveBtn) {
            openReservationModal(
                reserveBtn.dataset.id,
                reserveBtn.dataset.price,
                reserveBtn.dataset.rooms
            );
        }
    });
});
async function loadApartments(districtId) {
    try {
        const res = await fetch(`/rajon/api/apartments.php?district_id=${districtId}`);
        const apartments = await res.json();
        const list = document.getElementById('apartments-list');
        list.innerHTML = '';
        if (!apartments || apartments.length === 0) {
            list.innerHTML = '<p class="no-items">Квартир пока нет</p>';
            return;
        }
        apartments.forEach(a => {
            const card = document.createElement('div');
            card.className = 'street-card';
            card.innerHTML = `
            <div class="street-card-header">
                <h3>Квартира: ${escapeHtml(a.rooms)} комнат</h3>
            </div>
            ${a.image ? `<img src="/rajon/uploads/apartments/${a.image}" class="card-img" alt="">` : ''}
            <div class="street-card-body">
                <p><strong>Этаж:</strong> ${escapeHtml(a.floor) || '–'}</p>
                <p><strong>Площадь:</strong> ${escapeHtml(a.area_m2) || '–'} м²</p>
                <p><strong>Цена за день:</strong> ${escapeHtml(a.price_per_day) || '–'} руб.</p>
                <p><strong>Статус:</strong> ${a.is_available ? 'Свободна' : 'Занята'}</p>
                <p><strong>Описание:</strong> ${escapeHtml(a.description) || '–'}</p>
            </div>
            <div class="street-card-actions">
                <button class="btn btn-reserve" 
                        data-id="${a.id}" 
                        data-price="${a.price_per_day}" 
                        data-rooms="${a.rooms}">📅 Забронировать</button>
                ${IS_ADMIN ? `
                <div class="right-buttons">
                    <button class="btn-edit"
                        data-id="${a.id}"
                        data-floor="${escapeAttr(a.floor)}"
                        data-rooms="${escapeAttr(a.rooms)}"
                        data-area="${escapeAttr(a.area_m2)}"
                        data-price="${escapeAttr(a.price_per_day)}"
                        data-description="${escapeAttr(a.description)}">Изменить</button>
                    <button class="btn-delete" data-id="${a.id}">Удалить</button>
                </div>
                ` : ''}
            </div>
        `;
            list.appendChild(card);
        });
    } catch (err) {
        console.error("Ошибка при загрузке квартир:", err);
    }
}
function escapeHtml(val) {
    if (val === null || val === undefined) return '';
    return String(val)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
function escapeAttr(val) {
    if (val === null || val === undefined) return '';
    return String(val).replace(/"/g, '&quot;');
}
function openAddModal() {
    document.getElementById('modal-title').textContent = 'Добавить квартиру';
    document.getElementById('apartment-form').reset();
    document.getElementById('apartment-id').value = '';
    document.getElementById('modal').showModal();
}
function openEditModal(id, floor, rooms, area, price, description) {
    document.getElementById('modal-title').textContent = 'Изменить квартиру';
    document.getElementById('apartment-id').value          = id;
    document.getElementById('apartment-floor').value       = floor;
    document.getElementById('apartment-rooms').value       = rooms;
    document.getElementById('apartment-area').value        = area;
    document.getElementById('apartment-price').value       = price;
    document.getElementById('apartment-description').value = description;
    document.getElementById('modal').showModal();
}
function closeModal() {
    document.getElementById('modal').close();
}
function openReservationModal(apartmentId, price, rooms) {
    document.getElementById('reserve-apartment-id').value = apartmentId;
    document.getElementById('reserve-apartment-price').value = price;
    document.getElementById('reserve-apartment-title').value = `Квартира: ${rooms} комнат`;
    document.getElementById('reserve-days').value = 1;
    document.getElementById('reservation-modal').showModal();
}
function closeReservationModal() {
    document.getElementById('reservation-modal').close();
}
document.getElementById('apartment-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('apartment-id').value;
    const formData = new FormData();
    formData.append('district_id',   DISTRICT_ID);
    formData.append('floor',         document.getElementById('apartment-floor').value);
    formData.append('rooms',         document.getElementById('apartment-rooms').value);
    formData.append('area_m2',       document.getElementById('apartment-area').value);
    formData.append('price_per_day', document.getElementById('apartment-price').value);
    formData.append('description',   document.getElementById('apartment-description').value);
    const imageFile = document.getElementById('apartment-image').files[0];
    if (imageFile) formData.append('image', imageFile);
    try {
        const res = await fetch('/rajon/api/apartments.php' + (id ? '?id=' + id : ''), {
            method: 'POST',
            body: formData
        });
        const result = await res.json();
        if (res.ok) {
            closeModal();
            loadApartments(DISTRICT_ID);
        } else {
            alert("Ошибка: " + (result.error || 'Неизвестная ошибка'));
        }
    } catch (error) {
        console.error("Ошибка при сохранении:", error);
        alert("Системная ошибка, проверьте консоль.");
    }
});
document.getElementById('reservation-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const itemId = document.getElementById('reserve-apartment-id').value;
    const price = document.getElementById('reserve-apartment-price').value;
    const title = document.getElementById('reserve-apartment-title').value;
    const days = document.getElementById('reserve-days').value;
    const cartData = {
        item_id: parseInt(itemId),
        type: 'apartment',
        title: title,
        price: parseFloat(price),
        days: parseInt(days),
        quantity: 1
    };
    try {
        const res = await fetch('/rajon/api/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(cartData)
        });
        const result = await res.json();
        if (res.ok && result.success) {
            alert('Квартира успешно добавлена в корзину!');
            closeReservationModal();
            if (typeof updateCartBadge === 'function') updateCartBadge();
        } else {
            alert('Ошибка: ' + (result.error || 'Не удалось добавить в корзину'));
        }
    } catch (error) {
        console.error('Ошибка при добавлении в корзину:', error);
        alert('Системная ошибка при добавлении в корзину.');
    }
});
async function deleteApartment(id) {
    if (!confirm('Вы уверены, что хотите удалить эту квартиру?')) return;
    try {
        const res = await fetch('/rajon/api/apartments.php?id=' + id, {
            method: 'DELETE'
        });
        if (res.ok) {
            loadApartments(DISTRICT_ID);
        } else {
            alert('Не удалось удалить квартиру.');
        }
    } catch (error) {
        console.error('Ошибка при удалении квартиры:', error);
        alert('Системная ошибка при удалении.');
    }
}