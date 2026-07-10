<?php 

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

function flash(string $msg, string $type='info'):void{
    $_SESSION['flash'][]=['msg'=>$msg,'type'=>$type];
}

function load_roles_for(int $uid): array {
    $rows = fetch_cursor(
        'BEGIN pkg_auth.sp_get_roles_for_user(:uid, :cur); END;',
        ['uid' => $uid]
    );
    return array_column($rows, 'ROLE');
}
function login_user(array $user): void {
    session_regenerate_id(true);       
    $_SESSION['user']  = $user;
    $_SESSION['roles'] = load_roles_for((string)$user['NID']);
}

function grant_role(string $adminNid, string $nid, string $role): void {
    run_plsql(
        'BEGIN pkg_auth.sp_grant_role(:a,:u,:r); END;',
        ['a' => $adminNid, 'u' => $nid, 'r' => $role]
    );
}

function revoke_role(string $adminNid, string $nid, string $role): void {
    run_plsql(
        'BEGIN pkg_auth.sp_revoke_role(:a,:u,:r); END;',
        ['a' => $adminNid, 'u' => $nid, 'r' => $role]
    );
}





?>