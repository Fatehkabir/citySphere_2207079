<?php

if(session_status()===PHP_SESSION_NONE){
    session_start();
}

function load_roles_for(int $uid): array {
    $rows = db_select(
        'SELECT role FROM user_roles WHERE user_id = :uid',
        [':uid' => $uid]
    );
    return array_map(fn($r) => $r['ROLE'], $rows);
}

function login_user(array $user):void{
    $_SESSION['user']  = $user;
    $_SESSION['roles'] = load_roles_for((int)$user['USER_ID']);
}

function flash(string $msg, string $type = 'info'): void {
    $_SESSION['flash'][] = ['msg' => $msg, 'type' => $type];
}


function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}


function check_csrf(): void {
    $t = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $t)) {
        http_response_code(400);
        die('Invalid CSRF token');
    }
}


function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}



?>