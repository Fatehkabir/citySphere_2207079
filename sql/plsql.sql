CREATE OR REPLACE PACKAGE pkg_auth AS
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