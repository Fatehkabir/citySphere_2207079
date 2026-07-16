CREATE OR REPLACE PACKAGE pkg_auth AS
 FUNCTION  fn_has_role(p_nid VARCHAR2, p_role VARCHAR2) RETURN NUMBER;
 PROCEDURE sp_register_user(p_nid IN VARCHAR2, p_name IN VARCHAR2, p_email IN VARCHAR2, p_hash IN VARCHAR2, p_phone IN VARCHAR2);
 PROCEDURE sp_get_roles_for_user(p_nid IN VARCHAR2, p_cur OUT SYS_REFCURSOR);
 PROCEDURE sp_grant_role(p_admin_nid IN VARCHAR2, p_nid IN VARCHAR2, p_role IN VARCHAR2);
 PROCEDURE sp_revoke_role(p_admin_nid IN VARCHAR2, p_nid IN VARCHAR2, p_role IN VARCHAR2);
 END pkg_auth;
 /

 CREATE OR REPLACE PACKAGE BODY pkg_auth AS
   PROCEDURE sp_register_user(p_nid IN VARCHAR2, p_name IN VARCHAR2, p_email IN VARCHAR2, p_hash IN VARCHAR2, p_phone IN VARCHAR2)
   IS
   BEGIN
    IF NOT REGEXP_LIKE(p_nid,'^[0-9]{6}$') THEN
     RAISE_APPLICATION_ERROR(-20020,'NID mist be exactly 6 digits.');
    END IF;
    INSERT INTO users(nid,full_name,email,password_hash,phone)
    VALUES(p_nid,p_name,LOWER(p_email),p_hash,NULLIF(p_phone,''));
    INSERT INTO user_roles(user_nid,role)VALUES(p_nid,'user');
    END;

    PROCEDURE sp_get_roles_for_user(p_nid IN VARCHAR2, p_cur OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cur FOR SELECT role FROM user_roles WHERE user_nid = p_nid;
    END;

    PROCEDURE sp_grant_role(p_admin_nid IN VARCHAR2, p_nid IN VARCHAR2, p_role IN VARCHAR2) IS
    BEGIN
        IF fn_has_role(p_admin_nid,'admin') = 0 THEN
            RAISE_APPLICATION_ERROR(-20001,'Only admin can grant roles');
        END IF;
        BEGIN
            INSERT INTO user_roles(user_nid, role) VALUES (p_nid, p_role);
        EXCEPTION WHEN DUP_VAL_ON_INDEX THEN NULL; 
        END;
    END;
    
        PROCEDURE sp_grant_role(p_admin_nid IN VARCHAR2, p_nid IN VARCHAR2, p_role IN VARCHAR2) IS
    BEGIN
        IF fn_has_role(p_admin_nid,'admin') = 0 THEN
            RAISE_APPLICATION_ERROR(-20001,'Only admin can grant roles');
        END IF;
        BEGIN
            INSERT INTO user_roles(user_nid, role) VALUES (p_nid, p_role);
        EXCEPTION WHEN DUP_VAL_ON_INDEX THEN NULL;
        END;
    END;

    PROCEDURE sp_revoke_role(p_admin_nid IN VARCHAR2, p_nid IN VARCHAR2, p_role IN VARCHAR2) IS
    BEGIN
        IF fn_has_role(p_admin_nid,'admin') = 0 THEN
            RAISE_APPLICATION_ERROR(-20001,'Only admin can revoke roles');
        END IF;
        DELETE FROM user_roles WHERE user_nid = p_nid AND role = p_role;
    END;




END pkg_auth;
 /    



-- ----------------------------------------------------------------------------------------

 CREATE OR REPLACE PACKAGE pkg_dashboard AS
    PROCEDURE sp_get_dashboard_stats(
        p_total_reports   OUT NUMBER, p_pending_reports OUT NUMBER,
        p_total_areas     OUT NUMBER, p_total_buildings  OUT NUMBER);
    PROCEDURE sp_get_my_recent_reports(p_nid IN VARCHAR2, p_cur OUT SYS_REFCURSOR);
END pkg_dashboard;
/
CREATE OR REPLACE PACKAGE BODY pkg_dashboard AS

    PROCEDURE sp_get_dashboard_stats(
        p_total_reports   OUT NUMBER, p_pending_reports OUT NUMBER,
        p_total_areas     OUT NUMBER, p_total_buildings  OUT NUMBER)
    IS
    BEGIN
        SELECT COUNT(*) INTO p_total_reports   FROM crime_reports;
        SELECT COUNT(*) INTO p_pending_reports  FROM crime_reports WHERE status = 'pending';
        SELECT COUNT(*) INTO p_total_areas      FROM areas;
        SELECT COUNT(*) INTO p_total_buildings  FROM buildings;
    END;

    PROCEDURE sp_get_my_recent_reports(p_nid IN VARCHAR2, p_cur OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cur FOR
             SELECT * FROM (
                 SELECT report_id, title, status,
                        TO_CHAR(created_at,'YYYY-MM-DD HH24:MI') AS ts
                   FROM crime_reports
                  WHERE reporter_nid = p_nid
                  ORDER BY created_at DESC
             ) WHERE ROWNUM <= 5;
    END;

END pkg_dashboard;
/





-- --------------------------------------------------------------------------------

CREATE OR REPLACE PACKAGE pkg_city AS
  
    PROCEDURE sp_add_area(
        p_admin_nid IN VARCHAR2, p_area_id IN NUMBER,
        p_name IN VARCHAR2, p_city IN VARCHAR2);
    PROCEDURE sp_get_buildings(p_owner_nid IN VARCHAR2, p_cur OUT SYS_REFCURSOR);
    PROCEDURE sp_get_building_list(p_owner_nid IN VARCHAR2, p_cur OUT SYS_REFCURSOR);
    PROCEDURE sp_add_building(
        p_admin_nid  IN VARCHAR2, p_building_id IN NUMBER,
        p_name       IN VARCHAR2, p_address     IN VARCHAR2,
        p_area_id    IN NUMBER,   p_owner_nid   IN VARCHAR2,
        p_units      IN NUMBER);    

END pkg_city;
/
CREATE OR REPLACE PACKAGE BODY pkg_city AS

    PROCEDURE sp_add_area(
        p_admin_nid IN VARCHAR2, p_area_id IN NUMBER,
        p_name IN VARCHAR2, p_city IN VARCHAR2)
    IS
    BEGIN
        IF pkg_auth.fn_has_role(p_admin_nid,'admin') = 0 THEN
            RAISE_APPLICATION_ERROR(-20002,'Only admin can manage areas');
        END IF;
        INSERT INTO areas(area_id, name, city) VALUES (p_area_id, p_name, p_city);
    END;

     PROCEDURE sp_get_buildings(p_owner_nid IN VARCHAR2, p_cur OUT SYS_REFCURSOR) IS
    BEGIN
        IF p_owner_nid IS NULL THEN
            OPEN p_cur FOR
                SELECT b.building_id, b.name, b.address, b.total_units,
                       a.name AS area_name, u.full_name AS owner_name
                  FROM buildings b
                  JOIN areas a ON a.area_id  = b.area_id
                  JOIN users u ON u.nid      = b.owner_nid
                 ORDER BY b.created_at DESC;
        ELSE
            OPEN p_cur FOR
                SELECT b.building_id, b.name, b.address, b.total_units,
                       a.name AS area_name, u.full_name AS owner_name
                  FROM buildings b
                  JOIN areas a ON a.area_id  = b.area_id
                  JOIN users u ON u.nid      = b.owner_nid
                 WHERE b.owner_nid = p_owner_nid
                 ORDER BY b.created_at DESC;
        END IF;
    END;

    PROCEDURE sp_get_building_list(p_owner_nid IN VARCHAR2, p_cur OUT SYS_REFCURSOR) IS
    BEGIN
        IF p_owner_nid IS NULL THEN
            OPEN p_cur FOR SELECT building_id, name FROM buildings ORDER BY name;
        ELSE
            OPEN p_cur FOR SELECT building_id, name FROM buildings
             WHERE owner_nid = p_owner_nid ORDER BY name;
        END IF;
    END;

    PROCEDURE sp_add_building(
        p_admin_nid  IN VARCHAR2, p_building_id IN NUMBER,
        p_name       IN VARCHAR2, p_address     IN VARCHAR2,
        p_area_id    IN NUMBER,   p_owner_nid   IN VARCHAR2,
        p_units      IN NUMBER)
    IS
    BEGIN
        IF pkg_auth.fn_has_role(p_admin_nid,'admin') = 0 THEN
            RAISE_APPLICATION_ERROR(-20003,'Only admin can manage buildings');
        END IF;
        IF pkg_auth.fn_has_role(p_owner_nid,'house_owner') = 0 THEN
            BEGIN
                INSERT INTO user_roles(user_nid, role) VALUES (p_owner_nid,'house_owner');
            EXCEPTION WHEN DUP_VAL_ON_INDEX THEN NULL;
            END;
        END IF;
        INSERT INTO buildings(building_id, name, address, area_id, owner_nid, total_units)
        VALUES (p_building_id, p_name, p_address, p_area_id, p_owner_nid, NVL(p_units,1));
    END;


END pkg_city;
/

-- ---------------------------------------------------------------------------------------------

CREATE OR REPLACE PACKAGE pkg_announcements AS
    PROCEDURE sp_post_announcement(
        p_id IN NUMBER, p_title IN VARCHAR2, p_content IN CLOB,
        p_target_role IN VARCHAR2, p_created_by IN VARCHAR2);
    PROCEDURE sp_get_announcements_for_user(p_nid IN VARCHAR2, p_cur OUT SYS_REFCURSOR);
END pkg_announcements;
/
CREATE OR REPLACE PACKAGE BODY pkg_announcements AS

    PROCEDURE sp_post_announcement(
        p_id IN NUMBER, p_title IN VARCHAR2, p_content IN CLOB,
        p_target_role IN VARCHAR2, p_created_by IN VARCHAR2)
    IS
    BEGIN
        IF pkg_auth.fn_has_role(p_created_by, 'admin') = 0 THEN
            RAISE_APPLICATION_ERROR(-20016, 'Only admin can post announcements');
        END IF;
        INSERT INTO announcements(id, title, content, target_role, created_by)
        VALUES (p_id, p_title, p_content, p_target_role, p_created_by);
    END;

    PROCEDURE sp_get_announcements_for_user(p_nid IN VARCHAR2, p_cur OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cur FOR
            SELECT a.id, a.title,
                   DBMS_LOB.SUBSTR(a.content, 2000, 1) AS content,
                   a.target_role, a.created_by, a.created_at,
                   u.full_name AS author,
                   TO_CHAR(a.created_at,'Mon DD, YYYY HH24:MI') AS ts
              FROM announcements a
              JOIN users u ON a.created_by = u.nid
             WHERE a.target_role = 'all'
                OR a.target_role IN (
                       SELECT role FROM user_roles WHERE user_nid = p_nid
                   )
             ORDER BY a.created_at DESC;
    END;

END pkg_announcements;
/

-- ---------------------------------------------------------------------------------------------

CREATE OR REPLACE PACKAGE pkg_criminals AS
    PROCEDURE sp_add_criminal_record(
        p_police_nid IN VARCHAR2, p_record_id  IN NUMBER,
        p_citizen_nid IN VARCHAR2, p_report_id IN NUMBER,
        p_offense    IN VARCHAR2, p_desc IN CLOB);
    PROCEDURE sp_get_all_criminal_records(p_cur OUT SYS_REFCURSOR);
    PROCEDURE sp_get_citizen_records(p_nid IN VARCHAR2, p_cur OUT SYS_REFCURSOR);
END pkg_criminals;
/

CREATE OR REPLACE PACKAGE BODY pkg_criminals AS

    PROCEDURE sp_add_criminal_record(
        p_police_nid  IN VARCHAR2, p_record_id   IN NUMBER,
        p_citizen_nid IN VARCHAR2, p_report_id   IN NUMBER,
        p_offense     IN VARCHAR2, p_desc        IN CLOB)
    IS
        v_rid NUMBER := NULLIF(p_report_id, 0);
    BEGIN
        IF pkg_auth.fn_has_role(p_police_nid,'police') = 0
           AND pkg_auth.fn_has_role(p_police_nid,'admin') = 0 THEN
            RAISE_APPLICATION_ERROR(-20012,'Only police/admin can add records');
        END IF;
        INSERT INTO criminal_records(record_id, citizen_nid, report_id, offense, description, recorded_by)
        VALUES (p_record_id, p_citizen_nid, v_rid, p_offense, p_desc, p_police_nid);
    END;

    PROCEDURE sp_get_all_criminal_records(p_cur OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cur FOR
            SELECT cr.record_id, cr.offense,
                   DBMS_LOB.SUBSTR(cr.description, 400, 1) AS description,
                   TO_CHAR(cr.recorded_at,'YYYY-MM-DD HH24:MI') AS ts,
                   u.full_name AS citizen,
                   p.full_name AS officer,
                   cr.report_id
              FROM criminal_records cr
              JOIN users u ON u.nid = cr.citizen_nid
              JOIN users p ON p.nid = cr.recorded_by
             ORDER BY cr.recorded_at DESC;
    END;

    PROCEDURE sp_get_citizen_records(p_nid IN VARCHAR2, p_cur OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cur FOR
            SELECT cr.record_id, cr.offense,
                   DBMS_LOB.SUBSTR(cr.description, 400, 1) AS description,
                   TO_CHAR(cr.recorded_at,'YYYY-MM-DD HH24:MI') AS ts,
                   u.full_name AS citizen,
                   p.full_name AS officer,
                   cr.report_id
              FROM criminal_records cr
              JOIN users u ON u.nid = cr.citizen_nid
              JOIN users p ON p.nid = cr.recorded_by
             WHERE cr.citizen_nid = p_nid
             ORDER BY cr.recorded_at DESC;
    END;

END pkg_criminals;
/

-- -----------------------------------------------------------------
CREATE OR REPLACE PACKAGE pkg_reports AS
    PROCEDURE sp_file_report(
        p_report_id   IN NUMBER,  p_reporter_nid IN VARCHAR2,
        p_is_anonymous IN NUMBER, p_area_id      IN NUMBER,
        p_title       IN VARCHAR2, p_desc        IN CLOB);
    PROCEDURE sp_get_all_reports(p_cur OUT SYS_REFCURSOR);
    PROCEDURE sp_get_my_reports(p_nid IN VARCHAR2, p_cur OUT SYS_REFCURSOR);
    PROCEDURE sp_get_police_queue(p_cur OUT SYS_REFCURSOR);
    PROCEDURE sp_get_verified_reports(p_cur OUT SYS_REFCURSOR);
    PROCEDURE sp_review_report(
        p_police_nid IN VARCHAR2, p_report_id IN NUMBER, p_action IN VARCHAR2);
END pkg_reports;
/

CREATE OR REPLACE PACKAGE BODY pkg_reports AS

    PROCEDURE sp_file_report(
        p_report_id    IN NUMBER,  p_reporter_nid IN VARCHAR2,
        p_is_anonymous IN NUMBER,  p_area_id      IN NUMBER,
        p_title        IN VARCHAR2, p_desc        IN CLOB)
    IS
        v_reporter VARCHAR2(6) := p_reporter_nid;
    BEGIN
        IF p_is_anonymous = 1 THEN v_reporter := NULL; END IF;
        INSERT INTO crime_reports(report_id, reporter_nid, is_anonymous, area_id, title, description)
        VALUES (p_report_id, v_reporter, p_is_anonymous, p_area_id, p_title, p_desc);
    END;

    PROCEDURE sp_get_all_reports(p_cur OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cur FOR
            SELECT r.report_id, r.title, r.status, r.is_anonymous, r.area_id,
                   a.name AS area_name,
                   CASE WHEN r.is_anonymous = 1 THEN '(anonymous)'
                        ELSE u.full_name END AS reporter,
                   TO_CHAR(r.created_at,'YYYY-MM-DD HH24:MI') AS ts
              FROM crime_reports r
              LEFT JOIN areas a ON a.area_id    = r.area_id
              LEFT JOIN users u ON u.nid        = r.reporter_nid
             ORDER BY r.created_at DESC;
    END;

    PROCEDURE sp_get_my_reports(p_nid IN VARCHAR2, p_cur OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cur FOR
            SELECT r.report_id, r.title, r.status, r.is_anonymous,
                   a.name AS area_name,
                   TO_CHAR(r.created_at,'YYYY-MM-DD HH24:MI') AS ts
              FROM crime_reports r
              LEFT JOIN areas a ON a.area_id = r.area_id
             WHERE r.reporter_nid = p_nid
             ORDER BY r.created_at DESC;
    END;

    PROCEDURE sp_get_police_queue(p_cur OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cur FOR
            SELECT r.report_id, r.title, r.status, r.is_anonymous,
                   DBMS_LOB.SUBSTR(r.description, 400, 1) AS description,
                   a.name AS area_name,
                   CASE WHEN r.is_anonymous = 1 THEN '(anonymous)'
                        ELSE u.full_name END AS reporter,
                   r.reporter_nid,
                   TO_CHAR(r.created_at,'YYYY-MM-DD HH24:MI') AS ts
              FROM crime_reports r
              LEFT JOIN areas a ON a.area_id = r.area_id
              LEFT JOIN users u ON u.nid     = r.reporter_nid
             ORDER BY
                   CASE r.status
                     WHEN 'pending'  THEN 0
                     WHEN 'verified' THEN 1
                     WHEN 'solved'   THEN 2
                     ELSE 3
                   END,
                   r.created_at DESC;
    END;

    PROCEDURE sp_get_verified_reports(p_cur OUT SYS_REFCURSOR) IS
    BEGIN
        OPEN p_cur FOR
            SELECT report_id, title FROM crime_reports
             WHERE status IN ('verified','solved')
             ORDER BY report_id DESC;
    END;

    PROCEDURE sp_review_report(
        p_police_nid IN VARCHAR2, p_report_id IN NUMBER, p_action IN VARCHAR2)
    IS
    BEGIN
        IF pkg_auth.fn_has_role(p_police_nid,'police') = 0
           AND pkg_auth.fn_has_role(p_police_nid,'admin') = 0 THEN
            RAISE_APPLICATION_ERROR(-20010,'Only police/admin can review reports');
        END IF;
        IF p_action NOT IN ('verified','rejected','solved') THEN
            RAISE_APPLICATION_ERROR(-20011,'Invalid action');
        END IF;
        UPDATE crime_reports
           SET status      = p_action,
               reviewed_by = p_police_nid,
               reviewed_at = SYSTIMESTAMP
         WHERE report_id   = p_report_id;
    END;

END pkg_reports;
/