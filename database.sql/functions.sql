CREATE FUNCTION fn_event_revenue(p_event_id INT)
RETURNS NUMERIC
LANGUAGE plpgsql AS $$
DECLARE
    revenue NUMERIC;
BEGIN
    SELECT COALESCE(SUM(t.price * t.sold_quantity), 0) INTO revenue
    FROM tickets t WHERE t.event_id = p_event_id;
    RETURN revenue;
END;
$$;

CREATE FUNCTION fn_user_total_spent(p_user_id INT)
RETURNS NUMERIC
LANGUAGE plpgsql AS $$
DECLARE
    total NUMERIC;
BEGIN
    SELECT COALESCE(
        (SELECT SUM(total_price) FROM purchases WHERE user_id = p_user_id) +
        (SELECT COALESCE(SUM(total_price), 0) FROM apartment_reservations WHERE user_id = p_user_id), 0) INTO total;
    RETURN total;
END;
$$;

CREATE FUNCTION fn_apartment_days(p_start_date DATE, p_end_date DATE)
RETURNS INT
LANGUAGE plpgsql AS $$
BEGIN
    RETURN (p_end_date - p_start_date);
END;
$$;