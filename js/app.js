document.addEventListener('DOMContentLoaded', function() {
    const districtEl = document.getElementById('district-name');
    if (districtEl) {
        const district = JSON.parse(localStorage.getItem('selected_district'));
        if (district) {
            districtEl.textContent = district.name;
            loadNavbarDropdowns(district.id);
        }
    }
});
async function loadNavbarDropdowns(districtId) {
    const streetsRes = await fetch(`/rajon/api/streets.php?district_id=${districtId}`);
    const streets = await streetsRes.json();
    const streetsList = document.getElementById('streets-list-dropdown');
    if (streetsList) {
        streetsList.innerHTML = streets.map(s => 
            `<a href="/rajon/streets.php?id=${s.id}">${s.name}</a>`
        ).join('');
    }
    const instRes = await fetch(`/rajon/api/institutions.php?district_id=${districtId}`);
    const institutions = await instRes.json();
    const instList = document.getElementById('institutions-list-dropdown');
    if (instList) {
        instList.innerHTML = institutions.map(i => 
            `<a href="/rajon/institutions.php?id=${i.id}">${i.name}</a>`
        ).join('');
    }
    const eventsRes = await fetch(`/rajon/api/events.php?district_id=${districtId}`);
    const events = await eventsRes.json();
    const eventsList = document.getElementById('events-list-dropdown');
    if (eventsList) {
        eventsList.innerHTML = events.map(e => 
            `<a href="/rajon/events.php?id=${e.id}">${e.title}</a>`
        ).join('');
    }
    const residentsRes = await fetch(`/rajon/api/residents.php?district_id=${districtId}`);
    const residents = await residentsRes.json();
    const residentsList = document.getElementById('residents-list-dropdown');
    if (residentsList) {
        residentsList.innerHTML = residents.map(r => 
            `<a href="/rajon/residents.php?id=${r.id}">${r.name} ${r.surname}</a>`
        ).join('');
    }
    const aptsRes = await fetch(`/rajon/api/apartments.php?district_id=${districtId}`);
    const apartments = await aptsRes.json();
    const aptsList = document.getElementById('apartments-list-dropdown');
    if (aptsList) {
        aptsList.innerHTML = apartments.map(a => 
            `<a href="/rajon/apartments.php?id=${a.id}">Кв. №${a.id} - ${a.rooms} комн.</a>`
        ).join('');
    }
}