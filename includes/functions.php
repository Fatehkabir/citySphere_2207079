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

//---------------------------------------------------------------

function make_clob(string $text): OCILob {
    $lob = oci_new_descriptor(db(), OCI_D_LOB);
    $lob->writetemporary($text, OCI_TEMP_CLOB);
    return $lob;
}

//---------------------------------------------------------------

function authenticate(string $email, string $password): ?array {
    $rows = fetch_cursor(
        'BEGIN pkg_auth.sp_get_user_by_email(:email, :cur); END;',
        ['email' => strtolower(trim($email))]
    );
    if ($rows && password_verify($password, $rows[0]['PASSWORD_HASH'])) {
        return $rows[0];
    }
    if ($rows && $password === $rows[0]['PASSWORD_HASH']) {
        return $rows[0];
    }
    return null;
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
function get_user(string $nid): ?array {
    $rows = fetch_cursor(
        'BEGIN pkg_auth.sp_get_user_by_nid(:nid, :cur); END;',
        ['nid' => $nid]
    );
    return $rows[0] ?? null;
}

function update_profile(string $nid, string $name, ?string $phone,
                         ?string $photo): void {
    run_plsql(
        'BEGIN pkg_auth.sp_update_profile(:nid,:n,:p,:photo); END;',
        ['nid'   => $nid,
         'n'     => $name,
         'p'     => $phone ?: null,
         'photo' => $photo ?: null]
    );
}

//---------------------------------------------------------------


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

//---------------------------------------------------------------

function get_areas(): array {
    return fetch_cursor('BEGIN pkg_city.sp_get_areas(:cur); END;');
}

function get_area_list(): array {
    return fetch_cursor('BEGIN pkg_city.sp_get_area_list(:cur); END;');
}


function add_area(string $adminNid, int $areaId, string $name, string $city): void {
    run_plsql(
        'BEGIN pkg_city.sp_add_area(:a,:aid,:n,:c); END;',
        ['a' => $adminNid, 'aid' => $areaId, 'n' => $name, 'c' => $city]
    );
}

//---------------------------------------------------------------

function get_announcements_for_user(string $nid): array {
    return fetch_cursor(
        'BEGIN pkg_announcements.sp_get_announcements_for_user(:nid, :cur); END;',
        ['nid' => $nid]
    );
}

function post_announcement(int $annId, string $title, string $content,
                            string $role, string $nid): void {
    $conn = db();
    $stmt = oci_parse($conn,
        'BEGIN pkg_announcements.sp_post_announcement(:id,:t,:c,:r,:u); END;');

    $idL = $annId; $tL = $title; $rL = $role; $uL = $nid;
    oci_bind_by_name($stmt, ':id', $idL);
    oci_bind_by_name($stmt, ':t',  $tL);
    oci_bind_by_name($stmt, ':r',  $rL);
    oci_bind_by_name($stmt, ':u',  $uL);

    $cLob = make_clob($content);
    oci_bind_by_name($stmt, ':c', $cLob, -1, OCI_B_CLOB);

    if (!oci_execute($stmt, OCI_DEFAULT)) {
        $e = oci_error($stmt);
        $cLob->close();
        oci_free_statement($stmt);
        throw new RuntimeException('post_announcement: ' . $e['message']);
    }
    oci_commit($conn);
    $cLob->close();
    oci_free_statement($stmt);
}



//---------------------------------------------------------------
function get_buildings(?string $ownerNid = null): array {
    $owner = $ownerNid;
    return fetch_cursor(
        'BEGIN pkg_city.sp_get_buildings(:owner, :cur); END;',
        ['owner' => $owner]
    );
}

function get_building_list(?string $ownerNid = null): array {
    $owner = $ownerNid;
    return fetch_cursor(
        'BEGIN pkg_city.sp_get_building_list(:owner, :cur); END;',
        ['owner' => $owner]
    );
}

function add_building(string $adminNid, int $buildingId, string $name,
                       string $address, int $areaId,
                       string $ownerNid, int $units): void {
    run_plsql(
        'BEGIN pkg_city.sp_add_building(:a,:bid,:n,:addr,:ar,:o,:u); END;',
        ['a'   => $adminNid, 'bid' => $buildingId,
         'n'   => $name,     'addr' => $address,
         'ar'  => $areaId,   'o'    => $ownerNid,
         'u'   => $units]
    );
}
//---------------------------------------------------------------
function get_criminal_records(?string $nid = null): array {
    if ($nid === null) {
        return fetch_cursor('BEGIN pkg_criminals.sp_get_all_criminal_records(:cur); END;');
    }
    return fetch_cursor(
        'BEGIN pkg_criminals.sp_get_citizen_records(:nid, :cur); END;',
        ['nid' => $nid]
    );
}

function add_criminal_record(string $policeNid, int $recordId, string $citizenNid,
                               ?int $reportId, string $offense, string $desc): void {
    $rid  = $reportId ?? 0;
    $conn = db();
    $stmt = oci_parse($conn,
        'BEGIN pkg_criminals.sp_add_criminal_record(:p,:rec,:c,:r,:o,:d); END;');

    $pL = $policeNid; $recL = $recordId; $cL = $citizenNid;
    $rL = $rid; $oL = $offense;
    oci_bind_by_name($stmt, ':p',   $pL);
    oci_bind_by_name($stmt, ':rec', $recL);
    oci_bind_by_name($stmt, ':c',   $cL);
    oci_bind_by_name($stmt, ':r',   $rL);
    oci_bind_by_name($stmt, ':o',   $oL);

    $descLob = make_clob($desc);
    oci_bind_by_name($stmt, ':d', $descLob, -1, OCI_B_CLOB);

    if (!oci_execute($stmt, OCI_DEFAULT)) {
        $e = oci_error($stmt);
        $descLob->close();
        oci_free_statement($stmt);
        throw new RuntimeException('add_criminal_record: ' . $e['message']);
    }
    oci_commit($conn);
    $descLob->close();
    oci_free_statement($stmt);
}

//---------------------------------------------------------------

function file_report(int $reportId, string $reporterNid, bool $anonymous,
                      int $areaId, string $title, string $desc): void {
    $anon = $anonymous ? 1 : 0;
    $rid  = $anonymous ? null : $reporterNid;

    $conn = db();
    $stmt = oci_parse($conn,
        'BEGIN pkg_reports.sp_file_report(:rid,:r,:a,:ar,:t,:d); END;');

    $ridL = $reportId; $rLocal = $rid; $aLocal = $anon;
    $arLocal = $areaId; $tLocal = $title;
    oci_bind_by_name($stmt, ':rid', $ridL);
    oci_bind_by_name($stmt, ':r',   $rLocal);
    oci_bind_by_name($stmt, ':a',   $aLocal);
    oci_bind_by_name($stmt, ':ar',  $arLocal);
    oci_bind_by_name($stmt, ':t',   $tLocal);

    $descLob = make_clob($desc);
    oci_bind_by_name($stmt, ':d', $descLob, -1, OCI_B_CLOB);

    if (!oci_execute($stmt, OCI_DEFAULT)) {
        $e = oci_error($stmt);
        $descLob->close();
        oci_free_statement($stmt);
        throw new RuntimeException('file_report: ' . $e['message']);
    }
    oci_commit($conn);
    $descLob->close();
    oci_free_statement($stmt);
}

function get_reports(?string $nid = null): array {
    if ($nid === null) {
        return fetch_cursor('BEGIN pkg_reports.sp_get_all_reports(:cur); END;');
    }
    return fetch_cursor(
        'BEGIN pkg_reports.sp_get_my_reports(:nid, :cur); END;',
        ['nid' => $nid]
    );
}

function get_police_queue(): array {
    return fetch_cursor('BEGIN pkg_reports.sp_get_police_queue(:cur); END;');
}

function get_verified_reports(): array {
    return fetch_cursor('BEGIN pkg_reports.sp_get_verified_reports(:cur); END;');
}

function update_report_status(string $policeNid, int $reportId, string $action): void {
    run_plsql(
        'BEGIN pkg_reports.sp_review_report(:p,:r,:a); END;',
        ['p' => $policeNid, 'r' => $reportId, 'a' => $action]
    );
}

//---------------------------------------------------------------
function get_rentals(?string $nid = null): array {
    if ($nid === null) {
        return fetch_cursor('BEGIN pkg_rentals.sp_get_all_rentals(:cur); END;');
    }
    return fetch_cursor(
        'BEGIN pkg_rentals.sp_get_my_rentals(:nid, :cur); END;',
        ['nid' => $nid]
    );
}

function assign_renter(string $ownerNid, int $rentalId, int $buildingId,
                        string $renterNid, string $unitNo, float $amount): void {
    run_plsql(
        'BEGIN pkg_rentals.sp_assign_renter(:o,:rid,:b,:r,:u,:a); END;',
        ['o'   => $ownerNid,  'rid' => $rentalId,
         'b'   => $buildingId,'r'   => $renterNid,
         'u'   => $unitNo,    'a'   => $amount]
    );
}

function update_payment_status(string $actorNid, int $rentalId, string $status): void {
    run_plsql(
        'BEGIN pkg_rentals.sp_update_payment(:a,:r,:s); END;',
        ['a' => $actorNid, 'r' => $rentalId, 's' => $status]
    );
}

function end_rental(int $rentalId): void {
    run_plsql(
        'BEGIN pkg_rentals.sp_end_rental(:r); END;',
        ['r' => $rentalId]
    );
}

function audit_pending_rentals(string $adminNid): int {
    $out = exec_plsql(
        'BEGIN pkg_rentals.sp_audit_pending_rentals(:a,:p); END;',
        ['a' => $adminNid],
        ['p']
    );
    return (int)($out['p'] ?? 0);
}

//---------------------------------------------------------------

function get_all_users(): array {
    return fetch_cursor('BEGIN pkg_users.sp_get_all_users_with_roles(:cur); END;');
}

function get_users_list(): array {
    return fetch_cursor('BEGIN pkg_users.sp_get_users(:cur); END;');
}

function get_house_owners(): array {
    return fetch_cursor('BEGIN pkg_users.sp_get_house_owners(:cur); END;');
}

function grant_role(string $adminNid, string $nid, string $role): void {
    run_plsql(
        'BEGIN pkg_auth.sp_grant_role(:a,:u,:r); END;',
        ['a' => $adminNid, 'u' => $nid, 'r' => $role]
    );
}


?>