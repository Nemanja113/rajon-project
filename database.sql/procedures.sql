CREATE PROCEDURE sp_confirm_reservation(p_reservation_id INT)
LANGUAGE plpgsql AS $$
DECLARE v_apt_id INT;
BEGIN
  SELECT apartment_id INTO v_apt_id
  FROM apartment_reservations WHERE id = p_reservation_id;
  IF NOT FOUND THEN
    RAISE EXCEPTION 'Reservation % not found', p_reservation_id;
  END IF;
  UPDATE apartment_reservations SET status = 'confirmed'
  WHERE id = p_reservation_id;
  UPDATE apartments SET is_available = FALSE WHERE id = v_apt_id;
END;
$$;

CREATE PROCEDURE sp_cancel_reservation(p_reservation_id INT)
LANGUAGE plpgsql AS $$
DECLARE v_apt_id INT;
BEGIN
  SELECT apartment_id INTO v_apt_id
  FROM apartment_reservations WHERE id = p_reservation_id;
  IF NOT FOUND THEN
    RAISE EXCEPTION 'Reservation % not found', p_reservation_id;
  END IF;
  UPDATE apartment_reservations SET status = 'cancelled'
  WHERE id = p_reservation_id;
  UPDATE apartments SET is_available = TRUE WHERE id = v_apt_id;
END;
$$;

CREATE PROCEDURE sp_clear_user_cart(p_user_id INT)
LANGUAGE plpgsql AS $$
BEGIN
  DELETE FROM cart WHERE user_id = p_user_id;
END;
$$;
