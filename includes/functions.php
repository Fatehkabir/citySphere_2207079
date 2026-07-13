<?php 
require_once __DIR__ . '/../config/db.php';

function fetch_cursor(string $sql, array $binds = []): array {
    $conn = db();
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        $e = oci_error($conn);
        throw new RuntimeException('oci_parse failed: ' . $e['message']);
    }

    $locals = [];
    foreach ($binds as $name => $value) {
        $locals[$name] = $value;
        oci_bind_by_name($stmt, ':' . $name, $locals[$name]);
    }

    $cursor = oci_new_cursor($conn);
    oci_bind_by_name($stmt, ':cur', $cursor, -1, OCI_B_CURSOR);

    if (!oci_execute($stmt, OCI_DEFAULT)) {
        $e = oci_error($stmt);
        oci_free_statement($stmt);
        throw new RuntimeException('fetch_cursor execute: ' . $e['message']);
    }

    if (!oci_execute($cursor)) {
        $e = oci_error($cursor);
        oci_free_statement($cursor);
        oci_free_statement($stmt);
        throw new RuntimeException('fetch_cursor cursor execute: ' . $e['message']);
    }

    $rows = [];
    while ($row = oci_fetch_assoc($cursor)) {
        $rows[] = $row;
    }

    oci_free_statement($cursor);
    oci_free_statement($stmt);
    return $rows;
}

function exec_plsql(string $sql, array $binds = [], array $out_names = []): array {
    $conn = db();
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        $e = oci_error($conn);
        throw new RuntimeException('oci_parse failed: ' . $e['message']);
    }

    $locals = [];
    foreach ($binds as $name => $value) {
        $locals[$name] = $value;
        oci_bind_by_name($stmt, ':' . $name, $locals[$name]);
    }

    $out_locals = [];
    foreach ($out_names as $name) {
        $out_locals[$name] = null;
        oci_bind_by_name($stmt, ':' . $name, $out_locals[$name], 4000);
    }

    if (!oci_execute($stmt, OCI_DEFAULT)) {
        $e = oci_error($stmt);
        oci_free_statement($stmt);
        throw new RuntimeException('exec_plsql [' . $sql . ']: ' . $e['message']);
    }

    oci_commit($conn);
    oci_free_statement($stmt);
    return $out_locals;
}

function run_plsql(string $sql, array $binds = []): void {
    $conn = db();
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        $e = oci_error($conn);
        throw new RuntimeException('oci_parse failed: ' . $e['message']);
    }

    $locals = [];
    foreach ($binds as $name => $value) {
        $locals[$name] = $value;
        oci_bind_by_name($stmt, ':' . $name, $locals[$name]);
    }

    if (!oci_execute($stmt, OCI_DEFAULT)) {
        $e = oci_error($stmt);
        oci_free_statement($stmt);
        throw new RuntimeException('run_plsql: ' . $e['message']);
    }

    oci_commit($conn);
    oci_free_statement($stmt);
}

function register_user(string $nid, string $name, string $email,
                        string $pw, ?string $phone): void {
    if (!preg_match('/^[0-9]{6}$/', $nid)) {
        throw new RuntimeException('NID must be exactly 6 digits (0–9).');
    }
    run_plsql(
        'BEGIN pkg_auth.sp_register_user(:nid,:n,:e,:h,:p); END;',
        ['nid' => $nid,
         'n'   => $name,
         'e'   => strtolower(trim($email)),
         'h'   => $pw,
         'p'   => $phone ?: null]
    );
}



function get_dashboard_stats(): array {
    $out = exec_plsql(
        'BEGIN pkg_dashboard.sp_get_dashboard_stats(:tr,:pr,:ta,:tb); END;',
        [],
        ['tr', 'pr', 'ta', 'tb']
    );
    return [
        'reports'   => (int)($out['tr'] ?? 0),
        'pending'   => (int)($out['pr'] ?? 0),
        'areas'     => (int)($out['ta'] ?? 0),
        'buildings' => (int)($out['tb'] ?? 0),
    ];
}
function get_my_recent_reports(string $nid): array {
    return fetch_cursor(
        'BEGIN pkg_dashboard.sp_get_my_recent_reports(:nid, :cur); END;',
        ['nid' => $nid]
    );
}

function add_area(string $adminNid, int $areaId, string $name, string $city): void {
    run_plsql(
        'BEGIN pkg_city.sp_add_area(:a,:aid,:n,:c); END;',
        ['a' => $adminNid, 'aid' => $areaId, 'n' => $name, 'c' => $city]
    );
}

?>