-- ===================================================================
CREATE OR REPLACE TRIGGER trg_user_before_insert
  BEFORE INSERT ON users
  FOR EACH ROW
BEGIN
  :NEW.full_name := TRIM(:NEW.full_name);
  :NEW.email     := LOWER(TRIM(:NEW.email));

  IF :NEW.full_name IS NULL OR LENGTH(:NEW.full_name) = 0 THEN
    RAISE_APPLICATION_ERROR(-20030, 'Full name cannot be blank.');
  END IF;
END trg_user_before_insert;
/

-- ===================================================================
CREATE OR REPLACE TRIGGER trg_user_after_insert
  AFTER INSERT ON users
  FOR EACH ROW
DECLARE
  v_lid NUMBER;
BEGIN
  SELECT NVL(MAX(log_id), 0) + 1 INTO v_lid FROM audit_logs;
  INSERT INTO audit_logs(log_id, table_name, record_id, action, old_value, new_value)
  VALUES (
    v_lid,
    'users',
    NULL,
    'INSERT_USER',
    NULL,
    'NID: ' || :NEW.nid || ' | Name: ' || :NEW.full_name
  );
END trg_user_after_insert;
/

-- ===================================================================
CREATE OR REPLACE TRIGGER trg_rental_before_insert
  BEFORE INSERT ON rentals
  FOR EACH ROW
BEGIN
  IF :NEW.rent_amount <= 0 THEN
    RAISE_APPLICATION_ERROR(-20031, 'Rent amount must be greater than zero.');
  END IF;

  IF :NEW.payment_status IS NULL THEN
    :NEW.payment_status := 'pending';
  END IF;
  IF :NEW.status IS NULL THEN
    :NEW.status := 'active';
  END IF;
  IF :NEW.start_date IS NULL THEN
    :NEW.start_date := SYSDATE;
  END IF;
END trg_rental_before_insert;
/

-- ===================================================================
CREATE OR REPLACE TRIGGER trg_rental_payment_audit
  AFTER UPDATE OF payment_status ON rentals
  FOR EACH ROW
DECLARE
  v_lid NUMBER;
BEGIN
  IF :OLD.payment_status <> :NEW.payment_status THEN
    SELECT NVL(MAX(log_id), 0) + 1 INTO v_lid FROM audit_logs;
    INSERT INTO audit_logs(log_id, table_name, record_id, action, old_value, new_value)
    VALUES (
      v_lid,
      'rentals',
      :NEW.rental_id,
      'UPDATE_PAYMENT_STATUS',
      :OLD.payment_status,
      :NEW.payment_status
    );
  END IF;
END trg_rental_payment_audit;
/

-- ===================================================================
CREATE OR REPLACE TRIGGER trg_report_before_insert
  BEFORE INSERT ON crime_reports
  FOR EACH ROW
BEGIN
  :NEW.title := INITCAP(TRIM(:NEW.title));

  IF :NEW.is_anonymous = 1 THEN
    :NEW.reporter_nid := NULL;
  END IF;
END trg_report_before_insert;
/

-- ===================================================================
CREATE OR REPLACE TRIGGER trg_report_after_status_change
  AFTER UPDATE OF status ON crime_reports
  FOR EACH ROW
DECLARE
  v_lid NUMBER;
BEGIN
  IF :OLD.status <> :NEW.status THEN
    SELECT NVL(MAX(log_id), 0) + 1 INTO v_lid FROM audit_logs;
    INSERT INTO audit_logs(log_id, table_name, record_id, action, old_value, new_value)
    VALUES (
      v_lid,
      'crime_reports',
      :NEW.report_id,
      'UPDATE_REPORT_STATUS',
      :OLD.status,
      :NEW.status
    );
  END IF;
END trg_report_after_status_change;
/

-- ===================================================================
CREATE OR REPLACE TRIGGER trg_criminal_before_insert
  BEFORE INSERT ON criminal_records
  FOR EACH ROW
BEGIN
  :NEW.offense := UPPER(TRIM(:NEW.offense));

  IF :NEW.offense IS NULL OR LENGTH(:NEW.offense) = 0 THEN
    RAISE_APPLICATION_ERROR(-20033, 'Offense description cannot be blank.');
  END IF;
END trg_criminal_before_insert;
/

-- ===================================================================
CREATE OR REPLACE TRIGGER trg_criminal_after_insert
  AFTER INSERT ON criminal_records
  FOR EACH ROW
