<?php
require 'attendance-lib.php';
require_login();

$emp     = $_SESSION['emp'];
$empNo   = $emp[COL_EMP_NO];
$navName = $emp['name_with_initials'] ?? $emp['full_name'] ?? $empNo;
$db      = getDB();

// Sessions (same as before)
$sessions = att_sessions();
$sel  = $_GET['session'] ?? array_key_first($sessions);
if (!isset($sessions[$sel])) $sel = array_key_first($sessions);

$from = $_GET['from'] ?? $sessions[$sel][1];
$to   = $_GET['to']   ?? $sessions[$sel][2];

if (!strtotime($from) || !strtotime($to) || $from > $to) {
    $from = $sessions[$sel][1];
    $to   = $sessions[$sel][2];
}

// ========== Fetch from manual_attendance ==========
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
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Attendance Display (Manual)</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<?php att_css(); ?>
<style>
    .att-table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
    .att-table th, .att-table td { border: 1px solid #ddd; padding: 10px 8px; text-align: center; }
    .att-table th { background: #1e3a5f; color: #fff; font-weight: 500; }
    .att-table tr:nth-child(even) { background: #f8fafc; }
    .att-table tr:hover { background: #eef4ff; }
    .status-pending { color: #d97706; font-weight: 600; }
    .status-approved { color: #059669; font-weight: 600; }
    .status-rejected { color: #dc2626; font-weight: 600; }
    .time-cell { white-space: nowrap; }
    .remarks { max-width: 180px; text-align: left; }
    .empty-note { background: #fff7ed; border: 1px solid #fdba74; padding: 14px 18px; border-radius: 8px; margin: 20px 0; color: #9a3412; }
</style>
</head>
<body>
<?php nav_bar($navName); ?>

<div class="wrap">
    <h1>Attendance Display <small>Manual Attendance • v2.1.1</small></h1>

    <form method="get" id="f">
        <div class="frm">
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

            <label>From Date</label>
            <input type="date" name="from" id="from" value="<?= e($from) ?>">

            <label>To Date</label>
            <input type="date" name="to" id="to" value="<?= e($to) ?>">
        </div>
        <button class="btn" type="submit">Search</button>
    </form>

    <?php if (empty($rows)): ?>
        <div class="empty-note">
            මෙම period එකට manual attendance data නැහැ.<br>
            <small>Employee: <strong><?= e($empNo) ?></strong> | Date range: <?= e($from) ?> → <?= e($to) ?></small>
        </div>
    <?php else: ?>

        <div style="margin: 12px 0 8px; color:#475569; font-size:14px;">
            Showing <strong><?= count($rows) ?></strong> record(s) for <strong><?= e($empNo) ?></strong>
        </div>

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
                        <td class="<?= $statusClass ?>"><?= e($r['status'] ?? 'Pending') ?></td>
                        <td class="remarks"><?= e($r['remarks'] ?? '') ?></td>
                        <td class="time-cell"><?= $r['created_at'] ? date('Y-m-d H:i', strtotime($r['created_at'])) : '—' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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