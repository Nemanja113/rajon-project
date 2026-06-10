document.addEventListener('DOMContentLoaded', function() {
    loadInstitutions(DISTRICT_ID);
    if (IS_ADMIN && window.location.search.includes('add=1')) {
        openAddModal();
    }
    document.getElementById('institutions-list').addEventListener('click', function(e) {
        const editBtn = e.target.closest('.btn-edit');
        if (editBtn) {
            openEditModal(
                editBtn.dataset.id,
                editBtn.dataset.name,
                editBtn.dataset.type,
                editBtn.dataset.address,
                editBtn.dataset.phone,
                editBtn.dataset.hours
            );
        }
        const deleteBtn = e.target.closest('.btn-delete');
        if (deleteBtn) {
            deleteInstitution(deleteBtn.dataset.id);
        }
    });
});
async function loadInstitutions(districtId) {
    try {
        const res = await fetch(`/rajon/api/institutions.php?district_id=${districtId}`);
        const institutions = await res.json();
        const list = document.getElementById('institutions-list');
        list.innerHTML = '';
        if (!institutions || institutions.length === 0) {
            list.innerHTML = '<p class="no-items">Учреждений пока нет</p>';
            return;
        }
        institutions.forEach(i => {
            const card = document.createElement('div');
            card.className = 'street-card';
            card.innerHTML = `
                <div class="street-card-header">
                    <h3>${escapeHtml(i.name)}</h3>
                </div>
                ${i.image ? `<img src="/rajon/uploads/institutions/${i.image}" class="card-img" alt="">` : ''}
                <div class="street-card-body">
                    <p><strong>Тип:</strong> ${escapeHtml(i.institution_type) || '–'}</p>
                    <p><strong>Адрес:</strong> ${escapeHtml(i.address) || '–'}</p>
                    <p><strong>Телефон:</strong> ${escapeHtml(i.phone) || '–'}</p>
                    <p><strong>Часы работы:</strong> ${escapeHtml(i.working_hours) || '–'}</p>
                </div>
                <div class="street-card-actions">
                    ${IS_ADMIN ? `
                    <button class="btn-edit"
                        data-id="${i.id}"
                        data-name="${escapeAttr(i.name)}"
                        data-type="${escapeAttr(i.institution_type)}"
                        data-address="${escapeAttr(i.address)}"
                        data-phone="${escapeAttr(i.phone)}"
                        data-hours="${escapeAttr(i.working_hours)}">Изменить</button>
                    <button class="btn-delete" data-id="${i.id}">Удалить</button>
                    ` : ''}
                </div>
            `;
            list.appendChild(card);
        });
    } catch (err) {
        console.error("Greška pri učitavanju ustanova:", err);
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
function openAddModal() {
    document.getElementById('modal-title').textContent = 'Добавить учреждение';
    document.getElementById('institution-form').reset();
    document.getElementById('institution-id').value = '';
    document.getElementById('modal').showModal();
}
function escapeAttr(val) {
    if (val === null || val === undefined) return '';
    return String(val).replace(/"/g, '&quot;');
}
function openEditModal(id, name, type, address, phone, hours) {
    document.getElementById('modal-title').textContent = 'Изменить учреждение';
    document.getElementById('institution-id').value      = id;
    document.getElementById('institution-name').value    = name;
    document.getElementById('institution-type').value    = type;
    document.getElementById('institution-address').value = address;
    document.getElementById('institution-phone').value   = phone;
    document.getElementById('institution-hours').value   = hours;
    document.getElementById('modal').showModal();
}
function closeModal() {
    document.getElementById('modal').close();
}
document.getElementById('institution-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('institution-id').value;
    const formData = new FormData();
    formData.append('name',             document.getElementById('institution-name').value);
    formData.append('district_id',      DISTRICT_ID);
    formData.append('institution_type', document.getElementById('institution-type').value);
    formData.append('address',          document.getElementById('institution-address').value);
    formData.append('phone',            document.getElementById('institution-phone').value);
    formData.append('working_hours',    document.getElementById('institution-hours').value);
    const imageFile = document.getElementById('institution-image').files[0];
    if (imageFile) formData.append('image', imageFile);
    try {
        const res = await fetch('/rajon/api/institutions.php' + (id ? '?id=' + id : ''), {
            method: 'POST',
            body: formData
        });
        const result = await res.json();
        if (res.ok) {
            closeModal();
            loadInstitutions(DISTRICT_ID);
        } else {
            alert("Ошибка: " + (result.error || 'Неизвестная ошибка'));
        }
    } catch (error) {
        console.error("Greška:", error);
        alert("Системная ошибка, проверьте консоль.");
    }
});
async function deleteInstitution(id) {
    if (!confirm('Удалить учреждение?')) return;
    const res = await fetch('/rajon/api/institutions.php?id=' + id, {
        method: 'DELETE'
    });
    if (res.ok) loadInstitutions(DISTRICT_ID);
}