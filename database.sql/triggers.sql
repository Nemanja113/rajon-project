CREATE TRIGGER trg_set_updated_at
BEFORE UPDATE ON districts
FOR EACH ROW EXECUTE FUNCTION trg_districts_set_updated_at();

CREATE TRIGGER trg_auto_unavailable
AFTER INSERT ON apartment_reservations
FOR EACH ROW EXECUTE FUNCTION trg_auto_set_unavailable();

CREATE TRIGGER trg_validate_dates
BEFORE INSERT OR UPDATE ON apartment_reservations
FOR EACH ROW EXECUTE FUNCTION trg_validate_reservation_dates();