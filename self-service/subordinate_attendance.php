<?php
require 'attendance-lib.php';
require_login();
$emp   = $_SESSION['emp'];
$empNo = $emp[COL_EMP_NO];
$navName = $emp['name_with_initials'] ?? $emp['full_name'] ?? $empNo;
$db = getDB();
att_ensure_table($db);

// Me employee ta report karana (reporting_supervisor = emp_no / full_name / office_use_name) අය
$names = array_values(array_filter([$empNo, $emp['full_name'] ?? '', $emp['office_use_name'] ?? '', $emp['name_with_initials'] ?? '']));
$ph = implode(',', array_fill(0, count($names), '?'));
$st = $db->prepare("SELECT emp_no, COALESCE(NULLIF(full_name,''), office_use_name) AS nm FROM employees WHERE reporting_supervisor IN ($ph) ORDER BY emp_no");
$st->bind_param(str_repeat('s', count($names)), ...$names);
$st->execute();
$subs = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$st->close();

$sessions = att_sessions();
$sel  = $_GET['session'] ?? array_key_first($sessions);
if (!isset($sessions[$sel])) $sel = array_key_first($sessions);
$from = $_GET['from'] ?? $sessions[$sel][1];
$to   = $_GET['to']   ?? $sessions[$sel][2];
if (!strtotime($from) || !strtotime($to) || $from > $to) { $from = $sessions[$sel][1]; $to = $sessions[$sel][2]; }

$target = $_GET['emp'] ?? ($subs[0]['emp_no'] ?? '');
$allowed = array_column($subs, 'emp_no');
$rows = in_array($target, $allowed, true) ? att_fetch($db, $target, $from, $to) : [];
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Subordinate Attendance Display</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<?php att_css(); ?></head><body>
<?php nav_bar($navName); ?>
<div class="wrap">
  <h1>Subordinate Attendance Display <small>v2.1.1</small></h1>
  <?php if (!$subs): ?>
    <div class="note">Oyata report karana employees kisiwek naha (employees table eke <b>reporting_supervisor</b> eke oyage nama/emp no thiyena employees innawa nam pennanawa).</div>
  <?php else: ?>
  <form method="get">
    <div class="frm">
      <label>Employee</label>
      <select name="emp">
        <?php foreach ($subs as $s): ?>
          <option value="<?= e($s['emp_no']) ?>" <?= $s['emp_no'] === $target ? 'selected' : '' ?>><?= e($s['emp_no'].' - '.$s['nm']) ?></option>
        <?php endforeach; ?>
      </select>
      <label>Session</label>
      <select name="session" id="session">
        <?php foreach ($sessions as $code => [$label, $f, $t]): ?>
          <option value="<?= e($code) ?>" data-from="<?= $f ?>" data-to="<?= $t ?>" <?= $code === $sel ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>
      <label>From Date</label><input type="date" name="from" id="from" value="<?= e($from) ?>">
      <label>To Date</label><input type="date" name="to" id="to" value="<?= e($to) ?>">
    </div>
    <button class="btn" type="submit">Search</button>
  </form>
  <?php att_render($rows, $from, $to); ?>
  <?php endif; ?>
</div>
<script>
const s=document.getElementById('session');
if(s) s.addEventListener('change',e=>{const o=e.target.selectedOptions[0];document.getElementById('from').value=o.dataset.from;document.getElementById('to').value=o.dataset.to;});
</script>
</body></html>