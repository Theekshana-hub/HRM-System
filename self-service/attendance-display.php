<?php
require 'attendance-lib.php';
require_login();

$emp     = $_SESSION['emp'];
$empNo   = $emp[COL_EMP_NO];
$navName = $emp['name_with_initials'] ?? $emp['full_name'] ?? $empNo;
$db      = getDB();

$sessions = att_sessions();
$sel  = $_GET['session'] ?? array_key_first($sessions);
if (!isset($sessions[$sel])) $sel = array_key_first($sessions);

$from = $_GET['from'] ?? $sessions[$sel][1];
$to   = $_GET['to']   ?? $sessions[$sel][2];

if (!strtotime($from) || !strtotime($to) || $from > $to) {
    $from = $sessions[$sel][1];
    $to   = $sessions[$sel][2];
}

function att_fetch_manual(mysqli $db, string $empNo, string $from, string $to): array {
    $sql = "SELECT 
                id,
                emp_no,
                remarks,
                check_in_date,
                check_in_time,
                check_out_date,
                check_out_time,
                break_in_date,
                break_in_time,
                break_out_date,
                break_out_time,
                status,
                created_at
            FROM manual_attendance
            WHERE emp_no = ?
              AND (
                    (check_in_date BETWEEN ? AND ?)
                 OR (check_out_date BETWEEN ? AND ?)
                 OR (break_in_date BETWEEN ? AND ?)
                 OR (break_out_date BETWEEN ? AND ?)
              )
            ORDER BY COALESCE(check_in_date, check_out_date, break_in_date, break_out_date) ASC, 
                     COALESCE(check_in_time, check_out_time) ASC";

    $stmt = $db->prepare($sql);
    $stmt->bind_param('sssssssss', $empNo, $from, $to, $from, $to, $from, $to, $from, $to);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

$rows = att_fetch_manual($db, $empNo, $from, $to);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Attendance Display (Manual) - Sipway Campus</title>
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

  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
  }

  /* ========== MODERN NAVBAR ========== */
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

  nav .l, nav .r {
    display: flex;
    align-items: center;
    gap: 26px;
  }

  nav a {
    color: var(--text-muted);
    text-decoration: none;
    font-size: 14.5px;
    font-weight: 500;
    transition: color 0.25s ease;
  }

  nav a:hover {
    color: var(--primary);
  }

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

  /* ========== MAIN CONTENT ========== */
  .wrap {
    max-width: 1280px;
    margin: 0 auto;
    padding: 32px 24px 60px;
  }

  .page-header {
    margin-bottom: 28px;
  }

  .page-header h1 {
    font-size: 28px;
    font-weight: 800;
    letter-spacing: -0.5px;
    color: var(--text);
    margin-bottom: 4px;
  }

  .page-header h1 small {
    font-size: 14px;
    font-weight: 500;
    color: var(--text-muted);
    margin-left: 8px;
  }

  .page-header p {
    color: var(--text-muted);
    font-size: 14.5px;
  }

  /* Filter Card */
  .filter-card {
    background: var(--card);
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 8px 25px rgba(14, 165, 233, 0.08);
    margin-bottom: 28px;
    border: 1px solid rgba(255,255,255,0.9);
  }

  .frm {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 18px;
    align-items: end;
  }

  .frm label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-muted);
    margin-bottom: 6px;
  }

  .frm select,
  .frm input[type="date"] {
    width: 100%;
    height: 44px;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    padding: 0 14px;
    font: 500 14.5px 'Plus Jakarta Sans', sans-serif;
    background: #f8fafc;
    color: var(--text);
    transition: border-color 0.25s ease;
  }

  .frm select:focus,
  .frm input[type="date"]:focus {
    outline: none;
    border-color: var(--primary);
  }

  .btn {
    height: 44px;
    padding: 0 28px;
    background: linear-gradient(135deg, #0ea5e9, #0284c7);
    color: white;
    border: none;
    border-radius: 10px;
    font: 600 14.5px 'Plus Jakarta Sans', sans-serif;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(14, 165, 233, 0.3);
    transition: all 0.3s ease;
    white-space: nowrap;
  }

  .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(14, 165, 233, 0.4);
  }

  .result-info {
    margin-bottom: 16px;
    font-size: 14.5px;
    color: var(--text-muted);
  }

  .result-info strong {
    color: var(--text);
  }

  /* Table */
  .table-card {
    background: var(--card);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(14, 165, 233, 0.08);
    border: 1px solid rgba(255,255,255,0.9);
  }

  .att-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
  }

  .att-table th {
    background: linear-gradient(135deg, #0f172a, #1e293b);
    color: #fff;
    font-weight: 600;
    padding: 14px 12px;
    text-align: center;
    font-size: 13.5px;
  }

  .att-table td {
    padding: 13px 12px;
    text-align: center;
    border-bottom: 1px solid #f1f5f9;
  }

  .att-table tbody tr:hover {
    background: #f0f9ff;
  }

  .att-table tbody tr:last-child td {
    border-bottom: none;
  }

  .time-cell {
    white-space: nowrap;
    font-variant-numeric: tabular-nums;
  }

  .remarks {
    max-width: 200px;
    text-align: left;
    font-size: 13.5px;
  }

  .status-pending {
    display: inline-block;
    background: #fef3c7;
    color: #d97706;
    font-weight: 600;
    font-size: 12.5px;
    padding: 4px 12px;
    border-radius: 20px;
  }

  .status-approved {
    display: inline-block;
    background: #d1fae5;
    color: #059669;
    font-weight: 600;
    font-size: 12.5px;
    padding: 4px 12px;
    border-radius: 20px;
  }

  .status-rejected {
    display: inline-block;
    background: #fee2e2;
    color: #dc2626;
    font-weight: 600;
    font-size: 12.5px;
    padding: 4px 12px;
    border-radius: 20px;
  }

  .empty-note {
    background: #fff7ed;
    border: 1px solid #fdba74;
    padding: 28px 24px;
    border-radius: 16px;
    text-align: center;
    color: #9a3412;
    font-size: 15px;
    line-height: 1.6;
  }

  .empty-note small {
    display: block;
    margin-top: 10px;
    color: #c2410c;
    font-size: 13.5px;
  }

  /* Responsive */
  @media (max-width: 900px) {
    nav {
      padding: 0 20px;
    }

    .wrap {
      padding: 24px 16px 50px;
    }

    .page-header h1 {
      font-size: 24px;
    }

    .att-table {
      font-size: 13px;
    }
  }

  @media (max-width: 700px) {
    nav {
      height: 62px;
      padding: 0 14px;
    }

    nav .l, nav .r {
      gap: 14px;
    }

    .brand {
      font-size: 15px;
    }

    .user span {
      display: none;
    }

    .frm {
      grid-template-columns: 1fr;
    }

    .table-card {
      overflow-x: auto;
    }

    .att-table {
      min-width: 720px;
    }
  }
</style>
</head>
<body>

<!-- Modern Navbar -->
<nav>
  <div class="l">
    <a class="brand" href="dashboard.php">
      &#9638; <span>Smart HRIS</span>
    </a>
    <a href="dashboard.php">&#8630; Back</a>
    <a href="dashboard.php">Home</a>
  </div>
  <div class="r">
    <div class="user">
      <i></i>
      <span>Hello, <?= e($navName) ?></span>
    </div>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>
</nav>

<div class="wrap">
  <div class="page-header">
    <h1>Attendance Display <small>Manual Attendance</small></h1>
    <p>View and filter your manual attendance records</p>
  </div>

  <div class="filter-card">
    <form method="get" id="f">
      <div class="frm">
        <div>
          <label>Session</label>
          <select name="session" id="session">
            <?php foreach ($sessions as $code => [$label, $f, $t]): ?>
              <option value="<?= e($code) ?>"
                      data-from="<?= $f ?>"
                      data-to="<?= $t ?>"
                      <?= $code === $sel ? 'selected' : '' ?>>
                <?= e($label) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label>From Date</label>
          <input type="date" name="from" id="from" value="<?= e($from) ?>">
        </div>

        <div>
          <label>To Date</label>
          <input type="date" name="to" id="to" value="<?= e($to) ?>">
        </div>

        <div>
          <label>&nbsp;</label>
          <button class="btn" type="submit">Search Records</button>
        </div>
      </div>
    </form>
  </div>

  <?php if (empty($rows)): ?>
    <div class="empty-note">
      මෙම period එකට manual attendance data නැහැ.<br>
      <small>Employee: <strong><?= e($empNo) ?></strong> &nbsp;|&nbsp; Date range: <?= e($from) ?> → <?= e($to) ?></small>
    </div>
  <?php else: ?>

    <div class="result-info">
      Showing <strong><?= count($rows) ?></strong> record(s) for <strong><?= e($empNo) ?></strong>
    </div>

    <div class="table-card">
      <table class="att-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Check In</th>
            <th>Check Out</th>
            <th>Break In</th>
            <th>Break Out</th>
            <th>Status</th>
            <th>Remarks</th>
            <th>Created</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $i => $r): ?>
            <?php
              $statusClass = 'status-' . strtolower($r['status'] ?? 'pending');
              $checkIn  = trim(($r['check_in_date'] ?? '') . ' ' . ($r['check_in_time'] ?? ''));
              $checkOut = trim(($r['check_out_date'] ?? '') . ' ' . ($r['check_out_time'] ?? ''));
              $breakIn  = trim(($r['break_in_date'] ?? '') . ' ' . ($r['break_in_time'] ?? ''));
              $breakOut = trim(($r['break_out_date'] ?? '') . ' ' . ($r['break_out_time'] ?? ''));
            ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td class="time-cell"><?= $checkIn  ?: '—' ?></td>
              <td class="time-cell"><?= $checkOut ?: '—' ?></td>
              <td class="time-cell"><?= $breakIn  ?: '—' ?></td>
              <td class="time-cell"><?= $breakOut ?: '—' ?></td>
              <td><span class="<?= $statusClass ?>"><?= e($r['status'] ?? 'Pending') ?></span></td>
              <td class="remarks"><?= e($r['remarks'] ?? '') ?></td>
              <td class="time-cell"><?= $r['created_at'] ? date('Y-m-d H:i', strtotime($r['created_at'])) : '—' ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<script>
document.getElementById('session').addEventListener('change', e => {
  const o = e.target.selectedOptions[0];
  document.getElementById('from').value = o.dataset.from;
  document.getElementById('to').value   = o.dataset.to;
});
</script>
</body>
</html>