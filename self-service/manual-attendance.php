<?php
require 'config.php';
require_login();
$emp   = $_SESSION['emp'];
$empNo = $emp[COL_EMP_NO];
$name  = $emp['name_with_initials'] ?? $emp['full_name'] ?? $empNo;
$db    = getDB();

// Table eka nathnam hadanawa
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
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Apply Manual Attendance</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Poppins',sans-serif;background:#fff;color:#222;font-size:16px}
nav{background:#1ba1e2;color:#fff;height:58px;display:flex;align-items:center;justify-content:space-between;padding:0 clamp(15px,12vw,240px)}
nav a{color:#fff;text-decoration:none;font-size:15px;margin-right:24px}
nav .user{font-size:15px}
.wrap{max-width:1430px;margin:24px auto;padding:0 20px}
.tabs{display:flex;gap:6px}
.tabs button{border:0;background:#eee;padding:10px 14px;font:500 16px 'Poppins',sans-serif;cursor:pointer;border-top:2px solid transparent}
.tabs button.active{background:#fff;border-top-color:#e00;box-shadow:0 -1px 0 #ddd}
.panel{border:1px solid #eee;padding:25px;display:none}
.panel.show{display:block}
h2{font-weight:400;font-size:27px;padding-bottom:10px;border-bottom:1px solid #ddd;margin-bottom:22px}
h3{font-size:21px;font-weight:600;margin:28px 0 18px}
.row{display:flex;align-items:center;margin-bottom:22px}
.row label.main{width:200px}
.remarks{width:565px;max-width:100%;height:40px;border:1px solid #888;padding:6px 10px;font:inherit}
.grid{display:grid;grid-template-columns:200px 170px 230px 100px 1fr;gap:14px 14px;align-items:center;padding-left:25px}
.btn-row{display:flex;gap:25px;flex-wrap:wrap;padding-left:25px;margin-bottom:18px}
.mode{background:#8cc4fb;color:#fff;border:0;padding:12px 35px;font:500 17px 'Poppins',sans-serif;border-radius:3px;box-shadow:0 2px 6px rgba(0,0,0,.25);cursor:pointer}
.mode.on{background:#1d7fe0}
.mode:hover{background:#4aa3f5}
.fld{display:grid;grid-template-columns:200px 170px 200px 120px;gap:16px 0;padding-left:25px;align-items:center;margin-bottom:6px}
.fld input{height:32px;border:1px solid #bbb;background:#f3f3f3;padding:2px 6px;font:15px monospace;width:168px}
.fld input:disabled{opacity:.55;cursor:not-allowed}
.fld .gap{padding-left:100px}
.actions{margin-top:26px;display:flex;gap:6px}
.submit{background:#0cc02b;color:#fff;border:0;width:125px;height:63px;font:500 17px 'Poppins',sans-serif;cursor:pointer;box-shadow:0 2px 6px rgba(0,0,0,.3)}
.new{background:#8f8f8f;color:#fff;border:0;width:125px;height:63px;font:500 17px 'Poppins',sans-serif;cursor:pointer;box-shadow:0 2px 6px rgba(0,0,0,.3)}
.alert{padding:12px 16px;border-radius:4px;margin-bottom:18px;font-size:15px}
.alert.err{background:#fde8ea;color:#b4232f}.alert.ok{background:#e3f7e8;color:#15803d}
details{margin-top:28px;width:675px;max-width:100%}
summary{cursor:pointer;color:#fff;font-size:19px;padding:16px 25px;list-style:none;background:repeating-linear-gradient(135deg,#0f3b73 0 14px,#0b2d5a 14px 28px)}
details .body{border:1px solid #ddd;border-top:0;padding:15px 25px;font-size:15px}
details .body p{margin-bottom:6px}
table{width:100%;border-collapse:collapse;font-size:14px}
th{background:#1ba1e2;color:#fff;text-align:left;padding:10px}
td{padding:9px 10px;border-bottom:1px solid #eee}
.badge{padding:3px 10px;border-radius:12px;font-size:12px;background:#fef3c7;color:#92400e}
.badge.Approved{background:#dcfce7;color:#166534}.badge.Rejected{background:#fee2e2;color:#991b1b}
.scroll{overflow-x:auto}
@media(max-width:900px){.fld{grid-template-columns:1fr 1fr}.fld .gap{padding-left:0}.row{flex-direction:column;align-items:flex-start;gap:8px}}
</style>
</head>
<body>
<nav>
  <div><a href="dashboard.php"><b>&#9638; Smart HRIS</b></a><a href="dashboard.php">&#8630;</a><a href="dashboard.php">Home</a></div>
  <div class="user">Hello, <?= e($name) ?> &nbsp; <a href="logout.php" style="margin:0">Logout</a></div>
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
      <div class="row"><label class="main">Remarks</label>
        <input class="remarks" type="text" name="remarks" maxlength="255" value="<?= $old('remarks') ?>"></div>

      <h3>Check In / Out</h3>
      <div class="btn-row">
        <button type="button" class="mode" data-g="c" data-m="in">Check In</button>
        <button type="button" class="mode" data-g="c" data-m="out">Check Out</button>
        <button type="button" class="mode" data-g="c" data-m="both">Both(In &amp; Out)</button>
        <button type="button" class="mode" id="incBreak">Include Break</button>
      </div>
      <div class="fld">
        <label>Check In Date :</label><input type="date" name="check_in_date" id="check_in_date" value="<?= $old('check_in_date') ?>" disabled>
        <label class="gap">Check In Time :</label><input type="time" name="check_in_time" id="check_in_time" value="<?= $old('check_in_time') ?>" disabled>
        <label>Check Out Date :</label><input type="date" name="check_out_date" id="check_out_date" value="<?= $old('check_out_date') ?>" disabled>
        <label class="gap">Check Out Time :</label><input type="time" name="check_out_time" id="check_out_time" value="<?= $old('check_out_time') ?>" disabled>
      </div>

      <h3>Break In / Out</h3>
      <div class="btn-row">
        <button type="button" class="mode" data-g="b" data-m="in">Break In</button>
        <button type="button" class="mode" data-g="b" data-m="out">Break Out</button>
        <button type="button" class="mode" data-g="b" data-m="both">Both(In &amp; Out)</button>
      </div>
      <div class="fld">
        <label>Break In Date :</label><input type="date" name="break_in_date" id="break_in_date" value="<?= $old('break_in_date') ?>" disabled>
        <label class="gap">Break In Time :</label><input type="time" name="break_in_time" id="break_in_time" value="<?= $old('break_in_time') ?>" disabled>
        <label>Break Out Date :</label><input type="date" name="break_out_date" id="break_out_date" value="<?= $old('break_out_date') ?>" disabled>
        <label class="gap">Break Out Time :</label><input type="time" name="break_out_time" id="break_out_time" value="<?= $old('break_out_time') ?>" disabled>
      </div>

      <div class="actions">
        <button class="submit" type="submit">Submit</button>
        <button class="new" type="button" onclick="resetForm()">New</button>
      </div>
    </form>

    <details>
      <summary>&#9776; Reporting Hierarchy</summary>
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
      <tr><th>#</th><th>Remarks</th><th>Check In</th><th>Check Out</th><th>Break In</th><th>Break Out</th><th>Status</th><th>Applied On</th></tr>
      <?php if (!$hist): ?><tr><td colspan="8" style="text-align:center;color:#888">No records found.</td></tr><?php endif; ?>
      <?php foreach ($hist as $i => $r):
        $f = fn($d,$t) => $d ? e($d.' '.substr($t,0,5)) : '-'; ?>
      <tr>
        <td><?= $i+1 ?></td><td><?= e($r['remarks']) ?></td>
        <td><?= $f($r['check_in_date'],$r['check_in_time']) ?></td>
        <td><?= $f($r['check_out_date'],$r['check_out_time']) ?></td>
        <td><?= $f($r['break_in_date'],$r['break_in_time']) ?></td>
        <td><?= $f($r['break_out_date'],$r['break_out_time']) ?></td>
        <td><span class="badge <?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
        <td><?= e($r['created_at']) ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
    </div>
  </div>
</div>

<script>
const ids = {c:{in:['check_in_date','check_in_time'],out:['check_out_date','check_out_time']},
             b:{in:['break_in_date','break_in_time'],out:['break_out_date','break_out_time']}};
const $ = id => document.getElementById(id);

function showTab(t){
  ['apply','history'].forEach(x=>{ $('p-'+x).classList.toggle('show',x===t); $('t-'+x).classList.toggle('active',x===t); });
}
function setFields(g, which, on){
  ids[g][which].forEach(id=>{ const el=$(id); el.disabled=!on; if(!on) el.value=''; });
}
function setMode(g, m){
  setFields(g,'in',  m==='in'  || m==='both');
  setFields(g,'out', m==='out' || m==='both');
  document.querySelectorAll('.mode[data-g="'+g+'"]').forEach(b=>b.classList.toggle('on', b.dataset.m===m));
}
document.querySelectorAll('.mode[data-g]').forEach(b=>b.addEventListener('click',()=>setMode(b.dataset.g,b.dataset.m)));
$('incBreak').addEventListener('click',()=>{ setMode('c','both'); setMode('b','both'); $('incBreak').classList.add('on'); });

function resetForm(){
  $('mform').reset();
  document.querySelectorAll('.mode').forEach(b=>b.classList.remove('on'));
  ['c','b'].forEach(g=>{ setFields(g,'in',false); setFields(g,'out',false); });
  document.querySelector('[name=remarks]').value='';
}
// POST error unama fields tika enable karanawa
Object.values(ids).forEach(g=>Object.values(g).forEach(arr=>{
  if(arr.some(id=>$(id).value)) arr.forEach(id=>$(id).disabled=false);
}));
showTab(<?= json_encode($tab === 'history' ? 'history' : 'apply') ?>);
</script>
</body>
</html>