CREATE OR REPLACE PACKAGE pkg_auth AS
 PROCEDURE sp_register_user(
    p_nid IN VARCHAR2, p_name IN VARCHAR2, p_email IN VARCHAR2, p_hash IN VARCHAR2, p_phone IN VARCHAR2
 );
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
END pkg_auth;
 /    