DECLARE
  v_lid NUMBER;
BEGIN
  SELECT NVL(MAX(log_id), 0) + 1 INTO v_lid FROM audit_logs;
  INSERT INTO audit_logs(log_id, table_name, record_id, action, old_value, new_value)
  VALUES (
    v_lid,
    'criminal_records',
    :NEW.record_id,
    'INSERT_CRIMINAL_RECORD',
    NULL,
    'Citizen NID: ' || :NEW.citizen_nid || ' | Offense: ' || SUBSTR(:NEW.offense, 1, 100)
  );
END trg_criminal_after_insert;
/

-- ===================================================================
CREATE OR REPLACE PACKAGE pkg_rentals AS
    PROCEDURE sp_assign_renter(
        p_owner_nid   IN VARCHAR2, p_rental_id   IN NUMBER,
        p_building_id IN NUMBER,   p_renter_nid  IN VARCHAR2,
        p_unit_no     IN VARCHAR2, p_amount      IN NUMBER);
    PROCEDURE sp_update_payment(
        p_actor_nid IN VARCHAR2, p_rental_id IN NUMBER, p_status IN VARCHAR2);
    PROCEDURE sp_end_rental(p_rental_id IN NUMBER);
    PROCEDURE sp_get_all_rentals(p_cur OUT SYS_REFCURSOR);
    PROCEDURE sp_get_my_rentals(p_nid IN VARCHAR2, p_cur OUT SYS_REFCURSOR);
    PROCEDURE sp_audit_pending_rentals(
        p_admin_nid IN VARCHAR2, p_processed OUT NUMBER);
    PROCEDURE sp_mark_overdue_rentals(
        p_admin_nid IN VARCHAR2, p_updated OUT NUMBER);
END pkg_rentals;
/

