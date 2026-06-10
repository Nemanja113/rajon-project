CREATE FUNCTION fn_apartment_total_revenue(p_apartment_id INT)
RETURNS NUMERIC
LANGUAGE plpgsql AS $$
DECLARE total NUMERIC;
BEGIN
  SELECT COALESCE(SUM(total_price), 0) INTO total
  FROM apartment_reservations
  WHERE apartment_id = p_apartment_id
    AND status = 'confirmed';
  RETURN total;
END;
$$;

CREATE FUNCTION fn_count_events_in_district(p_district_id INT)
RETURNS INT
LANGUAGE plpgsql AS $$
DECLARE cnt INT;
BEGIN
  SELECT COUNT(*) INTO cnt
  FROM events
  WHERE district_id = p_district_id;
  RETURN cnt;
END;
$$;

CREATE FUNCTION fn_user_cart_total(p_user_id INT)
RETURNS NUMERIC
LANGUAGE plpgsql AS $$
DECLARE total NUMERIC;
BEGIN
  SELECT COALESCE(SUM(total), 0) INTO total
  FROM cart
  WHERE user_id = p_user_id;
  RETURN total;
END;
$$;
