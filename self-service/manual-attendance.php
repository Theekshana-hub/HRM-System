<?php
require 'config.php';
require_login();
$emp   = $_SESSION['emp'];
$empNo = $emp[COL_EMP_NO];
$name  = $emp['name_with_initials'] ?? $emp['full_name'] ?? $empNo;
$db    = getDB();

$db->query("CREATE TABLE IF NOT EXISTS manual_attendance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  emp_no VARCHAR(50) NOT NULL,
  remarks VARCHAR(255) NOT NULL,
  check_in_date DATE NULL, check_in_time TIME NULL,
  check_out_date DATE NULL, check_out_time TIME NULL,
  break_in_date DATE NULL, break_in_time TIME NULL,
  break_out_date DATE NULL, break_out_time TIME NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'Pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (emp_no)
) CHARACTER SET utf8mb4");

$msg = $_SESSION['flash_ok'] ?? ''; unset($_SESSION['flash_ok']);
$err = '';
$tab = $_GET['tab'] ?? 'apply';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $g = fn($k) => trim($_POST[$k] ?? '');
    $remarks = $g('remarks');
    $pairs = [
        'check_in'  => [$g('check_in_date'),  $g('check_in_time')],
        'check_out' => [$g('check_out_date'), $g('check_out_time')],
        'break_in'  => [$g('break_in_date'),  $g('break_in_time')],
        'break_out' => [$g('break_out_date'), $g('break_out_time')],
    ];
    $any = false;
    foreach ($pairs as $k => [$d, $t]) {
        if ($d === '' && $t === '') continue;
        if ($d === '' || $t === '') { $err = 'Please enter both date and time for '.str_replace('_',' ',$k).'.'; break; }
        $any = true;
    }
    if (!$err && $remarks === '') $err = 'Remarks is required.';
    if (!$err && !$any) $err = 'Please select Check In / Check Out / Break and fill the details.';
    if (!$err && $pairs['check_in'][0] && $pairs['check_out'][0] &&
        strtotime(implode(' ', $pairs['check_out'])) < strtotime(implode(' ', $pairs['check_in'])))
        $err = 'Check Out must be after Check In.';
    if (!$err && $pairs['break_in'][0] && $pairs['break_out'][0] &&
        strtotime(implode(' ', $pairs['break_out'])) < strtotime(implode(' ', $pairs['break_in'])))
        $err = 'Break Out must be after Break In.';

    if (!$err) {
        $n = fn($v) => $v === '' ? null : $v;
        $st = $db->prepare("INSERT INTO manual_attendance
          (emp_no,remarks,check_in_date,check_in_time,check_out_date,check_out_time,break_in_date,break_in_time,break_out_date,break_out_time)
          VALUES (?,?,?,?,?,?,?,?,?,?)");
        $a = [$empNo,$remarks,$n($pairs['check_in'][0]),$n($pairs['check_in'][1]),$n($pairs['check_out'][0]),$n($pairs['check_out'][1]),
              $n($pairs['break_in'][0]),$n($pairs['break_in'][1]),$n($pairs['break_out'][0]),$n($pairs['break_out'][1])];
        $st->bind_param('ssssssssss', ...$a);
        $st->execute(); $st->close();
        $_SESSION['flash_ok'] = 'Manual attendance request submitted successfully.';
        header('Location: manual-attendance.php?tab=history'); exit;
    }
    $tab = 'apply';
}

$hist = [];
$st = $db->prepare("SELECT * FROM manual_attendance WHERE emp_no=? ORDER BY id DESC");
$st->bind_param('s', $empNo); $st->execute();
$res = $st->get_result();
while ($r = $res->fetch_assoc()) $hist[] = $r;
$st->close();