-- ===================================================================
CREATE OR REPLACE PACKAGE BODY pkg_rentals AS

    PROCEDURE sp_assign_renter(
        p_owner_nid   IN VARCHAR2, p_rental_id   IN NUMBER,
        p_building_id IN NUMBER,   p_renter_nid  IN VARCHAR2,
        p_unit_no     IN VARCHAR2, p_amount      IN NUMBER)
    IS
        v_owner VARCHAR2(6);
        v_units NUMBER;
    BEGIN
        SELECT owner_nid, total_units INTO v_owner, v_units
          FROM buildings WHERE building_id = p_building_id;
        IF v_owner <> p_owner_nid AND pkg_auth.fn_has_role(p_owner_nid,'admin') = 0 THEN
            RAISE_APPLICATION_ERROR(-20004,'Only the building owner can assign renters');
        END IF;
        IF v_units <= 0 THEN
            RAISE_APPLICATION_ERROR(-20015,'No available units in this building');
        END IF;
        IF p_amount <= 0 THEN
            RAISE_APPLICATION_ERROR(-20005,'Rent amount must be positive');
        END IF;
        INSERT INTO rentals(rental_id, building_id, renter_nid, unit_no, rent_amount)
        VALUES (p_rental_id, p_building_id, p_renter_nid, p_unit_no, p_amount);
        UPDATE buildings SET total_units = total_units - 1 WHERE building_id = p_building_id;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            RAISE_APPLICATION_ERROR(-20009,'Building not found');
    END;

    PROCEDURE sp_update_payment(
        p_actor_nid IN VARCHAR2, p_rental_id IN NUMBER, p_status IN VARCHAR2)
    IS
        v_owner  VARCHAR2(6);
        v_renter VARCHAR2(6);
    BEGIN
        SELECT b.owner_nid, r.renter_nid INTO v_owner, v_renter
          FROM rentals r JOIN buildings b ON r.building_id = b.building_id
         WHERE r.rental_id = p_rental_id;
        IF p_actor_nid NOT IN (v_owner, v_renter)
           AND pkg_auth.fn_has_role(p_actor_nid,'admin') = 0 THEN
            RAISE_APPLICATION_ERROR(-20006,'Not allowed to update this payment');
        END IF;
        UPDATE rentals SET payment_status = p_status WHERE rental_id = p_rental_id;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            RAISE_APPLICATION_ERROR(-20009,'Rental not found');
    END;

    PROCEDURE sp_end_rental(p_rental_id IN NUMBER) IS
        v_bid    NUMBER;
        v_status VARCHAR2(10);
    BEGIN
        SELECT building_id, status INTO v_bid, v_status
          FROM rentals WHERE rental_id = p_rental_id;
        IF v_status = 'active' THEN
            UPDATE rentals SET status = 'ended' WHERE rental_id = p_rental_id;
            UPDATE buildings SET total_units = total_units + 1 WHERE building_id = v_bid;
        END IF;
    END;

    PROCEDURE sp_get_all_rentals(p_cur OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cur FOR
            SELECT r.rental_id, r.unit_no, r.rent_amount, r.payment_status,
                   r.status AS r_status,
                   TO_CHAR(r.start_date,'YYYY-MM-DD') AS sd,
                   b.name AS bname, u.full_name AS renter, o.full_name AS owner
              FROM rentals r
              JOIN buildings b ON b.building_id = r.building_id
              JOIN users    u ON u.nid          = r.renter_nid
              JOIN users    o ON o.nid          = b.owner_nid
             ORDER BY r.created_at DESC;
    END;

    PROCEDURE sp_get_my_rentals(p_nid IN VARCHAR2, p_cur OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cur FOR
            SELECT r.rental_id, r.unit_no, r.rent_amount, r.payment_status,
                   r.status AS r_status,
                   TO_CHAR(r.start_date,'YYYY-MM-DD') AS sd,
                   b.name AS bname, u.full_name AS renter, o.full_name AS owner
              FROM rentals r
              JOIN buildings b ON b.building_id = r.building_id
              JOIN users    u ON u.nid          = r.renter_nid
              JOIN users    o ON o.nid          = b.owner_nid
             WHERE b.owner_nid = p_nid OR r.renter_nid = p_nid
             ORDER BY r.created_at DESC;
    END;

    PROCEDURE sp_audit_pending_rentals(p_admin_nid IN VARCHAR2, p_processed OUT NUMBER) IS
        v_rental_id rentals.rental_id%TYPE;
        v_amount    rentals.rent_amount%TYPE;
        v_lid       NUMBER;
        CURSOR c_pending IS
            SELECT rental_id, rent_amount FROM rentals
             WHERE payment_status = 'pending' AND status = 'active';
    BEGIN
        IF pkg_auth.fn_has_role(p_admin_nid,'admin') = 0 THEN
            RAISE_APPLICATION_ERROR(-20007,'Only admin can run audit');
        END IF;
        p_processed := 0;
        OPEN c_pending;
        LOOP
            FETCH c_pending INTO v_rental_id, v_amount;
            EXIT WHEN c_pending%NOTFOUND;
            SELECT NVL(MAX(log_id), 0) + 1 INTO v_lid FROM audit_logs;
            INSERT INTO audit_logs(log_id, table_name, record_id, action, old_value, new_value)
            VALUES (v_lid, 'rentals', v_rental_id, 'AUDIT_PENDING',
                    NULL, 'Pending amount: ' || TO_CHAR(v_amount));
            p_processed := p_processed + 1;
        END LOOP;
        CLOSE c_pending;
    END;

    PROCEDURE sp_mark_overdue_rentals(p_admin_nid IN VARCHAR2, p_updated OUT NUMBER) IS
        v_rental_id rentals.rental_id%TYPE;
        v_lid       NUMBER;
        CURSOR c_overdue IS
            SELECT rental_id FROM rentals
             WHERE status         = 'active'
               AND payment_status = 'pending'
               AND start_date     < SYSDATE - 30;
    BEGIN
        IF pkg_auth.fn_has_role(p_admin_nid,'admin') = 0 THEN
            RAISE_APPLICATION_ERROR(-20008,'Only admin can mark overdue rentals');
        END IF;
        p_updated := 0;
        OPEN c_overdue;
        LOOP
            FETCH c_overdue INTO v_rental_id;
            EXIT WHEN c_overdue%NOTFOUND;
            UPDATE rentals SET payment_status = 'overdue' WHERE rental_id = v_rental_id;
            SELECT NVL(MAX(log_id), 0) + 1 INTO v_lid FROM audit_logs;
            INSERT INTO audit_logs(log_id, table_name, record_id, action, old_value, new_value)
            VALUES (v_lid, 'rentals', v_rental_id, 'AUTO_OVERDUE', 'pending', 'overdue');
            p_updated := p_updated + 1;
        END LOOP;
        CLOSE c_overdue;
    END;

END pkg_rentals;
/

COMMIT;