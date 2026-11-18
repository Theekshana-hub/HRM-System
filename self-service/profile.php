<?php
require 'config.php';
require_login();

$emp = find_employee($_SESSION['emp'][COL_EMP_NO]) ?? [];
$name = $emp['full_name'] ?? $emp['name_with_initials'] ?? $_SESSION['emp'][COL_EMP_NO];
$navName = $emp['name_with_initials'] ?? $name;

/* ---------- helpers ---------- */
function lookup_value(string $table, $id): ?string {
    try {
        $db = getDB();
        if ($table === 'employees') {
            $st = $db->prepare('SELECT * FROM `employees` WHERE id = ? OR emp_no = ? LIMIT 1');
            $st->bind_param('ss', $id, $id);
        } else {
            $st = $db->prepare('SELECT * FROM `'.$table.'` WHERE id = ? LIMIT 1');
            $st->bind_param('s', $id);
        }
        $st->execute();
        $r = $st->get_result()->fetch_assoc();
        $st->close();
        if (!$r) return null;
        foreach (['full_name','office_use_name'] as $k) if (!empty($r[$k])) return $r[$k];
        foreach ($r as $k => $v) if (stripos($k, 'name') !== false && $v !== '' && $v !== null) return $v;
        foreach (['title','description'] as $k) if (!empty($r[$k])) return $r[$k];
    } catch (Throwable $e) {}
    return null;
}

function fmt_date($v): string {
    $t = strtotime((string)$v);
    return $t ? date('d M Y', $t) : (string)$v;
}

// type: text | date | bool | lookup:<table>
function val(array $row, array $cands, string $type = 'text') {
    foreach ($cands as $c) {
        if ($c === '@dob_day'   && !empty($row['date_of_birth'])) return (int)date('j', strtotime($row['date_of_birth']));
        if ($c === '@dob_month' && !empty($row['date_of_birth'])) return (int)date('n', strtotime($row['date_of_birth']));
        if (!array_key_exists($c, $row) || $row[$c] === null || $row[$c] === '') continue;
        $v = $row[$c];
        if ($type === 'date') return fmt_date($v);
        if ($type === 'bool') return in_array(strtolower((string)$v), ['1','yes','true','y','active','on'], true);
        if (strpos($type, 'lookup:') === 0 && ctype_digit((string)$v)) {
            return lookup_value(substr($type, 7), $v) ?? $v;
        }
        return $v;
    }
    return $type === 'bool' ? false : '';
}

