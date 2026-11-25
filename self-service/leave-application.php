<?php
require 'config.php';
require_login();
$db     = getDB();
$emp    = find_employee($_SESSION['emp'][COL_EMP_NO]) ?? $_SESSION['emp'];
$empNo  = $emp[COL_EMP_NO];
$navName = $emp['name_with_initials'] ?? $emp['full_name'] ?? $empNo;

$db->query("CREATE TABLE IF NOT EXISTS leave_balances (
  id INT AUTO_INCREMENT PRIMARY KEY, emp_no VARCHAR(50) NOT NULL, leave_year INT NOT NULL,
  leave_type VARCHAR(100) NOT NULL, leave_amount DECIMAL(6,2) NOT NULL DEFAULT 0,
  UNIQUE KEY uq (emp_no, leave_year, leave_type)) CHARACTER SET utf8mb4");
$db->query("CREATE TABLE IF NOT EXISTS leave_applications (
  id INT AUTO_INCREMENT PRIMARY KEY, emp_no VARCHAR(50) NOT NULL, leave_year INT NOT NULL,
  reason VARCHAR(255) NOT NULL, category VARCHAR(30) NOT NULL, leave_type VARCHAR(100) NOT NULL,
  covering VARCHAR(255) DEFAULT NULL, applied_at DATETIME NOT NULL,
  from_dt DATETIME NOT NULL, to_dt DATETIME NOT NULL,
  days DECIMAL(7,3) NOT NULL DEFAULT 0, hours INT DEFAULT 0, minutes INT DEFAULT 0,
  status VARCHAR(20) NOT NULL DEFAULT 'Pending',
  approver_emp_no VARCHAR(50) DEFAULT NULL, decided_at DATETIME DEFAULT NULL,
  INDEX (emp_no), INDEX (status)) CHARACTER SET utf8mb4");
$db->query("CREATE TABLE IF NOT EXISTS lieu_leave_balances (
  id INT AUTO_INCREMENT PRIMARY KEY, emp_no VARCHAR(50) NOT NULL, worked_date DATE NOT NULL,
  amount DECIMAL(5,2) NOT NULL DEFAULT 0, used DECIMAL(5,2) NOT NULL DEFAULT 0, INDEX (emp_no)) CHARACTER SET utf8mb4");

function qa(mysqli $db, string $sql, string $types = '', array $p = []): array {
    if ($types === '') { $r = $db->query($sql); return $r ? $r->fetch_all(MYSQLI_ASSOC) : []; }
    $st = $db->prepare($sql); $st->bind_param($types, ...$p); $st->execute();
    $res = $st->get_result(); $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : []; $st->close(); return $rows;
}
function ex(mysqli $db, string $sql, string $types, array $p): void {
    $st = $db->prepare($sql); $st->bind_param($types, ...$p); $st->execute(); $st->close();
}
function nm(array $r): string { return $r['full_name'] ?? $r['office_use_name'] ?? $r['emp_no']; }

function ensure_balances(mysqli $db, string $empNo, int $year): void {
    foreach (['Casual Leave'=>2,'No Pay Leave'=>0,'Leave'=>0,'Election Leave'=>0] as $t => $a)
        ex($db, "INSERT IGNORE INTO leave_balances (emp_no,leave_year,leave_type,leave_amount) VALUES (?,?,?,?)", 'sisd', [$empNo,$year,$t,$a]);
}
function balances(mysqli $db, string $empNo, int $year): array {
    ensure_balances($db, $empNo, $year);
    $out = [];
    foreach (qa($db, "SELECT * FROM leave_balances WHERE emp_no=? AND leave_year=? ORDER BY id", 'si', [$empNo,$year]) as $b) {
        $u = qa($db, "SELECT status, COALESCE(SUM(days),0) d FROM leave_applications WHERE emp_no=? AND leave_year=? AND leave_type=? AND status IN ('Approved','Pending') GROUP BY status", 'sis', [$empNo,$year,$b['leave_type']]);
        $taken = 0; $pend = 0;
        foreach ($u as $x) { if ($x['status'] === 'Approved') $taken = (float)$x['d']; else $pend = (float)$x['d']; }
        $amt = (float)$b['leave_amount'];
        $out[] = ['year'=>$year,'type'=>$b['leave_type'],'amount'=>$amt,'taken'=>$taken,'available'=>$amt-$taken,'balance'=>$amt-$taken-$pend,'pending'=>$pend];
    }
    return $out;
}
function n2($v): string { return rtrim(rtrim(number_format((float)$v, 2, '.', ''), '0'), '.') ?: '0'; }

