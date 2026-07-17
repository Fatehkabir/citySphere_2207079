drop table users cascade;
drop table user_roles cascade;
drop table areas cascade;
drop table buildings cascade;
drop table rentals cascade;
drop table crime_reports cascade;
drop table criminal_records cascade;
drop table audit_logs cascade;
drop table announcements cascade;

CREATE TABLE users (
    nid           VARCHAR2(6)   NOT NULL,
    full_name     VARCHAR2(100) NOT NULL,
    email         VARCHAR2(150) NOT NULL UNIQUE,
    password_hash VARCHAR2(255) NOT NULL,
    phone         VARCHAR2(20),
    profile_photo VARCHAR2(255),
    created_at    TIMESTAMP DEFAULT SYSTIMESTAMP,
    CONSTRAINT pk_users     PRIMARY KEY (nid),
    CONSTRAINT chk_nid_fmt  CHECK (REGEXP_LIKE(nid, '^[0-9]{6}$'))
);


CREATE TABLE user_roles (
    user_nid   VARCHAR2(6)  NOT NULL,
    role       VARCHAR2(20) NOT NULL,
    CONSTRAINT pk_user_roles  PRIMARY KEY (user_nid, role),
    CONSTRAINT fk_ur_user     FOREIGN KEY (user_nid) REFERENCES users(nid) ON DELETE CASCADE,
    CONSTRAINT chk_role       CHECK (role IN ('user','admin','house_owner','police'))
);

CREATE TABLE areas (
    area_id    NUMBER        NOT NULL,
    name       VARCHAR2(100) NOT NULL UNIQUE,
    city       VARCHAR2(80)  NOT NULL,
    created_at TIMESTAMP DEFAULT SYSTIMESTAMP,
    CONSTRAINT pk_areas PRIMARY KEY (area_id)
);

CREATE TABLE buildings (
    building_id NUMBER        NOT NULL,
    name        VARCHAR2(120) NOT NULL,
    address     VARCHAR2(255) NOT NULL,
    area_id     NUMBER        NOT NULL,
    owner_nid   VARCHAR2(6)   NOT NULL,
    total_units NUMBER        DEFAULT 1,
    created_at  TIMESTAMP DEFAULT SYSTIMESTAMP,
    CONSTRAINT pk_buildings   PRIMARY KEY (building_id),
    CONSTRAINT fk_bld_area    FOREIGN KEY (area_id)    REFERENCES areas(area_id),
    CONSTRAINT fk_bld_owner   FOREIGN KEY (owner_nid)  REFERENCES users(nid)
);

CREATE TABLE rentals (
    rental_id      NUMBER         NOT NULL,
    building_id    NUMBER         NOT NULL,
    renter_nid     VARCHAR2(6)    NOT NULL,
    unit_no        VARCHAR2(20),
    rent_amount    NUMBER(10,2)   NOT NULL,
    start_date     DATE           DEFAULT SYSDATE,
    payment_status VARCHAR2(10)   DEFAULT 'pending',
    status         VARCHAR2(10)   DEFAULT 'active',
    created_at     TIMESTAMP DEFAULT SYSTIMESTAMP,
    CONSTRAINT pk_rentals        PRIMARY KEY (rental_id),
    CONSTRAINT fk_rent_bld       FOREIGN KEY (building_id) REFERENCES buildings(building_id) ON DELETE CASCADE,
    CONSTRAINT fk_rent_renter    FOREIGN KEY (renter_nid)  REFERENCES users(nid),
    CONSTRAINT chk_pay_status    CHECK (payment_status IN ('pending','paid','overdue')),
    CONSTRAINT chk_rent_status   CHECK (status IN ('active','ended')),
    CONSTRAINT uq_active_unit    UNIQUE (building_id, unit_no, status)
);


CREATE TABLE crime_reports (
    report_id    NUMBER        NOT NULL,
    reporter_nid VARCHAR2(6),
    is_anonymous NUMBER(1)     DEFAULT 0,
    area_id      NUMBER,
    title        VARCHAR2(200) NOT NULL,
    description  CLOB          NOT NULL,
    status       VARCHAR2(10)  DEFAULT 'pending',
    reviewed_by  VARCHAR2(6),
    reviewed_at  TIMESTAMP,
    created_at   TIMESTAMP DEFAULT SYSTIMESTAMP,
    CONSTRAINT pk_crime_reports  PRIMARY KEY (report_id),
    CONSTRAINT fk_rep_reporter   FOREIGN KEY (reporter_nid) REFERENCES users(nid),
    CONSTRAINT fk_rep_reviewer   FOREIGN KEY (reviewed_by)  REFERENCES users(nid),
    CONSTRAINT fk_rep_area       FOREIGN KEY (area_id)      REFERENCES areas(area_id),
    CONSTRAINT chk_rep_status    CHECK (status IN ('pending','verified','rejected','solved')),
    CONSTRAINT chk_anon          CHECK (is_anonymous IN (0,1))
);


CREATE TABLE criminal_records (
    record_id    NUMBER        NOT NULL,
    citizen_nid  VARCHAR2(6)   NOT NULL,
    report_id    NUMBER,
    offense      VARCHAR2(255) NOT NULL,
    description  CLOB,
    recorded_by  VARCHAR2(6)   NOT NULL,
    recorded_at  TIMESTAMP DEFAULT SYSTIMESTAMP,
    CONSTRAINT pk_criminal_rec   PRIMARY KEY (record_id),
    CONSTRAINT fk_cr_citizen     FOREIGN KEY (citizen_nid)  REFERENCES users(nid),
    CONSTRAINT fk_cr_report      FOREIGN KEY (report_id)    REFERENCES crime_reports(report_id),
    CONSTRAINT fk_cr_officer     FOREIGN KEY (recorded_by)  REFERENCES users(nid)
);


CREATE TABLE audit_logs (
    log_id     NUMBER        NOT NULL,
    table_name VARCHAR2(50),
    record_id  NUMBER,
    action     VARCHAR2(40),
    old_value  VARCHAR2(200),
    new_value  VARCHAR2(200),
    created_at TIMESTAMP DEFAULT SYSTIMESTAMP,
    CONSTRAINT pk_audit_logs PRIMARY KEY (log_id)
);


CREATE TABLE announcements (
    id          NUMBER        NOT NULL,
    title       VARCHAR2(200) NOT NULL,
    content     CLOB          NOT NULL,
    target_role VARCHAR2(20)  DEFAULT 'all',
    created_by  VARCHAR2(6)   NOT NULL,
    created_at  TIMESTAMP DEFAULT SYSTIMESTAMP,
    CONSTRAINT pk_announcements PRIMARY KEY (id),
    CONSTRAINT fk_ann_user      FOREIGN KEY (created_by) REFERENCES users(nid),
    CONSTRAINT chk_target       CHECK (target_role IN ('all','user','house_owner','police','admin'))
);

CREATE OR REPLACE TRIGGER trg_rental_payment_audit
  AFTER UPDATE OF payment_status ON rentals
  FOR EACH ROW
DECLARE
  v_lid NUMBER;
BEGIN
  IF :OLD.payment_status <> :NEW.payment_status THEN
    SELECT NVL(MAX(log_id), 0) + 1 INTO v_lid FROM audit_logs;
    INSERT INTO audit_logs(log_id, table_name, record_id, action, old_value, new_value)
    VALUES (v_lid, 'rentals', :NEW.rental_id, 'UPDATE_PAYMENT_STATUS',
            :OLD.payment_status, :NEW.payment_status);
  END IF;
END;
/