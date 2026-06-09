const API = '/rajon/api/users.php'; 
const modal = document.getElementById('modal');
const form = document.getElementById('user-form');

async function loadUsers() {
    try {
        const response = await fetch(API);
        if (!response.ok) throw new Error('Сетевой ответ был неудовлетворительным');
        const users = await response.json();
        
        const list = document.getElementById('users-list');
        list.innerHTML = '';
        
        users.forEach(user => {
            list.innerHTML += `
                <div class="user-card">
                    <div class="user-card-header">
                        <h3>${user.name} ${user.surname}</h3>
                        <span class="user-role-badge">${user.role ? user.role.toUpperCase() : 'USER'}</span>
                    </div>
                    <div class="user-card-body">
                        <p><strong>Username:</strong> <span>${user.username}</span></p>
                        <p><strong>Email:</strong> ${user.email}</p>
                        ${user.phone ? `<p><strong>Телефон:</strong> ${user.phone}</p>` : ''}
                    </div>
                    <div class="user-card-actions">
                        <button class="btn-delete" onclick="deleteUser(${user.id})">Удалить</button>
                    </div>
                </div>`;
        });
    } catch (e) {
        console.error('Ошибка при загрузке пользователей:', e);
    }
}

function openAddModal() {
    form.reset();
    document.getElementById('user-id').value = '';
    document.getElementById('modal-title').textContent = 'Добавить пользователя';
    modal.showModal();
}

function closeModal() {
    modal.close();
}

async function deleteUser(id) {
    if (!confirm('Вы уверены, что хотите удалить этого пользователя?')) {
        return;
    }

    try {
        const response = await fetch(`${API}?id=${id}`, {
            method: 'DELETE'
        });

        const result = await response.json();

        if (response.ok) {
            alert('Пользователь успешно удален!');
            loadUsers(); 
        } else {
            alert('Ошибка при удалении: ' + (result.error || 'Неизвестная ошибка'));
        }
    } catch (e) {
        console.error('Ошибка на клиенте при удалении:', e);
        alert('Ошибка при соединении с сервером');
    }
}

form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const data = {
        name: document.getElementById('user-name').value,
        surname: document.getElementById('user-surname').value,
        username: document.getElementById('user-username').value,
        email: document.getElementById('user-email').value,
        phone: document.getElementById('user-phone') ? document.getElementById('user-phone').value : null,
        password: document.getElementById('user-password').value 
    };

    try {
        const response = await fetch(API, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (response.ok) {
            closeModal();
            loadUsers();
            alert('Пользователь успешно сохранен!');
        } else {
            alert('Ошибка при сохранении: ' + (result.error || 'Неизвестная ошибка'));
        }
    } catch (e) {
        console.error('Ошибка соединения:', e);
        alert('Ошибка при соединении с сервером');
    }
});

loadUsers();