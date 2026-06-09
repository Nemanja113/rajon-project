document.addEventListener('DOMContentLoaded', function() {
    loadEvents(DISTRICT_ID);

    if (IS_ADMIN && window.location.search.includes('add=1')) {
        openAddModal();
    }

    document.getElementById('events-list').addEventListener('click', function(e) {
        const editBtn = e.target.closest('.btn-edit');
        if (editBtn) {
            openEditModal(
                editBtn.dataset.id,
                editBtn.dataset.title,
                editBtn.dataset.date,
                editBtn.dataset.location,
                editBtn.dataset.organizer,
                editBtn.dataset.visitors,
                editBtn.dataset.description
            );
        }
        const deleteBtn = e.target.closest('.btn-delete');
        if (deleteBtn) {
            deleteEvent(deleteBtn.dataset.id);
        }
    });
});

async function loadEvents(districtId) {
    try {
        const res = await fetch(`/rajon/api/events.php?district_id=${districtId}`);
        const events = await res.json();
        const list = document.getElementById('events-list');
        list.innerHTML = '';

        if (!events || events.length === 0) {
            list.innerHTML = '<p class="no-items">Событий пока нет</p>';
            return;
        }

        events.forEach(e => {
            const card = document.createElement('div');
            card.className = 'street-card';
            card.innerHTML = `
                <div class="street-card-header">
                    <h3>${escapeHtml(e.title)}</h3>
                </div>
                ${e.image ? `<img src="/rajon/uploads/events/${e.image}" class="card-img" alt="">` : ''}
                <div class="street-card-body">
                    <p><strong>Дата:</strong> ${e.event_date || '–'}</p>
                    <p><strong>Место:</strong> ${escapeHtml(e.location) || '–'}</p>
                    <p><strong>Организатор:</strong> ${escapeHtml(e.organizer) || '–'}</p>
                    <p><strong>Посетителей:</strong> ${e.visitors_count || '–'}</p>
                    ${e.description ? `<p>${escapeHtml(e.description)}</p>` : ''}
                </div>
                <div class="street-card-actions">
                    ${IS_ADMIN ? `
                    <button class="btn-edit"
                        data-id="${e.id}"
                        data-title="${escapeAttr(e.title)}"
                        data-date="${escapeAttr(e.event_date)}"
                        data-location="${escapeAttr(e.location)}"
                        data-organizer="${escapeAttr(e.organizer)}"
                        data-visitors="${escapeAttr(e.visitors_count)}"
                        data-description="${escapeAttr(e.description)}">Изменить</button>
                    <button class="btn-delete" data-id="${e.id}">Удалить</button>
                    ` : '<span style="color:#94a3b8; font-size:14px;">Только просмотр</span>'}
                </div>
            `;
            list.appendChild(card);
        });
    } catch (err) {
        console.error("Greška pri učitavanju događaja:", err);
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
    document.getElementById('modal-title').textContent = 'Добавить событие';
    document.getElementById('event-form').reset();
    document.getElementById('event-id').value = '';
    document.getElementById('modal').showModal();
}

function openEditModal(id, title, date, location, organizer, visitors, description) {
    document.getElementById('modal-title').textContent = 'Изменить событие';
    document.getElementById('event-id').value          = id;
    document.getElementById('event-title').value       = title;
    document.getElementById('event-date').value        = date;
    document.getElementById('event-location').value    = location;
    document.getElementById('event-organizer').value   = organizer;
    document.getElementById('event-visitors').value    = visitors;
    document.getElementById('event-description').value = description;
    document.getElementById('modal').showModal();
}

function closeModal() {
    document.getElementById('modal').close();
}

document.getElementById('event-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const id = document.getElementById('event-id').value;

    const formData = new FormData();
    formData.append('title',          document.getElementById('event-title').value);
    formData.append('event_date',     document.getElementById('event-date').value);
    formData.append('district_id',    DISTRICT_ID);
    formData.append('location',       document.getElementById('event-location').value);
    formData.append('organizer',      document.getElementById('event-organizer').value);
    formData.append('visitors_count', document.getElementById('event-visitors').value);
    formData.append('description',    document.getElementById('event-description').value);

    const imageFile = document.getElementById('event-image').files[0];
    if (imageFile) formData.append('image', imageFile);

    try {
        const res = await fetch('/rajon/api/events.php' + (id ? '?id=' + id : ''), {
            method: 'POST',
            body: formData
        });

        const result = await res.json();

        if (res.ok) {
            closeModal();
            loadEvents(DISTRICT_ID);
        } else {
            alert("Ошибка: " + (result.error || 'Неизвестная ошибка'));
        }
    } catch (error) {
        console.error("Greška:", error);
        alert("Системная ошибка, проверьте консоль.");
    }
});

async function deleteEvent(id) {
    if (!confirm('Удалить событие?')) return;
    const res = await fetch('/rajon/api/events.php?id=' + id, {
        method: 'DELETE'
    });
    if (res.ok) loadEvents(DISTRICT_ID);
}