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

?>