function leave_calc(string $cat, string $fd, string $ft, string $td, string $tt): array {
    $r = ['days'=>0,'hours'=>0,'minutes'=>0,'from'=>'','to'=>'','err'=>''];
    if ($cat === '') { $r['err'] = 'Please select the leave category.'; return $r; }
    if ($fd === '' || $td === '') { $r['err'] = 'Please select From and To dates.'; return $r; }
    if ($td < $fd) { $r['err'] = 'To Date must be on or after From Date.'; return $r; }
    if ($cat === 'Full Day') {
        $r['days'] = (strtotime($td) - strtotime($fd)) / 86400 + 1;
        $r['from'] = "$fd 00:00:00"; $r['to'] = "$td 23:59:59";
    } elseif ($cat === 'Half Day') {
        if ($fd !== $td) { $r['err'] = 'Half Day leave must be for a single date.'; return $r; }
        $start = $ft !== '' ? $ft : '08:00';
        $r['days'] = 0.5; $r['hours'] = 4;
        $r['from'] = "$fd $start:00"; $r['to'] = date('Y-m-d H:i:s', strtotime("$fd $start") + 4*3600);
    } elseif ($cat === 'Short Leave') {
        if ($fd !== $td) { $r['err'] = 'Short Leave must be for a single date.'; return $r; }
        if ($ft === '' || $tt === '') { $r['err'] = 'Please enter From and To times for Short Leave.'; return $r; }
        if ($tt <= $ft) { $r['err'] = 'To Time must be after From Time.'; return $r; }
        $mins = (strtotime("$fd $tt") - strtotime("$fd $ft")) / 60;
        if ($mins > 240) { $r['err'] = 'Short Leave cannot exceed 4 hours.'; return $r; }
        $r['days'] = round($mins / 480, 3); $r['hours'] = intdiv((int)$mins, 60); $r['minutes'] = (int)$mins % 60;
        $r['from'] = "$fd $ft:00"; $r['to'] = "$td $tt:00";
    } else { $r['err'] = 'Invalid leave category.'; }
    return $r;
}

function validate_leave(mysqli $db, string $empNo, array $in): array {
    $reason = trim($in['reason'] ?? ''); $cat = $in['category'] ?? ''; $type = $in['leave_type'] ?? '';
    $year = (int)($in['year'] ?? date('Y'));
    if ($reason === '') return ['err' => 'Reason for leave is required.'];
    if ($type === '') return ['err' => 'Please select the leave type.'];
    $c = leave_calc($cat, $in['from_date'] ?? '', $in['from_time'] ?? '', $in['to_date'] ?? '', $in['to_time'] ?? '');
    if ($c['err']) return ['err' => $c['err']];
    if ((int)substr($c['from'], 0, 4) !== $year) return ['err' => "Leave dates must be within the selected registration year ($year)."];
    $ov = qa($db, "SELECT id FROM leave_applications WHERE emp_no=? AND status IN ('Pending','Approved') AND from_dt < ? AND to_dt > ? LIMIT 1", 'sss', [$empNo, $c['to'], $c['from']]);
    if ($ov) return ['err' => 'You already have a leave application for this period.'];
    if ($type !== 'No Pay Leave') {
        $bal = null;
        foreach (balances($db, $empNo, $year) as $b) if ($b['type'] === $type) $bal = $b['balance'];
        if ($bal === null) return ['err' => 'Invalid leave type.'];
        if ($c['days'] > $bal + 0.0001) return ['err' => "Insufficient leave balance. Available balance: ".n2($bal)." day(s)."];
    }
    return ['err' => '', 'calc' => $c, 'reason' => $reason, 'cat' => $cat, 'type' => $type, 'year' => $year];
}

$supRaw = trim((string)($emp['reporting_supervisor'] ?? ''));
$supRow = null; $supName = $supRaw;
if ($supRaw !== '') {
    $f = qa($db, "SELECT * FROM employees WHERE emp_no=? OR full_name=? OR office_use_name=? LIMIT 1", 'sss', [$supRaw,$supRaw,$supRaw]);
    if ($f) { $supRow = $f[0]; $supName = nm($supRow); }
}
$levelName = $supRow['job_level'] ?? ($emp['job_level'] ?? 'Supervisor');
$myNames = array_values(array_filter([$empNo, $emp['full_name'] ?? '', $emp['office_use_name'] ?? '', $emp['name_with_initials'] ?? '']));
$ph = implode(',', array_fill(0, count($myNames), '?'));
$subs = qa($db, "SELECT emp_no FROM employees WHERE reporting_supervisor IN ($ph)", str_repeat('s', count($myNames)), $myNames);
$subNos = array_column($subs, 'emp_no');

$tab = $_GET['tab'] ?? 'apply';
$err = ''; $flash = $_SESSION['lv_flash'] ?? ''; unset($_SESSION['lv_flash']);
$act = $_POST['action'] ?? '';

