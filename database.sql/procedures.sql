CREATE PROCEDURE sp_buy_ticket(
    p_user_id INT,
    p_ticket_id INT,
    p_quantity INT
)
LANGUAGE plpgsql AS $$
DECLARE
    v_price NUMERIC;
    v_available INT;
BEGIN
    SELECT price, (total_quantity - sold_quantity)
    INTO v_price, v_available
    FROM tickets WHERE id = p_ticket_id;

    IF v_available < p_quantity THEN
        RAISE EXCEPTION 'Not enough tickets available';
    END IF;

    UPDATE tickets
    SET sold_quantity = sold_quantity + p_quantity
    WHERE id = p_ticket_id;

    INSERT INTO purchases (user_id, ticket_id, quantity, total_price)
    VALUES (p_user_id, p_ticket_id, p_quantity, v_price * p_quantity);
END;
$$;

CREATE PROCEDURE sp_reserve_apartment(
    p_user_id INT,
    p_apartment_id INT,
    p_start_date DATE,
    p_end_date DATE
)
LANGUAGE plpgsql AS $$
DECLARE
    v_price_per_day NUMERIC;
    v_days INT;
    v_total NUMERIC;
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM apartments 
        WHERE id = p_apartment_id AND is_available = TRUE
    ) THEN
        RAISE EXCEPTION 'Apartment is not available';
    END IF;

    SELECT price_per_day INTO v_price_per_day
    FROM apartments WHERE id = p_apartment_id;

    v_days := p_end_date - p_start_date;

    IF v_days <= 0 THEN
        RAISE EXCEPTION 'End date must be after start date';
    END IF;

    v_total := v_price_per_day * v_days;

    INSERT INTO apartment_reservations 
        (user_id, apartment_id, start_date, end_date, total_price, status)
    VALUES 
        (p_user_id, p_apartment_id, p_start_date, p_end_date, v_total, 'pending');

    UPDATE apartments SET is_available = FALSE 
    WHERE id = p_apartment_id;
END;
$$;

CREATE PROCEDURE sp_promote_user(p_username VARCHAR)
LANGUAGE plpgsql AS $$
BEGIN
    UPDATE users SET role = 'admin' WHERE username = p_username;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'User % not found', p_username;
    END IF;
END;
$$;