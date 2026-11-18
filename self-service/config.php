<?php
// Project eke common DB config eka use karanawa
require_once __DIR__ . '/../config/db.php';

define('EMP_TABLE', 'employees');
define('COL_EMP_NO', 'emp_no');   // username
define('COL_NIC', 'nic');         // password column -> phpMyAdmin eke NIC column nama methana denna (eg: nic_no)

if (session_status() === PHP_SESSION_NONE) session_start();

// emp_no eken employee row eka gannawa
function find_employee(string $empNo): ?array {
    $db = getDB();
    $st = $db->prepare('SELECT * FROM `'.EMP_TABLE.'` WHERE `'.COL_EMP_NO.'` = ? LIMIT 1');
    $st->bind_param('s', $empNo);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    $st->close();
    return $row ?: null;
}

function require_login(): void {
    if (empty($_SESSION['emp'])) { header('Location: index.php'); exit; }
}
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