if ($act === 'calc') {
    header('Content-Type: application/json');
    $v = validate_leave($db, $empNo, $_POST);
    if ($v['err']) { echo json_encode(['ok'=>false,'msg'=>$v['err']]); exit; }
    echo json_encode(['ok'=>true,'days'=>n2($v['calc']['days']),'hours'=>$v['calc']['hours'],'minutes'=>$v['calc']['minutes'],'msg'=>'Leave validated. You can submit now.']); exit;
}
if ($act === 'submit') {
    $v = validate_leave($db, $empNo, $_POST);
    if ($v['err']) { $err = $v['err']; $tab = 'apply'; }
    else {
        $cov = implode(', ', array_map('trim', (array)($_POST['covering'] ?? [])));
        $now = date('Y-m-d H:i:s'); $c = $v['calc'];
        ex($db, "INSERT INTO leave_applications (emp_no,leave_year,reason,category,leave_type,covering,applied_at,from_dt,to_dt,days,hours,minutes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)",
           'sisssssssdii', [$empNo,$v['year'],$v['reason'],$v['cat'],$v['type'],$cov,$now,$c['from'],$c['to'],$c['days'],$c['hours'],$c['minutes']]);
        $_SESSION['lv_flash'] = 'Leave application submitted successfully.';
        header('Location: leave-application.php?tab=history'); exit;
    }
}
if ($act === 'cancel') {
    $id = (int)($_POST['id'] ?? 0);
    ex($db, "UPDATE leave_applications SET status='Cancelled' WHERE id=? AND emp_no=? AND status='Pending'", 'is', [$id,$empNo]);
    $_SESSION['lv_flash'] = 'Leave application cancelled.';
    header('Location: leave-application.php?tab=history'); exit;
}
if ($act === 'approve' || $act === 'reject') {
    $id = (int)($_POST['id'] ?? 0);
    $row = qa($db, "SELECT emp_no FROM leave_applications WHERE id=? AND status='Pending'", 'i', [$id]);
    if ($row && in_array($row[0]['emp_no'], $subNos, true)) {
        $new = $act === 'approve' ? 'Approved' : 'Rejected';
        ex($db, "UPDATE leave_applications SET status=?, approver_emp_no=?, decided_at=NOW() WHERE id=?", 'ssi', [$new,$empNo,$id]);
        $_SESSION['lv_flash'] = "Leave $new.";
    }
    header('Location: leave-application.php?tab=approvals'); exit;
}

$curYear = (int)date('Y');
$year = (int)($_POST['year'] ?? $curYear);
$bal = balances($db, $empNo, $year);
$lieu = qa($db, "SELECT * FROM lieu_leave_balances WHERE emp_no=? ORDER BY worked_date DESC", 's', [$empNo]);
$others = qa($db, "SELECT emp_no, COALESCE(NULLIF(full_name,''), office_use_name) AS nm FROM employees WHERE emp_no<>? ORDER BY nm", 's', [$empNo]);
$types = array_column($bal, 'type');
$old = fn($k) => e($_POST[$k] ?? '');

$hf = $_GET['hfrom'] ?? ''; $ht = $_GET['hto'] ?? '';
$filtered = isset($_GET['hf']);
$sp = !$filtered || isset($_GET['hp']); $sa = !$filtered || isset($_GET['ha']); $sr = !$filtered || isset($_GET['hr']);
$sql = "SELECT * FROM leave_applications WHERE emp_no=?"; $ty = 's'; $pa = [$empNo];
if ($hf) { $sql .= " AND DATE(from_dt)>=?"; $ty .= 's'; $pa[] = $hf; }
if ($ht) { $sql .= " AND DATE(from_dt)<=?"; $ty .= 's'; $pa[] = $ht; }
$allowed = ["'Cancelled'"]; if ($sp) $allowed[] = "'Pending'"; if ($sa) $allowed[] = "'Approved'"; if ($sr) $allowed[] = "'Rejected'";
$sql .= " AND status IN (".implode(',', $allowed).") ORDER BY from_dt DESC";
$hist = qa($db, $sql, $ty, $pa);

$af = $_GET['afrom'] ?? ''; $at = $_GET['ato'] ?? '';
$appr = [];
if ($subNos) {
    $ph2 = implode(',', array_fill(0, count($subNos), '?'));
    $sql = "SELECT l.*, COALESCE(NULLIF(e.full_name,''), e.office_use_name) AS ename FROM leave_applications l JOIN employees e ON e.emp_no=l.emp_no WHERE l.emp_no IN ($ph2) AND l.status='Pending'";
    $ty = str_repeat('s', count($subNos)); $pa = $subNos;
    if ($af) { $sql .= " AND DATE(l.from_dt)>=?"; $ty .= 's'; $pa[] = $af; }
    if ($at) { $sql .= " AND DATE(l.from_dt)<=?"; $ty .= 's'; $pa[] = $at; }
    $appr = qa($db, $sql." ORDER BY l.from_dt", $ty, $pa);
}

$dy = $_GET['dyear'] ?? ''; $df = $_GET['dfrom'] ?? ''; $dt = $_GET['dto'] ?? '';
$det = []; $searched = isset($_GET['dsearch']);
if ($searched) {
    $sql = "SELECT * FROM leave_applications WHERE emp_no=?"; $ty = 's'; $pa = [$empNo];
    if ($dy !== '') { $sql .= " AND leave_year=?"; $ty .= 'i'; $pa[] = (int)$dy; }
    if ($df) { $sql .= " AND DATE(from_dt)>=?"; $ty .= 's'; $pa[] = $df; }
    if ($dt) { $sql .= " AND DATE(from_dt)<=?"; $ty .= 's'; $pa[] = $dt; }
    $det = qa($db, $sql." ORDER BY from_dt DESC", $ty, $pa);
}
$yearsAvail = array_unique(array_merge([$curYear-1, $curYear, $curYear+1], array_column(qa($db, "SELECT DISTINCT leave_year y FROM leave_applications WHERE emp_no=?", 's', [$empNo]), 'y')));
sort($yearsAvail);

