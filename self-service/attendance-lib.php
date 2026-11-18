<?php
// Attendance pages walata common functions
require_once __DIR__ . '/config.php';

function nav_bar(string $navName): void { ?>
<nav>
  <div><a href="dashboard.php"><b>&#9638; Smart HRIS</b></a><a href="dashboard.php">&#8630;</a><a href="dashboard.php">Home</a></div>
  <div style="font-size:15px">Hello, <?= e($navName) ?> &nbsp; <a href="logout.php" style="margin:0">Logout</a></div>
</nav>
<?php }

function att_ensure_table(mysqli $db): void {
    $db->query("CREATE TABLE IF NOT EXISTS attendance_records (
      id INT AUTO_INCREMENT PRIMARY KEY,
      emp_no VARCHAR(50) NOT NULL,
      att_date DATE NOT NULL,
      shift_name VARCHAR(100) DEFAULT 'General Shift',
      remark VARCHAR(100) DEFAULT NULL,
      shift_start TIME DEFAULT '08:00:00',
      shift_end TIME DEFAULT '17:00:00',
      in_time TIME NULL, out_time TIME NULL,
      late TIME DEFAULT '00:00:00',
      break_start TIME NULL, break_end TIME NULL,
      break_time TIME DEFAULT '00:00:00',
      work_hours TIME DEFAULT '00:00:00',
      ot_rate DECIMAL(4,1) DEFAULT 1.5,
      ot_count TIME DEFAULT '00:00:00',
      days_pay DECIMAL(4,1) DEFAULT 0,
      leave_type VARCHAR(50) DEFAULT NULL,
      is_off TINYINT(1) DEFAULT 0,
      is_holiday TINYINT(1) DEFAULT 0,
      is_24h TINYINT(1) DEFAULT 0,
      UNIQUE KEY uq_emp_date (emp_no, att_date)
    ) CHARACTER SET utf8mb4");
}

// Last 12 months session list: code => [label, from, to]
function att_sessions(): array {
    $out = [];
    for ($i = 0; $i < 12; $i++) {
        $t = strtotime("first day of -$i month");
        $out[date('Ym', $t)] = [date('Ym', $t).' - '.date('F Y', $t), date('Y-m-01', $t), date('Y-m-t', $t)];
    }
    return $out;
}

function att_fetch(mysqli $db, string $empNo, string $from, string $to): array {
    $st = $db->prepare('SELECT * FROM attendance_records WHERE emp_no=? AND att_date BETWEEN ? AND ? ORDER BY att_date');
    $st->bind_param('sss', $empNo, $from, $to);
    $st->execute();
    $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $st->close();
    return $rows;
}

function att_summary(array $rows, string $from, string $to): array {
    $cal = (int)((strtotime($to) - strtotime($from)) / 86400) + 1;
    $s = ['cal'=>$cal,'hol'=>0,'off'=>0,'present'=>0,'leave'=>0,'late'=>0,'h24'=>0];
    foreach ($rows as $r) {
        if ($r['is_holiday']) $s['hol']++;
        if ($r['is_off'])     $s['off']++;
        if ($r['in_time'])    $s['present']++;
        if (!empty($r['leave_type'])) $s['leave']++;
        if ($r['late'] && $r['late'] > '00:00:00') $s['late']++;
        if ($r['is_24h'])     $s['h24']++;
    }
    $s['work'] = $s['cal'] - $s['off'] - $s['hol'];
    return $s;
}

function tm($v, $dash = ''): string {
    if ($v === null || $v === '') return $dash;
    return e($v);
}

