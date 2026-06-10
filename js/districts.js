document.addEventListener('DOMContentLoaded', function() {
    loadDistricts();
});
function openAddModal() {
    document.getElementById('modal-title').textContent = 'Добавить район';
    document.getElementById('district-form').reset();
    document.getElementById('district-id').value = '';
    document.getElementById('modal').showModal();
}
function closeModal() {
    document.getElementById('modal').close();
}
async function loadDistricts() {
    const res = await fetch('/rajon/api/districts.php');
    const districts = await res.json();
    const list = document.getElementById('districts-list');
    list.innerHTML = '';
    districts.forEach(d => {
        const card = document.createElement('div');
        card.className = 'district-card';
        card.innerHTML = `
            <div class="district-card-header">
                <h3>${escapeHtml(d.name)}</h3>
            </div>
            <div class="district-card-body">
                ${d.image ? `<img src="/rajon/uploads/districts/${d.image}" class="card-img" alt="${escapeHtml(d.name)}">` : ''}
                <p><strong>Описание:</strong> ${escapeHtml(d.description || '—')}</p>
                <p><strong>Площадь:</strong> ${d.area || '—'} км²</p>
                <p><strong>Население:</strong> ${d.population || '—'}</p>
                <p><strong>Год основания:</strong> ${d.founded_year || '—'}</p>
            </div>
            <div class="district-card-actions">
                <a href="/rajon/streets.php?district_id=${d.id}" class="btn">Улицы</a>
                ${IS_ADMIN ? `
                    <button class="btn-edit" data-id="${d.id}" data-name="${escapeHtml(d.name)}" data-desc="${escapeHtml(d.description || '')}" data-area="${d.area || ''}" data-pop="${d.population || ''}" data-founded="${d.founded_year || ''}">Изменить</button>
                    <button class="btn-delete" data-id="${d.id}">Удалить</button>
                ` : ''}
            </div>
        `;
        if (IS_ADMIN) {
            const editBtn = card.querySelector('.btn-edit');
            const deleteBtn = card.querySelector('.btn-delete');
            editBtn.onclick = () => openEditModal(
                editBtn.dataset.id,
                editBtn.dataset.name,
                editBtn.dataset.desc,
                editBtn.dataset.area,
                editBtn.dataset.pop,
                editBtn.dataset.founded
            );
            deleteBtn.onclick = () => deleteDistrict(deleteBtn.dataset.id);
        }
        list.appendChild(card);
    });
}
function openEditModal(id, name, description, area, population, founded) {
    document.getElementById('modal-title').textContent = 'Изменить район';
    document.getElementById('district-id').value          = id;
    document.getElementById('district-name-input').value  = name;
    document.getElementById('district-description').value = description || '';
    document.getElementById('district-area').value        = area || '';
    document.getElementById('district-population').value  = population || '';
    document.getElementById('district-founded').value     = founded || '';
    document.getElementById('district-image').value       = '';
    document.getElementById('modal').showModal();
}
document.getElementById('district-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('district-id').value;
    const formData = new FormData();
    formData.append('name',         document.getElementById('district-name-input').value.trim());
    formData.append('description',  document.getElementById('district-description').value.trim());
    formData.append('area',         document.getElementById('district-area').value);
    formData.append('population',   document.getElementById('district-population').value);
    formData.append('founded_year', document.getElementById('district-founded').value);
    if (id) formData.append('_method', 'PUT');
    const imageFile = document.getElementById('district-image').files[0];
    if (imageFile) formData.append('image', imageFile);
    const res = await fetch('/rajon/api/districts.php' + (id ? '?id=' + id : ''), {
        method: 'POST',
        body: formData
    });
    const data = await res.json();
    if (res.ok && data.success) {
        closeModal();
        loadDistricts();
    } else {
        alert(data.error || 'Ошибка');
    }
});
async function deleteDistrict(id) {
    if (!confirm('Удалить район?')) return;
    const res = await fetch('/rajon/api/districts.php?id=' + id, {
        method: 'DELETE'
    });
    if (res.ok) loadDistricts();
}
function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}