$old = fn($k) => e($_POST[$k] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Apply Manual Attendance - Sipway Campus</title>
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
    max-width: 1100px;
    margin: 0 auto;
    padding: 32px 24px 60px;
  }

  /* Tabs */
  .tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 24px;
    flex-wrap: wrap;
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
    margin-bottom: 22px;
    letter-spacing: -0.3px;
  }

  .panel h3 {
    font-size: 16px;
    font-weight: 700;
    margin: 28px 0 14px;
    color: var(--text);
  }

  /* Alerts */
  .alert {
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 20px;
    font-size: 14.5px;
    font-weight: 500;
  }

  .alert.err {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
  }

  .alert.ok {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #6ee7b7;
  }

  /* Form */
  .row {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 12px;
  }

  .row label.main {
    width: 160px;
    font-size: 14.5px;
    font-weight: 600;
    color: var(--text-muted);
  }

  .remarks {
    width: 100%;
    max-width: 520px;
    height: 44px;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    padding: 0 14px;
    font: 500 14.5px 'Plus Jakarta Sans', sans-serif;
    background: #f8fafc;
    transition: border-color 0.25s ease;
  }

  .remarks:focus {
    outline: none;
    border-color: var(--primary);
  }

  /* Mode buttons */
  .btn-row {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 16px;
  }

  .mode {
    background: #e0f2fe;
    color: var(--primary-dark);
    border: 1.5px solid #bae6fd;
    padding: 10px 22px;
    font: 600 14px 'Plus Jakarta Sans', sans-serif;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.25s ease;
  }

  .mode:hover {
    background: #bae6fd;
  }

  .mode.on {
    background: linear-gradient(135deg, #0ea5e9, #0284c7);
    color: white;
    border-color: transparent;
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
  }

  /* Fields */
  .fld {
    display: grid;
    grid-template-columns: 150px 180px 150px 180px;
    gap: 12px 16px;
    align-items: center;
    margin-bottom: 8px;
  }

  .fld label {
    font-size: 13.5px;
    font-weight: 600;
    color: var(--text-muted);
  }

  .fld input {
    height: 42px;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    background: #f8fafc;
    padding: 0 12px;
    font: 500 14px 'Plus Jakarta Sans', sans-serif;
    transition: border-color 0.25s ease;
  }

  .fld input:focus {
    outline: none;
    border-color: var(--primary);
  }

  .fld input:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background: #f1f5f9;
  }

  /* Actions */
  .actions {
    margin-top: 32px;
    display: flex;
    gap: 12px;
  }

  .submit {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: white;
    border: none;
    height: 48px;
    padding: 0 32px;
    font: 600 15px 'Plus Jakarta Sans', sans-serif;
    border-radius: 12px;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(34, 197, 94, 0.3);
    transition: all 0.25s ease;
  }

  .submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(34, 197, 94, 0.4);
  }

  .new {
    background: #e2e8f0;
    color: var(--text);
    border: none;
    height: 48px;
    padding: 0 28px;
    font: 600 15px 'Plus Jakarta Sans', sans-serif;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.25s ease;
  }

  .new:hover {
    background: #cbd5e1;
  }

  /* Details */
  details {
    margin-top: 36px;
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

  details .body {
    border: 1px solid var(--border);
    border-top: none;
    border-radius: 0 0 12px 12px;
    padding: 16px 20px;
    background: #fff;
    font-size: 14.5px;
    line-height: 1.6;
  }

  details .body p {
    margin-bottom: 6px;
  }

  /* Table */
  .scroll {
    overflow-x: auto;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
  }

  th {
    background: linear-gradient(135deg, #0f172a, #1e293b);
    color: #fff;
    text-align: left;
    padding: 13px 12px;
    font-weight: 600;
    font-size: 13.5px;
  }

  td {
    padding: 12px;
    border-bottom: 1px solid #f1f5f9;
  }

  tr:hover td {
    background: #f0f9ff;
  }

  .badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12.5px;
    font-weight: 600;
  }

  .badge.Pending {
    background: #fef3c7;
    color: #d97706;
  }

  .badge.Approved {
    background: #d1fae5;
    color: #059669;
  }

  .badge.Rejected {
    background: #fee2e2;
    color: #dc2626;
  }

  /* Responsive */
  @media (max-width: 900px) {
    nav { padding: 0 20px; }
    .wrap { padding: 24px 16px 50px; }
    .fld {
      grid-template-columns: 1fr 1fr;
    }
  }

  @media (max-width: 600px) {
    nav { height: 62px; padding: 0 14px; }
    .user span { display: none; }
    .fld {
      grid-template-columns: 1fr;
    }
    .row {
      flex-direction: column;
      align-items: flex-start;
    }
    .row label.main {
      width: 100%;
    }
    .remarks {
      max-width: 100%;
    }
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
    <div class="user">
      <i></i>
      <span>Hello, <?= e($name) ?></span>
    </div>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>
</nav>

<div class="wrap">
  <div class="tabs">
    <button type="button" id="t-apply" onclick="showTab('apply')">Apply Manual Attendance</button>
    <button type="button" id="t-history" onclick="showTab('history')">Manual Attendance History</button>
  </div>

  <!-- APPLY -->
  <div class="panel" id="p-apply">
    <h2>Apply Manual Attendance</h2>
    <?php if ($err): ?><div class="alert err"><?= e($err) ?></div><?php endif; ?>

    <form method="post" id="mform">
      <div class="row">
        <label class="main">Remarks</label>
        <input class="remarks" type="text" name="remarks" maxlength="255" value="<?= $old('remarks') ?>" placeholder="Enter remarks...">
      </div>

      <h3>Check In / Out</h3>
      <div class="btn-row">
        <button type="button" class="mode" data-g="c" data-m="in">Check In</button>
        <button type="button" class="mode" data-g="c" data-m="out">Check Out</button>
        <button type="button" class="mode" data-g="c" data-m="both">Both (In & Out)</button>
        <button type="button" class="mode" id="incBreak">Include Break</button>
      </div>

      <div class="fld">
        <label>Check In Date</label>
        <input type="date" name="check_in_date" id="check_in_date" value="<?= $old('check_in_date') ?>" disabled>
        <label>Check In Time</label>
        <input type="time" name="check_in_time" id="check_in_time" value="<?= $old('check_in_time') ?>" disabled>

        <label>Check Out Date</label>
        <input type="date" name="check_out_date" id="check_out_date" value="<?= $old('check_out_date') ?>" disabled>
        <label>Check Out Time</label>
        <input type="time" name="check_out_time" id="check_out_time" value="<?= $old('check_out_time') ?>" disabled>
      </div>

      <h3>Break In / Out</h3>
      <div class="btn-row">
        <button type="button" class="mode" data-g="b" data-m="in">Break In</button>
        <button type="button" class="mode" data-g="b" data-m="out">Break Out</button>
        <button type="button" class="mode" data-g="b" data-m="both">Both (In & Out)</button>
      </div>

      <div class="fld">
        <label>Break In Date</label>
        <input type="date" name="break_in_date" id="break_in_date" value="<?= $old('break_in_date') ?>" disabled>
        <label>Break In Time</label>
        <input type="time" name="break_in_time" id="break_in_time" value="<?= $old('break_in_time') ?>" disabled>

        <label>Break Out Date</label>
        <input type="date" name="break_out_date" id="break_out_date" value="<?= $old('break_out_date') ?>" disabled>
        <label>Break Out Time</label>
        <input type="time" name="break_out_time" id="break_out_time" value="<?= $old('break_out_time') ?>" disabled>
      </div>

      <div class="actions">
        <button class="submit" type="submit">Submit</button>
        <button class="new" type="button" onclick="resetForm()">New</button>
      </div>
    </form>

    <details>
      <summary>Reporting Hierarchy</summary>
      <div class="body">
        <p><b>Employee:</b> <?= e($name) ?> (<?= e($empNo) ?>)</p>
        <p>Approval request eka oyage reporting supervisor ta yanawa.</p>
      </div>
    </details>
  </div>

  <!-- HISTORY -->
  <div class="panel" id="p-history">
    <h2>Manual Attendance History</h2>
    <?php if ($msg): ?><div class="alert ok"><?= e($msg) ?></div><?php endif; ?>

    <div class="scroll">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Remarks</th>
            <th>Check In</th>
            <th>Check Out</th>
            <th>Break In</th>
            <th>Break Out</th>
            <th>Status</th>
            <th>Applied On</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$hist): ?>
            <tr>
              <td colspan="8" style="text-align:center;color:#888;padding:24px">No records found.</td>
            </tr>
          <?php endif; ?>
          <?php foreach ($hist as $i => $r):
            $f = fn($d, $t) => $d ? e($d . ' ' . substr($t, 0, 5)) : '—';
          ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><?= e($r['remarks']) ?></td>
            <td><?= $f($r['check_in_date'], $r['check_in_time']) ?></td>
            <td><?= $f($r['check_out_date'], $r['check_out_time']) ?></td>
            <td><?= $f($r['break_in_date'], $r['break_in_time']) ?></td>
            <td><?= $f($r['break_out_date'], $r['break_out_time']) ?></td>
            <td><span class="badge <?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
            <td><?= e($r['created_at']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
const ids = {
  c: { in: ['check_in_date', 'check_in_time'], out: ['check_out_date', 'check_out_time'] },
  b: { in: ['break_in_date', 'break_in_time'], out: ['break_out_date', 'break_out_time'] }
};
const $ = id => document.getElementById(id);

function showTab(t) {
  ['apply', 'history'].forEach(x => {
    $('p-' + x).classList.toggle('show', x === t);
    $('t-' + x).classList.toggle('active', x === t);
  });
}

function setFields(g, which, on) {
  ids[g][which].forEach(id => {
    const el = $(id);
    el.disabled = !on;
    if (!on) el.value = '';
  });
}

function setMode(g, m) {
  setFields(g, 'in',  m === 'in'  || m === 'both');
  setFields(g, 'out', m === 'out' || m === 'both');
  document.querySelectorAll('.mode[data-g="' + g + '"]').forEach(b => {
    b.classList.toggle('on', b.dataset.m === m);
  });
}

document.querySelectorAll('.mode[data-g]').forEach(b => {
  b.addEventListener('click', () => setMode(b.dataset.g, b.dataset.m));
});

$('incBreak').addEventListener('click', () => {
  setMode('c', 'both');
  setMode('b', 'both');
  $('incBreak').classList.add('on');
});

function resetForm() {
  $('mform').reset();
  document.querySelectorAll('.mode').forEach(b => b.classList.remove('on'));
  ['c', 'b'].forEach(g => {
    setFields(g, 'in', false);
    setFields(g, 'out', false);
  });
  document.querySelector('[name=remarks]').value = '';
}

// Keep fields enabled if they already have values (after validation error)
Object.values(ids).forEach(g => Object.values(g).forEach(arr => {
  if (arr.some(id => $(id).value)) {
    arr.forEach(id => $(id).disabled = false);
  }
}));

showTab(<?= json_encode($tab === 'history' ? 'history' : 'apply') ?>);
</script>
</body>
</html>