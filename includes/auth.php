<?php 

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

function flash(string $msg, string $type='info'):void{
    $_SESSION['flash'][]=['msg'=>$msg,'type'=>$type];
}
function pop_flashes(): array {
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}


function load_roles_for(int $uid): array {
    $rows = fetch_cursor(
        'BEGIN pkg_auth.sp_get_roles_for_user(:uid, :cur); END;',
        ['uid' => $uid]
    );
    return array_column($rows, 'ROLE');
}
function current_roles(): array  { return $_SESSION['roles'] ?? []; }
function has_role(string $r): bool { return in_array($r, current_roles(), true); }
function login_user(array $user): void {
    session_regenerate_id(true);       
    $_SESSION['user']  = $user;
    $_SESSION['roles'] = load_roles_for((string)$user['NID']);
}


function revoke_role(string $adminNid, string $nid, string $role): void {
    run_plsql(
        'BEGIN pkg_auth.sp_revoke_role(:a,:u,:r); END;',
        ['a' => $adminNid, 'u' => $nid, 'r' => $role]
    );
}


function require_login(): void {
    if (!current_user()) { header('Location: login.php'); exit; }
}
function require_role(string $role): void {
    require_login();
    if (!has_role($role) && !has_role('admin')) {
        http_response_code(403); die('403 – Forbidden');
    }
}


function current_user(): ?array {
    $u = $_SESSION['user'] ?? null;
    if ($u !== null && !array_key_exists('NID', $u)) {
        session_destroy();
        return null;
    }
    return $u;
}
function current_user_nid(): ?string {
    $u = current_user();
    return ($u && isset($u['NID'])) ? (string)$u['NID'] : null;
}

function e(mixed $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}




?>