/* ---------- sections & fields: [label, candidate columns, type] ---------- */
$sections = [
 'master' => ['Employee Master', 'Personal Details', 'dark', [
   ['Emp No', ['emp_no']], ['Office Use Name', ['office_use_name']], ['Language', ['language']],
   ['Gender', ['gender']], ['Passport No', ['passport_no']], ['Marital Status', ['marital_status']],
   ['Initials', ['name_with_initials','initials']], ['Date Of Birth', ['date_of_birth'], 'date'], ['Nationality', ['nationality']],
   ['First Name', ['first_name']], ['Birth Date', ['@dob_day']], ['Race', ['race']],
   ['Middle Name', ['middle_name']], ['Birth Month', ['@dob_month']], ['Religion', ['religion']],
   ['Last Name', ['last_name']], ['NIC', [COL_NIC]], ['Blood Group', ['blood_group']],
   ['Full Name', ['full_name']], ['Title', ['title']], ['Distance From Work Place', ['distance_from_workplace','distance_from_work_place']],
 ]],
 'official' => ['Official Details', 'Official Details', 'light', [
   ['Office No', ['employee_office_id','office_no','emp_no']], ['Tax Payee No', ['tax_payee_no']], ['Company Name', ['company_name']],
   ['Proximity Card No', ['proximity_card_no']], ['Job Source', ['job_source']], ['Division Name', ['division_name']],
   ['Atendance Account No', ['attendance_account_no','atendance_account_no']], ['Man Power Company', ['manpower_company','man_power_company']], ['Sub Division Name', ['sub_division_name']],
   ['Is Active Employee', ['is_active_employee'], 'bool'], ['Salary Process', ['salary_process'], 'bool'], ['Department Name', ['department_name']],
   ['EPF No', ['ppf_epf_no','epf_no']], ['Salary Period', ['salary_period']], ['Sub Department Name', ['sub_department_name']],
   ['ETF No', ['etf_no']], ['Statute Name', ['statute_name','statute']], ['Section Name', ['section_name']],
   ['Is Tax Payee', ['is_tax_payee'], 'bool'], ['', []], ['Sub Section Name', ['sub_section_name']],
 ]],
 'contact' => ['Contact Details', 'Contact Details', 'dark', [
   ['Contact Type', ['contact_type']], ['Postal Code / Zip Code', ['postal_code','zip_code']], ['Work Tel', ['work_tel']],
   ['Address No', ['address_no','address']], ['Country', ['country']], ['Comments', ['address_comments','comments']],
   ['City', ['city']], ['Tel No', ['tel_no','telephone']], ['Office Email', ['office_email','email']],
   ['District', ['district']], ['Mobile No', ['mobile_no','mobile']], ['Private Email', ['private_email','personal_email']],
 ]],
 'job' => ['Job Details', 'Job Details', 'dark', [
   ['Designation', ['designation']], ['Date of Confirmation', ['date_of_confirmation','confirmation_date'], 'date'], ['Job Specification', ['job_specification']],
   ['Join Date', ['join_date','date_of_join','joined_date'], 'date'], ['Probation Period(Months)', ['probation_period_months']], ['Reporting Supervisor', ['reporting_supervisor'], 'lookup:employees'],
   ['Date of Appointment', ['date_of_appointment','appointment_date'], 'date'], ['Retirement Date', ['retirement_date'], 'date'], ['Comment', ['job_comment']],
   ['', []], ['Work Category', ['work_category']], ['Job Level', ['job_level']],
   ['', []], ['Job Category', ['job_category']], ['Rejoin Date', ['rejoin_date'], 'date'],
 ]],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Employee Profile - Sipway Campus</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Poppins',sans-serif;background:#fff;color:#222}
nav{background:#1ba1e2;color:#fff;height:58px;display:flex;align-items:center;justify-content:space-between;padding:0 clamp(15px,12vw,240px)}
nav a{color:#fff;text-decoration:none;font-size:15px;margin-right:24px}
.wrap{max-width:1430px;margin:0 auto;padding:0 20px 40px}
.head{display:flex;gap:70px;align-items:flex-start;margin:20px 0 25px}
.head svg{width:225px;height:225px;flex:none;border-radius:50%}
.head h1{font-weight:400;font-size:30px;margin-top:-6px}
.bar{display:flex;align-items:center;gap:12px;width:100%;border:0;cursor:pointer;color:#fff;font:400 18px 'Poppins',sans-serif;padding:14px 10px;margin-top:5px;text-align:left;
  background:repeating-linear-gradient(135deg,#0b2d5a 0 14px,#27508a 14px 28px)}
.bar.light{background:repeating-linear-gradient(135deg,#1ba1e2 0 14px,#42b0ea 14px 28px)}
.bar .arr{font-size:9px;transition:.2s}
.bar.open .arr{transform:rotate(90deg)}
.panel{display:none;border:1px solid #eee;padding:22px 12px 30px}
.panel.show{display:block}
.panel h2{font-weight:400;font-size:29px;padding-bottom:8px;border-bottom:1px solid #ddd;margin-bottom:22px}
.grid{display:grid;grid-template-columns:repeat(3,375px);gap:14px 25px}
.f label{display:block;font-size:17px;margin-bottom:6px;min-height:26px}
.f input{width:100%;height:40px;border:1px solid #ccc;padding:6px 8px;font:17px 'Poppins',sans-serif;background:#fff;color:#222}
.tg{display:inline-flex;width:60px;height:26px;border:1px solid #bbb;margin-top:4px}
.tg i{flex:1}.tg b{width:18px;background:#333}
.tg.on i{background:#17a600}.tg.off{flex-direction:row-reverse}.tg.off i{background:#ee1111}
.empty{visibility:hidden}
.dbg{margin-top:25px;font-size:13px;background:#f6f6f6;padding:12px;word-break:break-all}
@media(max-width:1250px){.grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:800px){.grid{grid-template-columns:1fr}.head{gap:20px;flex-direction:column}.head svg{width:140px;height:140px}}
</style>
</head>
<body>
<nav>
  <div><a href="dashboard.php"><b>&#9638; Smart HRIS</b></a><a href="dashboard.php">&#8630;</a><a href="dashboard.php">Home</a></div>
  <div style="font-size:15px">Hello, <?= e($navName) ?> &nbsp; <a href="logout.php" style="margin:0">Logout</a></div>
</nav>

<div class="wrap">
  <div class="head">
    <?php $photo = trim((string)($emp['photo'] ?? '')); ?>
    <?php if ($photo !== ''): ?><img src="../<?= e(ltrim($photo,'/')) ?>" alt="" style="width:225px;height:225px;border-radius:50%;object-fit:cover" onerror="this.style.display='none';document.getElementById('defav').style.display='block'"><?php endif; ?>
    <span id="defav" style="<?= $photo !== '' ? 'display:none' : '' ?>">    <svg viewBox="0 0 225 225"><circle cx="112" cy="112" r="112" fill="#c7dcfb"/><path d="M45 225c0-45 30-65 67-65s67 20 67 65z" fill="#3d6ea8"/><path d="M92 160l20 40 20-40z" fill="#eee"/><path d="M107 170h10l4 30-9 15-9-15z" fill="#e0636b"/><rect x="82" y="55" width="60" height="85" rx="28" fill="#fdd9c8"/><path d="M78 80c0-35 20-45 36-45s34 10 34 45c-6-20-20-26-36-26s-28 6-34 26z" fill="#2f4668"/></svg></span>
    <h1>Hi, <?= e($name) ?></h1>
  </div>

<?php foreach ($sections as $key => [$barTitle, $title, $style, $fields]): ?>
  <button type="button" class="bar <?= $style === 'light' ? 'light' : '' ?>" data-t="<?= $key ?>"><span class="arr">&#9654;</span> &#9873; <?= e($barTitle) ?></button>
  <div class="panel" id="p-<?= $key ?>">
    <h2><?= e($title) ?></h2>
    <div class="grid">
    <?php foreach ($fields as $f):
        [$label, $cands] = $f; $type = $f[2] ?? 'text';
        if ($label === ''): ?><div class="f empty"></div><?php continue; endif;
        $v = val($emp, $cands, $type); ?>
      <div class="f">
        <label><?= e($label) ?></label>
        <?php if ($type === 'bool'): ?>
          <span class="tg <?= $v ? 'on' : 'off' ?>"><i></i><b></b></span>
        <?php else: ?>
          <input type="text" value="<?= e($v) ?>" readonly>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
    </div>
  </div>
<?php endforeach; ?>

<?php if (isset($_GET['debug'])): $c = $emp; unset($c[COL_NIC]); ?>
  <div class="dbg"><b>DB columns (debug):</b><br><?= e(implode(', ', array_keys($c))) ?></div>
<?php endif; ?>
</div>

<script>
const bars = document.querySelectorAll('.bar');
function open_(key){
  bars.forEach(b=>{
    const on = b.dataset.t===key && !b.classList.contains('open');
    b.classList.toggle('open', on);
    document.getElementById('p-'+b.dataset.t).classList.toggle('show', on);
  });
}
bars.forEach(b=>b.addEventListener('click',()=>open_(b.dataset.t)));
open_('master');
</script>
</body>
</html>