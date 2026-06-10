CREATE VIEW v_apartments_available AS
SELECT id, district_id, floor, rooms, area_m2, price_per_day, description, image
FROM apartments
WHERE is_available = TRUE;

CREATE VIEW v_events_with_district AS
SELECT e.id, e.title, e.event_date, e.location, e.organizer,
       e.visitors_count, e.description, e.image,
       d.name AS district_name
FROM events e
JOIN districts d ON d.id = e.district_id;

CREATE VIEW v_district_stats AS
SELECT d.id, d.name,
       COUNT(DISTINCT s.id) AS streets_count,
       COUNT(DISTINCT i.id) AS institutions_count,
       COUNT(DISTINCT e.id) AS events_count
FROM districts d
LEFT JOIN streets s ON s.district_id = d.id
LEFT JOIN institutions i ON i.district_id = d.id
LEFT JOIN events e ON e.district_id = d.id
GROUP BY d.id, d.name;