function dt_fmt($v) { return e(date('d M Y h:i A', strtotime($v))); }
function lv_dur(array $r): string { $s = n2($r['days']).' day(s)'; if ($r['hours'] || $r['minutes']) $s .= ' / '.$r['hours'].'h '.$r['minutes'].'m'; return $s; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Leave Application - Sipway Campus</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --primary: #0ea5e9;
    --primary-dark: #0284c7;
    --bg: #f1f5f9;
    --card: #ffffff;
    --text: #0f172a;
    --text-muted: #64748b;
    --border: #e2e8f0;
  }

  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
  }

  /* Navbar */
  nav {
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(16px);
    height: 68px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 40px;
    border-bottom: 1px solid rgba(14, 165, 233, 0.1);
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
  }

  nav .l, nav .r { display: flex; align-items: center; gap: 26px; }

  nav a {
    color: var(--text-muted);
    text-decoration: none;
    font-size: 14.5px;
    font-weight: 500;
    transition: color 0.25s ease;
  }

  nav a:hover { color: var(--primary); }

  .brand {
    font-weight: 800;
    font-size: 17px;
    color: var(--text) !important;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .brand span {
    background: linear-gradient(135deg, #0ea5e9, #38bdf8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  .user {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    font-weight: 600;
    background: #f1f5f9;
    padding: 5px 14px 5px 5px;
    border-radius: 50px;
  }

  .user i {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0ea5e9, #38bdf8);
    display: inline-block;
  }

  .logout-btn {
    background: linear-gradient(135deg, #ef4444, #f87171);
    color: white !important;
    padding: 8px 18px;
    border-radius: 10px;
    font-weight: 600 !important;
    font-size: 13.5px !important;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
    transition: all 0.3s ease !important;
  }

  .logout-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(239, 68, 68, 0.35);
  }

  /* Main */
  .wrap {
    max-width: 1280px;
    margin: 0 auto;
    padding: 32px 24px 60px;
  }

  /* Tabs */
  .tabs {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 24px;
  }

  .tabs button {
    border: none;
    background: #e2e8f0;
    color: var(--text-muted);
    padding: 11px 20px;
    font: 600 14.5px 'Plus Jakarta Sans', sans-serif;
    cursor: pointer;
    border-radius: 12px;
    transition: all 0.25s ease;
  }

  .tabs button.active {
    background: linear-gradient(135deg, #0ea5e9, #0284c7);
    color: white;
    box-shadow: 0 4px 14px rgba(14, 165, 233, 0.3);
  }

  .tabs button:hover:not(.active) {
    background: #cbd5e1;
  }

  /* Panel */
  .panel {
    display: none;
    background: var(--card);
    border-radius: 18px;
    padding: 28px 28px 40px;
    box-shadow: 0 8px 30px rgba(14, 165, 233, 0.08);
    border: 1px solid rgba(255,255,255,0.9);
  }

  .panel.show { display: block; }

  .panel h2 {
    font-size: 22px;
    font-weight: 800;
    margin-bottom: 24px;
    letter-spacing: -0.3px;
  }

  /* Form rows */
  .r {
    display: flex;
    align-items: center;
    margin-bottom: 16px;
    flex-wrap: wrap;
    gap: 12px;
  }

  .r > label {
    width: 280px;
    font-size: 14.5px;
    font-weight: 600;
    color: var(--text-muted);
  }

  .r select,
  .r input[type=text],
  .r input[type=date],
  .r input[type=time] {
    height: 44px;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    padding: 0 14px;
    font: 500 14.5px 'Plus Jakarta Sans', sans-serif;
    background: #f8fafc;
    color: var(--text);
    transition: border-color 0.25s ease;
  }

  .r select:focus,
  .r input:focus {
    outline: none;
    border-color: var(--primary);
  }

  .w1 { width: 420px; max-width: 100%; }
  .w2 { width: 170px; }
  .w3 { width: 140px; }

  .val { font-size: 15px; font-weight: 500; }

  /* Buttons */
  .btn {
    border: none;
    color: #fff;
    cursor: pointer;
    font: 600 14px 'Plus Jakarta Sans', sans-serif;
    padding: 0 24px;
    height: 44px;
    border-radius: 10px;
    transition: all 0.25s ease;
  }

  .blue {
    background: linear-gradient(135deg, #0ea5e9, #0284c7);
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
  }

  .blue:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(14, 165, 233, 0.4); }

  .green {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
  }

  .grey {
    background: #e2e8f0;
    color: var(--text);
  }

  .orange {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
  }

  .btn:disabled {
    opacity: 0.45;
    cursor: not-allowed;
    transform: none !important;
  }

  .sm { height: 36px; padding: 0 16px; font-size: 13px; }

  /* Trio boxes */
  .trio {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin: 28px 0 20px;
  }

  .trio label {
    display: block;
    margin-bottom: 6px;
    font-size: 13.5px;
    font-weight: 600;
    color: var(--text-muted);
  }

  .trio input {
    width: 100%;
    height: 44px;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    background: #f1f5f9;
    padding: 0 14px;
    font: 600 15px 'Plus Jakarta Sans', sans-serif;
  }

  /* Messages */
  .msg {
    padding: 14px 18px;
    border-radius: 12px;
    margin: 0 0 16px;
    font-size: 14.5px;
    font-weight: 500;
  }

  .msg.warn { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
  .msg.ok   { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
  .msg.bad  { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

  .acts { display: flex; gap: 10px; margin-top: 10px; }

  /* Chips */
  .chips {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin: -6px 0 18px 292px;
  }

  .chip {
    background: #e0f2fe;
    border: 1px solid #7dd3fc;
    border-radius: 20px;
    padding: 5px 14px;
    font-size: 13.5px;
    font-weight: 500;
  }

  .chip b {
    cursor: pointer;
    margin-left: 8px;
    color: #dc2626;
  }

  /* Details sections */
  details {
    margin-top: 28px;
  }

  summary {
    cursor: pointer;
    color: #fff;
    font-size: 15px;
    font-weight: 600;
    padding: 14px 20px;
    list-style: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #0f172a, #1e293b);
  }

  .dbody {
    border: 1px solid var(--border);
    border-top: none;
    border-radius: 0 0 12px 12px;
    padding: 16px;
    background: #fff;
  }

  /* Tables */
  .scroll { overflow-x: auto; }

  table.t {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
  }

  table.t th {
    padding: 12px 10px;
    color: #fff;
    text-align: center;
    font-weight: 600;
    font-size: 13.5px;
  }

  table.t td {
    padding: 11px 10px;
    border-bottom: 1px solid #f1f5f9;
  }

  table.t.or th { background: linear-gradient(135deg, #f59e0b, #d97706); }
  table.t.or tr:nth-child(odd) td { background: #fffbeb; }

  table.t.bl th { background: linear-gradient(135deg, #3b82f6, #2563eb); }
  table.t.bl td { background: #eff6ff; }

  table.t.gen th { background: linear-gradient(135deg, #0f172a, #1e293b); }
  table.t.gen td { background: #fff; }

  /* Filter section */
  .filt {
    background: linear-gradient(135deg, #0ea5e9, #0284c7);
    color: #fff;
    font-size: 18px;
    font-weight: 700;
    padding: 12px 18px;
    border-radius: 12px 12px 0 0;
  }

  .fbody {
    border: 1px solid var(--border);
    border-top: none;
    border-radius: 0 0 12px 12px;
    padding: 20px;
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
    align-items: flex-start;
    background: #fff;
  }

  .fbody .col label.l {
    display: block;
    margin-bottom: 6px;
    font-size: 13.5px;
    font-weight: 600;
    color: var(--text-muted);
  }

  .fbody input[type=date] {
    height: 42px;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    padding: 0 12px;
    font: 500 14.5px 'Plus Jakarta Sans', sans-serif;
    background: #f8fafc;
  }

  .chk label {
    display: flex;
    gap: 10px;
    align-items: center;
    font-size: 14.5px;
    font-weight: 500;
    margin-bottom: 12px;
  }

  .chk input { width: 18px; height: 18px; }

  .fbtn {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: #fff;
    border: none;
    width: 100px;
    height: 100px;
    font: 600 14px 'Plus Jakarta Sans', sans-serif;
    cursor: pointer;
    border-radius: 14px;
    box-shadow: 0 4px 14px rgba(34, 197, 94, 0.3);
    transition: all 0.25s ease;
  }

  .fbtn:hover { transform: translateY(-2px); }

  .legend div {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    margin-bottom: 6px;
  }

  .legend i {
    width: 48px;
    height: 18px;
    display: inline-block;
    border-radius: 4px;
  }

  .c-Approved  { background: #22c55e; }
  .c-Pending   { background: #f59e0b; }
  .c-Rejected  { background: #ef4444; }
  .c-Cancelled { background: #64748b; }

  .res { margin-top: 24px; }

  .stt {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: 6px;
  }

  /* Responsive */
  @media (max-width: 900px) {
    nav { padding: 0 20px; }
    .wrap { padding: 24px 16px 50px; }
    .r > label { width: 100%; }
    .chips { margin-left: 0; }
    .trio { grid-template-columns: 1fr; }
    .w1 { width: 100%; }
  }

  @media (max-width: 600px) {
    nav { height: 62px; padding: 0 14px; }
    .user span { display: none; }
    .tabs button { padding: 10px 14px; font-size: 13.5px; }
    .panel { padding: 20px 16px 30px; }
  }
</style>
</head>
<body>

<nav>
  <div class="l">
    <a class="brand" href="dashboard.php">&#9638; <span>Smart HRIS</span></a>
    <a href="dashboard.php">&#8630; Back</a>
    <a href="dashboard.php">Home</a>
  </div>
  <div class="r">
    <div class="user"><i></i><span>Hello, <?= e($navName) ?></span></div>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>
</nav>

<div class="wrap">
  <div class="tabs">
    <button class="tb" data-t="apply">Apply Leave</button>
    <button class="tb" data-t="history">Leave History</button>
    <button class="tb" data-t="approvals">Leave Approvals</button>
    <button class="tb" data-t="details">Leave History Details</button>
  </div>

  <!-- APPLY -->
  <div class="panel" id="p-apply">
    <h2>Leave Application</h2>
    <form method="post" id="lf">
      <input type="hidden" name="action" value="submit">

      <div class="r">
        <label>Leave Registration Year</label>
        <select name="year" id="year" class="w1">
          <?php foreach ([$curYear-1,$curYear,$curYear+1] as $y): ?>
            <option <?= $y===$year?'selected':'' ?>><?= $y ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="r">
        <label>Reason For Leave</label>
        <input type="text" name="reason" id="reason" class="w1" maxlength="255" value="<?= $old('reason') ?>">
      </div>

      <div class="r">
        <label>Leave Category</label>
        <select name="category" id="category" class="w1">
          <option value="">-select-</option>
          <?php foreach (['Full Day','Half Day','Short Leave'] as $c): ?>
            <option <?= ($_POST['category'] ?? '')===$c?'selected':'' ?>><?= $c ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="r">
        <label>Leave Type</label>
        <select name="leave_type" id="leave_type" class="w1">
          <option value=""></option>
          <?php foreach ($types as $t): ?>
            <option <?= ($_POST['leave_type'] ?? '')===$t?'selected':'' ?>><?= e($t) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="r" style="margin-top:24px">
        <label>Work Covering Person</label>
        <select id="cover" class="w1">
          <option value="">-select-</option>
          <?php foreach ($others as $o): ?>
            <option value="<?= e($o['emp_no'].' - '.$o['nm']) ?>"><?= e($o['emp_no'].' - '.$o['nm']) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="button" class="btn blue" style="height:44px;width:110px" onclick="addCover()">ADD</button>
      </div>
      <div class="chips" id="chips"></div>

      <div class="r" style="margin-top:20px">
        <label>Leave application date</label>
        <span class="val" id="nowtxt"><?= date('d M Y h:i:s A') ?></span>
      </div>

      <div class="r">
        <label>From Date &amp; Time</label>
        <input type="date" name="from_date" id="from_date" class="w2" value="<?= $old('from_date') ?>">
        <input type="time" name="from_time" id="from_time" class="w3" value="<?= $old('from_time') ?>">
      </div>

      <div class="r">
        <label>To Date &amp; Time</label>
        <input type="date" name="to_date" id="to_date" class="w2" value="<?= $old('to_date') ?>">
        <input type="time" name="to_time" id="to_time" class="w3" value="<?= $old('to_time') ?>">
        <button type="button" class="btn blue sm" onclick="validateLeave()">Validate</button>
      </div>

      <div class="trio">
        <div><label>Days</label><input type="text" id="o_days" readonly></div>
        <div><label>Hours</label><input type="text" id="o_hours" readonly></div>
        <div><label>Minutes</label><input type="text" id="o_min" readonly></div>
      </div>

      <?php if ($err): ?><div class="msg bad"><?= e($err) ?></div><?php endif; ?>
      <div class="msg ok" id="okmsg" style="display:none"></div>
      <div class="msg bad" id="badmsg" style="display:none"></div>

      <div class="acts">
        <button type="submit" class="btn green" id="submitBtn" disabled>Submit</button>
        <button type="button" class="btn grey" onclick="newForm()">New</button>
      </div>
    </form>

    <details open style="margin-top:50px">
      <summary>Available Leave Balance</summary>
      <div class="dbody scroll">
        <table class="t or">
          <tr>
            <th>Leave Year</th><th>Leave Type</th><th>Leave Amount</th>
            <th>Taken Amount</th><th>Available Amount</th><th>Balance</th><th>Pending</th>
          </tr>
          <?php foreach ($bal as $b): ?>
          <tr>
            <td><?= $b['year'] ?></td>
            <td><?= e($b['type']) ?></td>
            <td><?= n2($b['amount']) ?></td>
            <td><?= n2($b['taken']) ?></td>
            <td><?= n2($b['available']) ?></td>
            <td><?= n2($b['balance']) ?></td>
            <td><?= n2($b['pending']) ?></td>
          </tr>
          <?php endforeach; ?>
        </table>
      </div>
    </details>

    <details open>
      <summary>Lieu Leave Balance</summary>
      <div class="dbody scroll">
        <table class="t gen">
          <tr><th>Worked Date</th><th>Amount</th><th>Used</th><th>Balance</th></tr>
          <?php if (!$lieu): ?>
            <tr><td colspan="4" style="text-align:center;color:#888;padding:16px">No lieu leave balance.</td></tr>
          <?php endif; ?>
          <?php foreach ($lieu as $l): ?>
            <tr>
              <td><?= e($l['worked_date']) ?></td>
              <td><?= n2($l['amount']) ?></td>
              <td><?= n2($l['used']) ?></td>
              <td><?= n2($l['amount']-$l['used']) ?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      </div>
    </details>

    <details open>
      <summary>Reporting Hierarchy</summary>
      <div class="dbody">
        <table class="t bl">
          <tr><th style="width:35%">Level Name</th><th>Supervisor Name</th></tr>
          <?php if ($supRaw !== ''): ?>
            <tr><td style="text-align:center"><?= e($levelName) ?></td><td><?= e($supName) ?></td></tr>
          <?php else: ?>
            <tr><td colspan="2" style="text-align:center;color:#888">No supervisor assigned.</td></tr>
          <?php endif; ?>
        </table>
      </div>
    </details>
  </div>

  <!-- HISTORY -->
  <div class="panel" id="p-history">
    <h2>Leave History</h2>
    <?php if ($flash): ?><div class="msg ok"><?= e($flash) ?></div><?php endif; ?>

    <div class="filt">Filteration</div>
    <form method="get">
      <input type="hidden" name="tab" value="history">
      <input type="hidden" name="hf" value="1">
      <div class="fbody">
        <div class="col">
          <label class="l">From Date</label>
          <input type="date" name="hfrom" value="<?= e($hf) ?>"><br><br>
          <label class="l">To Date</label>
          <input type="date" name="hto" value="<?= e($ht) ?>">
        </div>
        <div class="chk">
          <label><input type="checkbox" name="hp" <?= $sp?'checked':'' ?>> Pending Leaves</label>
          <label><input type="checkbox" name="ha" <?= $sa?'checked':'' ?>> Approved Leaves</label>
          <label><input type="checkbox" name="hr" <?= $sr?'checked':'' ?>> Rejected Leaves</label>
        </div>
        <button class="fbtn" type="submit">&#128269;<br>Filter</button>
        <div class="legend">
          <div><i class="c-Approved"></i>Approved Leaves</div>
          <div><i class="c-Pending"></i>Pending Leaves</div>
          <div><i class="c-Rejected"></i>Rejected Leaves</div>
          <div><i class="c-Cancelled"></i>Cancelled Leaves</div>
        </div>
      </div>
    </form>

    <div class="res scroll">
      <table class="t gen">
        <tr>
          <th></th><th>Applied On</th><th>Leave Type</th><th>Category</th>
          <th>From</th><th>To</th><th>Duration</th><th>Reason</th><th>Covering</th><th>Status</th><th></th>
        </tr>
        <?php if (!$hist): ?>
          <tr><td colspan="11" style="text-align:center;color:#888;padding:20px">No leave records found.</td></tr>
        <?php endif; ?>
        <?php foreach ($hist as $r): ?>
        <tr>
          <td style="width:14px;padding:0"><i class="c-<?= e($r['status']) ?>" style="display:block;width:10px;height:38px;border-radius:4px"></i></td>
          <td><?= dt_fmt($r['applied_at']) ?></td>
          <td><?= e($r['leave_type']) ?></td>
          <td><?= e($r['category']) ?></td>
          <td><?= dt_fmt($r['from_dt']) ?></td>
          <td><?= dt_fmt($r['to_dt']) ?></td>
          <td><?= e(lv_dur($r)) ?></td>
          <td><?= e($r['reason']) ?></td>
          <td><?= e($r['covering']) ?></td>
          <td><?= e($r['status']) ?></td>
          <td>
            <?php if ($r['status'] === 'Pending'): ?>
              <form method="post" onsubmit="return confirm('Cancel this leave application?')">
                <input type="hidden" name="action" value="cancel">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <button class="btn grey sm">Cancel</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>

  <!-- APPROVALS -->
  <div class="panel" id="p-approvals">
    <h2>Leave Approvals</h2>
    <div class="filt">Filteration</div>
    <form method="get">
      <input type="hidden" name="tab" value="approvals">
      <div class="fbody">
        <div class="col">
          <label class="l">From Date</label>
          <input type="date" name="afrom" value="<?= e($af) ?>">
        </div>
        <div class="col" style="margin-left:40px">
          <label class="l">To Date</label>
          <input type="date" name="ato" value="<?= e($at) ?>">
        </div>
        <button class="fbtn" type="submit">&#128269;<br>Filter</button>
      </div>
    </form>

    <div class="res scroll">
      <table class="t gen">
        <tr>
          <th>Employee</th><th>Applied On</th><th>Leave Type</th><th>Category</th>
          <th>From</th><th>To</th><th>Duration</th><th>Reason</th><th>Action</th>
        </tr>
        <?php if (!$subNos): ?>
          <tr><td colspan="9" style="text-align:center;color:#888;padding:20px">You have no subordinates.</td></tr>
        <?php elseif (!$appr): ?>
          <tr><td colspan="9" style="text-align:center;color:#888;padding:20px">No pending leave approvals.</td></tr>
        <?php endif; ?>
        <?php foreach ($appr as $r): ?>
        <tr>
          <td><?= e($r['emp_no'].' - '.$r['ename']) ?></td>
          <td><?= dt_fmt($r['applied_at']) ?></td>
          <td><?= e($r['leave_type']) ?></td>
          <td><?= e($r['category']) ?></td>
          <td><?= dt_fmt($r['from_dt']) ?></td>
          <td><?= dt_fmt($r['to_dt']) ?></td>
          <td><?= e(lv_dur($r)) ?></td>
          <td><?= e($r['reason']) ?></td>
          <td style="white-space:nowrap">
            <form method="post" style="display:inline">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button class="btn green sm" name="action" value="approve">Approve</button>
              <button class="btn sm" style="background:#ef4444" name="action" value="reject" onclick="return confirm('Reject this leave?')">Reject</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>

  <!-- DETAILS -->
  <div class="panel" id="p-details">
    <h2>Employee Leave History</h2>
    <form method="get">
      <input type="hidden" name="tab" value="details">
      <input type="hidden" name="dsearch" value="1">
      <div class="r">
        <label style="width:200px">Year</label>
        <select name="dyear" class="w1">
          <option value="">-select-</option>
          <?php foreach ($yearsAvail as $y): ?>
            <option <?= (string)$y===$dy?'selected':'' ?>><?= $y ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="r">
        <label style="width:200px">From Date</label>
        <input type="date" name="dfrom" class="w2" value="<?= e($df) ?>">
      </div>
      <div class="r">
        <label style="width:200px">To Date</label>
        <input type="date" name="dto" class="w2" value="<?= e($dt) ?>">
      </div>
      <button class="btn orange" style="height:44px;width:130px">Search</button>
    </form>

    <?php if ($searched): ?>
    <div class="res scroll">
      <table class="t gen">
        <tr>
          <th>Year</th><th>Leave Type</th><th>Category</th><th>From</th>
          <th>To</th><th>Duration</th><th>Reason</th><th>Status</th>
        </tr>
        <?php if (!$det): ?>
          <tr><td colspan="8" style="text-align:center;color:#888;padding:20px">No records found.</td></tr>
        <?php endif; ?>
        <?php foreach ($det as $r): ?>
        <tr>
          <td><?= (int)$r['leave_year'] ?></td>
          <td><?= e($r['leave_type']) ?></td>
          <td><?= e($r['category']) ?></td>
          <td><?= dt_fmt($r['from_dt']) ?></td>
          <td><?= dt_fmt($r['to_dt']) ?></td>
          <td><?= e(lv_dur($r)) ?></td>
          <td><?= e($r['reason']) ?></td>
          <td><i class="stt c-<?= e($r['status']) ?>"></i><?= e($r['status']) ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
const $ = id => document.getElementById(id);

function showTab(t) {
  document.querySelectorAll('.panel').forEach(p => p.classList.toggle('show', p.id === 'p-' + t));
  document.querySelectorAll('.tb').forEach(b => b.classList.toggle('active', b.dataset.t === t));
}

document.querySelectorAll('.tb').forEach(b => b.addEventListener('click', () => showTab(b.dataset.t)));
showTab(<?= json_encode(in_array($tab,['apply','history','approvals','details']) ? $tab : 'apply') ?>);

setInterval(() => {
  $('nowtxt').textContent = new Date().toLocaleString('en-GB', {
    day: '2-digit', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
  }).replace(',', '');
}, 1000);

function addCover() {
  const v = $('cover').value;
  if (!v) return;
  if ([...document.querySelectorAll('input[name="covering[]"]')].some(i => i.value === v)) return;
  const chip = document.createElement('span');
  chip.className = 'chip';
  chip.innerHTML = '<input type="hidden" name="covering[]"><span></span><b title="Remove">&times;</b>';
  chip.querySelector('input').value = v;
  chip.querySelector('span').textContent = v;
  chip.querySelector('b').onclick = () => chip.remove();
  $('chips').appendChild(chip);
  $('cover').value = '';
}

const submitBtn = $('submitBtn');
function resetValidation() {
  submitBtn.disabled = true;
  $('okmsg').style.display = 'none';
}

['year','reason','category','leave_type','from_date','from_time','to_date','to_time'].forEach(id => {
  $(id).addEventListener('input', resetValidation);
  $(id).addEventListener('change', resetValidation);
});

$('category').addEventListener('change', () => {
  const c = $('category').value;
  $('from_time').disabled = c === 'Full Day';
  $('to_time').disabled = c === 'Full Day' || c === 'Half Day';
  if (c === 'Full Day') { $('from_time').value = ''; $('to_time').value = ''; }
  if (c === 'Half Day') { $('to_time').value = ''; }
});

async function validateLeave() {
  $('badmsg').style.display = 'none';
  $('okmsg').style.display = 'none';
  const fd = new FormData($('lf'));
  fd.set('action', 'calc');
  const r = await fetch('leave-application.php', { method: 'POST', body: fd });
  const j = await r.json();
  if (!j.ok) {
    $('badmsg').textContent = j.msg;
    $('badmsg').style.display = 'block';
    $('o_days').value = $('o_hours').value = $('o_min').value = '';
    return;
  }
  $('o_days').value = j.days;
  $('o_hours').value = j.hours;
  $('o_min').value = j.minutes;
  $('okmsg').textContent = j.msg;
  $('okmsg').style.display = 'block';
  submitBtn.disabled = false;
}

function newForm() {
  $('lf').reset();
  $('chips').innerHTML = '';
  $('o_days').value = $('o_hours').value = $('o_min').value = '';
  $('from_time').disabled = false;
  $('to_time').disabled = false;
  $('badmsg').style.display = 'none';
  resetValidation();
}

$('lf').addEventListener('submit', e => { if (submitBtn.disabled) e.preventDefault(); });
$('category').dispatchEvent(new Event('change'));
</script>
</body>
</html>