function att_render(array $rows, string $from, string $to): void {
    $s = att_summary($rows, $from, $to);
    $n = fn($x) => number_format($x, 1); ?>
  <h3 class="sum-title">Atendance Summery</h3>
  <table class="sum">
    <tr><td>Calander Days</td><td class="v"><?= $n($s['cal']) ?></td><td class="y">Total No of Present Days</td><td class="v y2"><?= $n($s['present']) ?></td><td>Total No of 24 Hour Shifts</td><td class="v"><?= $n($s['h24']) ?></td></tr>
    <tr><td>Total Holidays</td><td class="v"><?= $n($s['hol']) ?></td><td>Total Leaves</td><td class="v"><?= $n($s['leave']) ?></td><td>Total No of Late Days</td><td class="v"><?= $n($s['late']) ?></td></tr>
    <tr><td>Total Working Days</td><td class="v"><?= $n($s['work']) ?></td><td colspan="4"></td></tr>
  </table>
  <div class="scroll">
  <table class="att">
    <tr><th>Date</th><th>Shift Name</th><th>Date Name</th><th>Remark</th><th>Shift Start</th><th>Shift End</th><th>Employee IN</th><th>Employee OUT</th><th>Late</th><th>Break Start</th><th>Break End</th><th>Break Time</th><th>Work Hours</th><th>OT Rate</th><th>OT Count (Days:hh:mm:ss)</th><th>Days Pay</th><th>Leave Type</th></tr>
    <?php if (!$rows): ?><tr><td colspan="17" style="text-align:center;padding:25px;color:#888">No attendance records found for this period.</td></tr><?php endif; ?>
    <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= e($r['att_date']) ?></td><td><?= e($r['shift_name']) ?></td><td><?= e(date('l', strtotime($r['att_date']))) ?></td><td><?= e($r['remark']) ?></td>
      <td class="g"><?= tm($r['shift_start']) ?></td><td class="g"><?= tm($r['shift_end']) ?></td>
      <td class="b"><?= tm($r['in_time']) ?></td><td class="b"><?= tm($r['out_time']) ?></td><td class="b"><?= tm($r['late']) ?></td>
      <td class="g"><?= tm($r['break_start'], '--:--') ?></td><td class="g"><?= tm($r['break_end'], '--:--') ?></td>
      <td class="g"><?= tm($r['break_time']) ?></td><td class="g"><?= tm($r['work_hours']) ?></td>
      <td class="g"><?= e(number_format((float)$r['ot_rate'],1)) ?></td><td class="g"><?= tm($r['ot_count']) ?></td>
      <td class="g"><?= e((float)$r['days_pay'] + 0) ?></td><td><?= e($r['leave_type']) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  </div>
<?php }

function att_css(): void { ?>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Poppins',sans-serif;background:#fff;color:#222;font-size:15px}
nav{background:#1ba1e2;color:#fff;height:58px;display:flex;align-items:center;justify-content:space-between;padding:0 clamp(15px,12vw,240px)}
nav a{color:#fff;text-decoration:none;font-size:15px;margin-right:24px}
.wrap{max-width:1300px;margin:0 auto;padding:12px 20px 50px}
h1{font-weight:400;font-size:26px;padding-bottom:8px;border-bottom:1px solid #ddd;margin-bottom:22px}
h1 small{font-size:14px;color:#888}
.frm{display:grid;grid-template-columns:170px 330px;gap:18px 0;align-items:center;margin-bottom:12px}
.frm select,.frm input{height:38px;border:1px solid #ccc;border-radius:3px;padding:4px 10px;font:16px 'Poppins',sans-serif;width:100%}
.btn{background:#3b8fe0;color:#fff;border:0;padding:0 40px;height:52px;font:500 18px 'Poppins',sans-serif;cursor:pointer;box-shadow:0 2px 6px rgba(0,0,0,.3);margin-top:8px}
.btn:hover{background:#2c7bd0}
.sum-title{margin:40px 0 8px;font-size:19px;font-weight:600}
table.sum{width:100%;border-collapse:collapse;border:2px solid #000;margin-bottom:6px}
table.sum td{border:2px solid #000;padding:8px 8px;font-size:16px}
table.sum td.v{width:70px;font-weight:600;font-size:14px}
table.sum td.y,table.sum td.y2{background:#ffff00}
.scroll{overflow-x:auto}
table.att{width:100%;border-collapse:collapse;font-size:14px;text-align:center}
table.att th{background:#1ba1e2;color:#fff;padding:9px 6px;font-weight:600;border:1px solid #fff}
table.att td{padding:12px 6px;border:1px solid #fff;background:#fff}
table.att tr:nth-child(odd) td{background:#e8e8e8}
table.att tr:nth-child(even) td{background:#fff}
table.att td:nth-child(-n+4){text-align:left;font-weight:500}
table.att td.g{color:#666}table.att td.b{font-weight:600}
.cards{display:flex;gap:18px;flex-wrap:wrap;margin-top:35px}
.mcard{display:flex;align-items:center;gap:14px;width:437px;max-width:100%;height:125px;padding:0 8px;background:#fff;box-shadow:0 1px 6px rgba(0,0,0,.3);text-decoration:none;color:#000080;font-size:19px;transition:.2s}
.mcard:hover{transform:translateY(-4px);box-shadow:0 8px 18px rgba(27,161,226,.35)}
.mcard i{width:92px;height:92px;border-radius:50%;background:#6fa8c4;display:flex;align-items:center;justify-content:center;flex:none}
.note{background:#fff7d6;border:1px solid #f0d878;padding:10px 14px;border-radius:4px;margin:10px 0;font-size:14px}
</style>
<?php }