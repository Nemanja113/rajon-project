CREATE VIEW v_events_with_tickets AS
SELECT e.id, e.title, e.event_date, e.location, e.organizer,
    t.price, t.total_quantity, t.sold_quantity,
    (t.total_quantity - t.sold_quantity) AS available_tickets,
    (t.price * t.sold_quantity) AS total_revenue
FROM events e
LEFT JOIN tickets t ON t.event_id = e.id;

CREATE VIEW v_residents_with_address AS
SELECT r.id, r.name, r.surname, r.birth_year,
    r.address, r.phone, r.email,
    d.name AS district_name,
    s.name AS street_name
FROM residents r
LEFT JOIN districts d ON d.id = r.district_id
LEFT JOIN streets s ON s.id = r.street_id;

CREATE VIEW v_available_apartments AS
SELECT a.id, a.floor, a.rooms, a.area_m2, a.price_per_day,
    a.description,
    d.name AS district_name,
    s.name AS street_name
FROM apartments a
LEFT JOIN districts d ON d.id = a.district_id
LEFT JOIN streets s ON s.id = a.street_id
WHERE a.is_available = TRUE;