document.addEventListener('DOMContentLoaded', function() {
    loadStreets(DISTRICT_ID);

    if (IS_ADMIN && window.location.search.includes('add=1')) {
        openAddModal();
    }

    document.getElementById('streets-list').addEventListener('click', function(e) {
        const editBtn = e.target.closest('.btn-edit');
        if (editBtn) {
            openEditModal(
                editBtn.dataset.id,
                editBtn.dataset.name,
                editBtn.dataset.postal,
                editBtn.dataset.built,
                editBtn.dataset.length,
                editBtn.dataset.surface
            );
        }
        const deleteBtn = e.target.closest('.btn-delete');
        if (deleteBtn) {
            deleteStreet(deleteBtn.dataset.id);
        }
    });
});

async function loadStreets(districtId) {
    try {
        const res = await fetch(`/rajon/api/streets.php?district_id=${districtId}`);
        const streets = await res.json();
        const list = document.getElementById('streets-list');
        list.innerHTML = '';

        if (!streets || streets.length === 0) {
            list.innerHTML = '<p class="no-streets">Улиц пока нет</p>';
            return;
        }

        streets.forEach(s => {
            const card = document.createElement('div');
            card.className = 'street-card';
            card.innerHTML = `
                <div class="street-card-header">
                    <h3>${escapeHtml(s.name)}</h3>
                </div>
                ${s.image ? `<img src="/rajon/uploads/streets/${s.image}" class="card-img" alt="">` : ''}
                <div class="street-card-body">
                    <p><strong>Индекс:</strong> ${escapeHtml(s.postal_code) || '–'}</p>
                    <p><strong>Год постройки:</strong> ${s.built_year || '–'}</p>
                    <p><strong>Длина:</strong> ${s.length_km || '–'} км</p>
                    <p><strong>Покрытие:</strong> ${escapeHtml(s.surface_type) || '–'}</p>
                </div>
                <div class="street-card-actions">
                    ${IS_ADMIN ? `
                    <button class="btn-edit"
                        data-id="${s.id}"
                        data-name="${escapeAttr(s.name)}"
                        data-postal="${escapeAttr(s.postal_code)}"
                        data-built="${escapeAttr(s.built_year)}"
                        data-length="${escapeAttr(s.length_km)}"
                        data-surface="${escapeAttr(s.surface_type)}">Изменить</button>
                    <button class="btn-delete" data-id="${s.id}">Удалить</button>
                    ` : ''}
                </div>
            `;
            list.appendChild(card);
        });
    } catch (err) {
        console.error("Greška pri učitavanju ulica:", err);
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
    document.getElementById('modal-title').textContent = 'Добавить улицу';
    document.getElementById('street-form').reset();
    document.getElementById('street-id').value = '';
    document.getElementById('modal').showModal();
}

function openEditModal(id, name, postal, built, length, surface) {
    document.getElementById('modal-title').textContent = 'Изменить улицу';
    document.getElementById('street-id').value      = id;
    document.getElementById('street-name').value    = name;
    document.getElementById('street-postal').value  = postal;
    document.getElementById('street-built').value   = built;
    document.getElementById('street-length').value  = length;
    document.getElementById('street-surface').value = surface;
    document.getElementById('modal').showModal();
}

function closeModal() {
    document.getElementById('modal').close();
}

document.getElementById('street-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const id = document.getElementById('street-id').value;

    const formData = new FormData();
    formData.append('name',         document.getElementById('street-name').value);
    formData.append('district_id',  DISTRICT_ID);
    formData.append('postal_code',  document.getElementById('street-postal').value);
    formData.append('built_year',   document.getElementById('street-built').value);
    formData.append('length_km',    document.getElementById('street-length').value);
    formData.append('surface_type', document.getElementById('street-surface').value);

    const imageFile = document.getElementById('street-image').files[0];
    if (imageFile) formData.append('image', imageFile);

    try {
        const res = await fetch('/rajon/api/streets.php' + (id ? '?id=' + id : ''), {
            method: 'POST',
            body: formData
        });

        const result = await res.json();

        if (res.ok) {
            closeModal();
            loadStreets(DISTRICT_ID);
        } else {
            alert("Ошибка: " + (result.error || 'Неизвестная ошибка'));
        }
    } catch (error) {
        console.error("Greška:", error);
        alert("Системная ошибка, проверьте консоль.");
    }
});

async function deleteStreet(id) {
    if (!confirm('Удалить улицу?')) return;
    const res = await fetch('/rajon/api/streets.php?id=' + id, {
        method: 'DELETE'
    });
    if (res.ok) loadStreets(DISTRICT_ID);
}
