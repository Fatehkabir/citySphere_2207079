<?php

define('ORA_USER', 'citysphere');
define('ORA_PASS', 'citysphere123');
define('ORA_DSN',  'localhost:1521/XE');

require_once __DIR__ . '/../config/db.php';

function db() {
    static $c = null;
    if ($c === null) {
        $c = oci_connect(ORA_USER, ORA_PASS, ORA_DSN, 'AL32UTF8');
        if (!$c) {
            $e = oci_error();
            throw new RuntimeException('Oracle connect failed: ' . $e['message']);
        }
    }
    return $c;
}

function db_select(string $sql, array $binds=[]):array{
    $stmt=oci_parse(db(),$sql);
    foreach($binds as $k=>$v){
        oci_bind_by_name($stmt,$k,$binds[$k]);
    }
        if (!oci_execute($stmt, OCI_DEFAULT)) {
        $e = oci_error($stmt); oci_free_statement($stmt);
        throw new RuntimeException('ORA SELECT: ' . $e['message']);
    }
    $rows = [];
    oci_fetch_all($stmt, $rows, 0, -1, OCI_FETCHALL_ASSOC);
    oci_free_statement($stmt);
    return $rows;
}

function db_select_one(string $sql, array $binds = []): ?array {
    $rows = db_select($sql, $binds);
    return $rows[0] ?? null;
}

function db_exec(string $sql, array $binds = []): void {
    $stmt = oci_parse(db(), $sql);
    foreach ($binds as $k => $v) {
        oci_bind_by_name($stmt, $k, $binds[$k]);
    }
    if (!oci_execute($stmt, OCI_DEFAULT)) {
        $e = oci_error($stmt); oci_free_statement($stmt);
        throw new RuntimeException('ORA EXEC: ' . $e['message']);
    }
    oci_commit(db());
    oci_free_statement($stmt);
}

function db_call(string $sql, array $binds = [], array $out = []): array {
    $stmt = oci_parse(db(), $sql);

    foreach ($binds as $k => $v) {
        oci_bind_by_name($stmt, $k, $binds[$k]);
    }
    $outVars = [];
    foreach ($out as $k) {
        $outVars[$k] = null;
        oci_bind_by_name($stmt, $k, $outVars[$k], 4000);
    }
    if (!oci_execute($stmt, OCI_DEFAULT)) {
        $e = oci_error($stmt); oci_free_statement($stmt);
        throw new RuntimeException('ORA CALL: ' . $e['message']);
    }
    oci_commit(db());
    oci_free_statement($stmt);
    return $outVars;
}

?>