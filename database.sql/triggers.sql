CREATE TABLE reservation_log (
  id SERIAL PRIMARY KEY,
  reservation_id INT,
  old_status VARCHAR(16),
  new_status VARCHAR(16),
  changed_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE FUNCTION trg_districts_set_updated_at()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
  NEW.updated_at = NOW();
  RETURN NEW;
END;
$$;

CREATE FUNCTION trg_check_apartment_price()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
  IF NEW.price_per_day <= 0 THEN
    RAISE EXCEPTION 'price_per_day must be > 0, got %', NEW.price_per_day;
  END IF;
  RETURN NEW;
END;
$$;

CREATE FUNCTION trg_log_reservation_status()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
  IF OLD.status IS DISTINCT FROM NEW.status THEN
    INSERT INTO reservation_log(reservation_id, old_status, new_status)
    VALUES (NEW.id, OLD.status, NEW.status);
  END IF;
  RETURN NEW;
END;
$$;

CREATE TRIGGER trg_set_district_updated_at
BEFORE UPDATE ON districts
FOR EACH ROW EXECUTE FUNCTION trg_districts_set_updated_at();

CREATE TRIGGER trg_check_apartment_price
BEFORE INSERT OR UPDATE ON apartments
FOR EACH ROW EXECUTE FUNCTION trg_check_apartment_price();

CREATE TRIGGER trg_log_reservation_status
AFTER UPDATE ON apartment_reservations
FOR EACH ROW EXECUTE FUNCTION trg_log_reservation_status();
