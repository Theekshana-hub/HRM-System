<?php
require 'config.php';
require_login();

$emp = find_employee($_SESSION['emp'][COL_EMP_NO]) ?? [];
$name = $emp['full_name'] ?? $emp['name_with_initials'] ?? $_SESSION['emp'][COL_EMP_NO];
$navName = $emp['name_with_initials'] ?? $name;

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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Employee Profile - Sipway Campus</title>
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

  nav a {
    color: var(--text-muted);
    text-decoration: none;
    font-size: 14.5px;
    font-weight: 500;
    margin-right: 22px;
    transition: color 0.25s ease;
  }

  nav a:hover {
    color: var(--primary);
  }

  nav b {
    font-weight: 800;
    font-size: 17px;
    color: var(--text);
  }

  nav b span {
    background: linear-gradient(135deg, #0ea5e9, #38bdf8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  .user-info {
    font-size: 14.5px;
    font-weight: 600;
    color: var(--text);
  }

  .logout-btn {
    background: linear-gradient(135deg, #ef4444, #f87171);
    color: white !important;
    padding: 7px 16px;
    border-radius: 9px;
    font-weight: 600 !important;
    font-size: 13.5px !important;
    margin-left: 12px;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
    transition: all 0.3s ease;
  }

  .logout-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(239, 68, 68, 0.35);
  }

  .wrap {
    max-width: 1280px;
    margin: 0 auto;
    padding: 32px 24px 60px;
  }

  .head {
    display: flex;
    gap: 40px;
    align-items: center;
    margin-bottom: 36px;
    background: var(--card);
    padding: 28px 32px;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(14, 165, 233, 0.08);
  }

  .head img,
  .head svg {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    border: 4px solid #e0f2fe;
    box-shadow: 0 8px 24px rgba(14, 165, 233, 0.15);
  }

  .head h1 {
    font-weight: 800;
    font-size: 28px;
    letter-spacing: -0.5px;
    color: var(--text);
  }

  .head h1 span {
    display: block;
    font-size: 15px;
    font-weight: 500;
    color: var(--text-muted);
    margin-top: 6px;
  }

  .bar {
    display: flex;
    align-items: center;
    gap: 14px;
    width: 100%;
    border: none;
    cursor: pointer;
    color: #fff;
    font: 600 16px 'Plus Jakarta Sans', sans-serif;
    padding: 16px 22px;
    margin-top: 12px;
    text-align: left;
    border-radius: 14px;
    background: linear-gradient(135deg, #0f172a, #1e293b);
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(15, 23, 42, 0.15);
  }

  .bar.light {
    background: linear-gradient(135deg, #0ea5e9, #0284c7);
  }

  .bar:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(14, 165, 233, 0.25);
  }

  .bar .arr {
    font-size: 12px;
    transition: transform 0.3s ease;
  }

  .bar.open .arr {
    transform: rotate(90deg);
  }

  .panel {
    display: none;
    background: var(--card);
    border-radius: 0 0 16px 16px;
    padding: 28px 24px 32px;
    margin-top: -6px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    border: 1px solid var(--border);
    border-top: none;
    animation: fadeIn 0.35s ease;
  }

  .panel.show {
    display: block;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .panel h2 {
    font-weight: 700;
    font-size: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e0f2fe;
    margin-bottom: 24px;
    color: var(--text);
  }

  .grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px 22px;
  }

  .f label {
    display: block;
    font-size: 13.5px;
    font-weight: 600;
    color: var(--text-muted);
    margin-bottom: 6px;
  }

  .f input {
    width: 100%;
    height: 44px;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    padding: 0 14px;
    font: 500 15px 'Plus Jakarta Sans', sans-serif;
    background: #f8fafc;
    color: var(--text);
    transition: border-color 0.25s ease;
  }

  .f input:focus {
    outline: none;
    border-color: var(--primary);
  }

  .tg {
    display: inline-flex;
    width: 56px;
    height: 28px;
    border-radius: 20px;
    margin-top: 4px;
    overflow: hidden;
    border: none;
  }

  .tg i {
    flex: 1;
  }

  .tg b {
    width: 20px;
    background: #fff;
    border-radius: 50%;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
  }

  .tg.on {
    background: #22c55e;
  }

  .tg.off {
    background: #ef4444;
    flex-direction: row-reverse;
  }

  .empty {
    visibility: hidden;
  }

  .dbg {
    margin-top: 30px;
    font-size: 13px;
    background: #f8fafc;
    padding: 16px;
    border-radius: 12px;
    word-break: break-all;
    border: 1px solid var(--border);
  }

  @media (max-width: 1100px) {
    .grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media (max-width: 700px) {
    nav {
      padding: 0 16px;
      height: 60px;
    }

    .wrap {
      padding: 24px 16px 50px;
    }

    .head {
      flex-direction: column;
      text-align: center;
      gap: 20px;
      padding: 24px 20px;
    }

    .head img,
    .head svg {
      width: 120px;
      height: 120px;
    }

    .head h1 {
      font-size: 24px;
    }

    .grid {
      grid-template-columns: 1fr;
    }

    .bar {
      font-size: 15px;
      padding: 14px 18px;
    }
  }
</style>
</head>
<body>
<nav>
  <div>
    <a href="dashboard.php"><b>&#9638; <span>Smart HRIS</span></b></a>
    <a href="dashboard.php">&#8630; Back</a>
    <a href="dashboard.php">Home</a>
  </div>
  <div class="user-info">
    Hello, <?= e($navName) ?>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>
</nav>

<div class="wrap">
  <div class="head">
    <?php $photo = trim((string)($emp['photo'] ?? '')); ?>
    <?php if ($photo !== ''): ?>
      <img src="../<?= e(ltrim($photo,'/')) ?>" alt="" onerror="this.style.display='none';document.getElementById('defav').style.display='block'">
    <?php endif; ?>
    <span id="defav" style="<?= $photo !== '' ? 'display:none' : '' ?>">
      <svg viewBox="0 0 225 225">
        <circle cx="112" cy="112" r="112" fill="#c7dcfb"/>
        <path d="M45 225c0-45 30-65 67-65s67 20 67 65z" fill="#3d6ea8"/>
        <path d="M92 160l20 40 20-40z" fill="#eee"/>
        <path d="M107 170h10l4 30-9 15-9-15z" fill="#e0636b"/>
        <rect x="82" y="55" width="60" height="85" rx="28" fill="#fdd9c8"/>
        <path d="M78 80c0-35 20-45 36-45s34 10 34 45c-6-20-20-26-36-26s-28 6-34 26z" fill="#2f4668"/>
      </svg>
    </span>
    <h1>
      Hi, <?= e($name) ?>
      <span>Employee Profile</span>
    </h1>
  </div>

  <?php foreach ($sections as $key => [$barTitle, $title, $style, $fields]): ?>
    <button type="button" class="bar <?= $style === 'light' ? 'light' : '' ?>" data-t="<?= $key ?>">
      <span class="arr">&#9654;</span> <?= e($barTitle) ?>
    </button>
    <div class="panel" id="p-<?= $key ?>">
      <h2><?= e($title) ?></h2>
      <div class="grid">
        <?php foreach ($fields as $f):
          [$label, $cands] = $f;
          $type = $f[2] ?? 'text';
          if ($label === ''): ?>
            <div class="f empty"></div>
          <?php continue; endif;
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
  function open_(key) {
    bars.forEach(b => {
      const on = b.dataset.t === key && !b.classList.contains('open');
      b.classList.toggle('open', on);
      document.getElementById('p-' + b.dataset.t).classList.toggle('show', on);
    });
  }
  bars.forEach(b => b.addEventListener('click', () => open_(b.dataset.t)));
  open_('master');
</script>
</body>
</html>