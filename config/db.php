<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'citysphere');
define('DB_USER', 'root');
define('DB_PASS', '');

require_once __DIR__ . '/../config/db.php';

function db(): PDO {
    static $conn = null;
    if ($conn === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $conn = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_CASE               => PDO::CASE_UPPER,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $conn;
}

function db_exec(string $sql, array $binds = [], array $out = []): array {
    $pdo = db();
    try {
        foreach ($out as $sessionVar) {
            $var = ltrim($sessionVar, '@');
            $pdo->exec("SET @{$var} = NULL");
        }
        $st = $pdo->prepare($sql);
        $st->execute($binds);
        while ($st->nextRowset()) {

        }
        $outVals = [];
        foreach ($out as $key => $sessionVar) {
            $var = ltrim($sessionVar, '@');
            $row = $pdo->query("SELECT @{$var} AS v")->fetch();
            $outVals[$key] = $row['V'] ?? null;
        }
        return $outVals;
    } catch (PDOException $e) {
        throw new RuntimeException($e->getMessage());
    }
}


?>