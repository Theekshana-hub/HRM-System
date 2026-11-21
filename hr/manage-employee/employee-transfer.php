<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

$conn = getDB();
$message = '';
$msgType = '';
$emp = null;

$conn->query("CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    emp_no VARCHAR(50) UNIQUE,
    gender VARCHAR(20), title VARCHAR(20), initials VARCHAR(50),
    denoted_by_initial VARCHAR(100), first_name VARCHAR(100), middle_name VARCHAR(100),
    last_name VARCHAR(100), name_with_initials VARCHAR(200), full_name VARCHAR(250),
    office_use_name VARCHAR(200), language VARCHAR(50), passport_no VARCHAR(50),
    marital_status VARCHAR(30), date_of_birth DATE, nationality VARCHAR(50),
    age INT, race VARCHAR(50), birth_place VARCHAR(100), religion VARCHAR(50),
    nic VARCHAR(20), blood_group VARCHAR(10), emp_id_issue_date DATE,
    distance_from_workplace VARCHAR(50), photo VARCHAR(255) DEFAULT NULL,
    employee_office_id VARCHAR(50), tax_payee_no VARCHAR(50), company_name VARCHAR(100),
    proximity_card_no VARCHAR(50), job_source VARCHAR(50) DEFAULT 'Interviews',
    division_name VARCHAR(100), attendance_account_no VARCHAR(50),
    manpower_company VARCHAR(100) DEFAULT 'None', sub_division_name VARCHAR(100),
    is_active_employee TINYINT(1) DEFAULT 1, salary_process TINYINT(1) DEFAULT 1,
    department_name VARCHAR(100), ppf_epf_no VARCHAR(50),
    salary_period VARCHAR(30) DEFAULT 'Monthly', sub_department_name VARCHAR(100),
    employee_fund VARCHAR(50) DEFAULT 'EPF Applicable', basic_salary DECIMAL(12,2) DEFAULT 0,
    section_name VARCHAR(100), etf_no VARCHAR(50), statute_name VARCHAR(100) DEFAULT 'Shop & Office',
    sub_section_name VARCHAR(100), is_tax_payee TINYINT(1) DEFAULT 0,
    next_salary_increment_date DATE, base_customer_site VARCHAR(150), erp_code VARCHAR(50),
    address_no VARCHAR(100), contact_type VARCHAR(50), postal_code VARCHAR(20),
    work_tel VARCHAR(30), city VARCHAR(100), country VARCHAR(50) DEFAULT 'Sri Lanka',
    office_email VARCHAR(150), district VARCHAR(100), tel_no VARCHAR(30),
    private_email VARCHAR(150), province VARCHAR(100), mobile_no VARCHAR(30),
    address_comments TEXT, designation VARCHAR(150), resign_date DATE,
    reporting_supervisor VARCHAR(150), join_date DATE, leaving_type VARCHAR(50),
    job_level VARCHAR(50) DEFAULT 'Executive', date_of_appointment DATE,
    retirement_date DATE, rejoin_date DATE, work_category VARCHAR(100),
    grade VARCHAR(50), date_of_confirmation DATE, job_category VARCHAR(100),
    type_of_labor_cost VARCHAR(50), contract_period_months INT, probation_period_months INT,
    job_comment TEXT, job_specification TEXT, job_description TEXT,
    attendance_group VARCHAR(100), att_from_date DATE, att_to_date DATE,
    is_permanent TINYINT(1) DEFAULT 0, employee_work_schedule VARCHAR(100),
    check_by_timezone TINYINT(1) DEFAULT 0, allow_temporary_shifts TINYINT(1) DEFAULT 0,
    checkin_is_must TINYINT(1) DEFAULT 0, allow_flexy_attendance TINYINT(1) DEFAULT 0,
    checkout_is_must TINYINT(1) DEFAULT 0, ot_applicable TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS employee_transfers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    emp_id INT,
    old_emp_no VARCHAR(50),
    new_emp_no VARCHAR(50),
    reason_to_transfer VARCHAR(150),
    new_company VARCHAR(100),
    new_division VARCHAR(100),
    new_sub_division VARCHAR(100),
    new_department VARCHAR(100),
    new_sub_department VARCHAR(100),
    new_section VARCHAR(100),
    new_sub_section VARCHAR(100),
    new_designation VARCHAR(150),
    new_ppf_epf_no VARCHAR(50),
    new_erp_code VARCHAR(50),
    resign_date DATE,
    effective_date DATE,
    date_of_appointment DATE,
    work_category VARCHAR(100),
    job_category VARCHAR(100),
    contract_period_months INT,
    probation_period_months INT,
    type_of_labour_cost VARCHAR(50),
    grade VARCHAR(50),
    job_level VARCHAR(50),
    leaving_type VARCHAR(50),
    approved_by VARCHAR(150),
    remark TEXT,
    transferred_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

if (isset($_GET['ajax_search'])) {
    header('Content-Type: application/json');
    $q = $conn->real_escape_string(trim($_GET['ajax_search']));
    $res = $conn->query("SELECT id, emp_no, full_name, name_with_initials FROM employees WHERE emp_no LIKE '%$q%' OR full_name LIKE '%$q%' OR name_with_initials LIKE '%$q%' LIMIT 10");
    $list = [];
    while ($r = $res->fetch_assoc()) {
        $list[] = ['id' => $r['id'], 'label' => $r['emp_no'] . ' | ' . ($r['full_name'] ?: $r['name_with_initials'])];
    }
    echo json_encode($list);
    exit;
}

if (isset($_GET['search']) || isset($_GET['id'])) {
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = (int)$_GET['id'];
        $res = $conn->query("SELECT * FROM employees WHERE id = $id");
    } else {
        $q = $conn->real_escape_string(trim($_GET['q'] ?? ''));
        $res = $conn->query("SELECT * FROM employees WHERE emp_no LIKE '%$q%' OR full_name LIKE '%$q%' OR name_with_initials LIKE '%$q%' LIMIT 1");
    }
    if ($res && $res->num_rows > 0) {
        $emp = $res->fetch_assoc();
    } else {
        $message = "No employee found.";
        $msgType = 'error';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'transfer') {
    $emp_id = (int)$_POST['emp_id'];
    $old_emp_no = $conn->real_escape_string($_POST['old_emp_no'] ?? '');

    $fields = [
        'reason_to_transfer','new_company','new_emp_no','new_division','new_ppf_epf_no',
        'new_sub_division','new_erp_code','new_department','new_designation','new_sub_department',
        'resign_date','new_section','effective_date','new_sub_section','date_of_appointment',
        'work_category','job_category','contract_period_months','probation_period_months',
        'type_of_labour_cost','grade','job_level','leaving_type','approved_by','remark'
    ];
    $data = [];
    foreach ($fields as $f) {
        $data[$f] = isset($_POST[$f]) ? trim($_POST[$f]) : null;
        if ($data[$f] === '') $data[$f] = null;
    }

    $cols = ['emp_id','old_emp_no'];
    $vals = [$emp_id, "'$old_emp_no'"];
    foreach ($data as $k => $v) {
        $cols[] = $k;
        $vals[] = $v === null ? 'NULL' : "'" . $conn->real_escape_string($v) . "'";
    }

    // Fixed SQL
    $sql = "INSERT INTO employee_transfers (`" . implode('`,`', $cols) . "`) VALUES (" . implode(',', $vals) . ")";

    if ($conn->query($sql)) {
        $updates = [];
        if ($data['new_emp_no']) $updates[] = "emp_no='" . $conn->real_escape_string($data['new_emp_no']) . "'";
        if ($data['new_company']) $updates[] = "company_name='" . $conn->real_escape_string($data['new_company']) . "'";
        if ($data['new_division']) $updates[] = "division_name='" . $conn->real_escape_string($data['new_division']) . "'";
        if ($data['new_sub_division']) $updates[] = "sub_division_name='" . $conn->real_escape_string($data['new_sub_division']) . "'";
        if ($data['new_department']) $updates[] = "department_name='" . $conn->real_escape_string($data['new_department']) . "'";
        if ($data['new_sub_department']) $updates[] = "sub_department_name='" . $conn->real_escape_string($data['new_sub_department']) . "'";
        if ($data['new_section']) $updates[] = "section_name='" . $conn->real_escape_string($data['new_section']) . "'";
        if ($data['new_sub_section']) $updates[] = "sub_section_name='" . $conn->real_escape_string($data['new_sub_section']) . "'";
        if ($data['new_designation']) $updates[] = "designation='" . $conn->real_escape_string($data['new_designation']) . "'";
        if ($data['new_ppf_epf_no']) $updates[] = "ppf_epf_no='" . $conn->real_escape_string($data['new_ppf_epf_no']) . "'";
        if ($data['new_erp_code']) $updates[] = "erp_code='" . $conn->real_escape_string($data['new_erp_code']) . "'";
        if ($data['work_category']) $updates[] = "work_category='" . $conn->real_escape_string($data['work_category']) . "'";
        if ($data['job_category']) $updates[] = "job_category='" . $conn->real_escape_string($data['job_category']) . "'";
        if ($data['job_level']) $updates[] = "job_level='" . $conn->real_escape_string($data['job_level']) . "'";
        if ($data['grade']) $updates[] = "grade='" . $conn->real_escape_string($data['grade']) . "'";
        if ($data['effective_date']) $updates[] = "join_date='" . $conn->real_escape_string($data['effective_date']) . "'";
        if ($data['resign_date']) $updates[] = "resign_date='" . $conn->real_escape_string($data['resign_date']) . "'";

        if (count($updates) > 0) {
            $conn->query("UPDATE employees SET " . implode(',', $updates) . " WHERE id=$emp_id");
        }

        $message = "Employee transferred successfully!";
        $msgType = 'success';
        $res = $conn->query("SELECT * FROM employees WHERE id=$emp_id");
        $emp = $res->fetch_assoc();
    } else {
        $message = "Transfer failed: " . $conn->error;
        $msgType = 'error';
    }
}

function v($emp, $key, $default = '') {
    return ($emp && isset($emp[$key]) && $emp[$key] !== null) ? htmlspecialchars($emp[$key]) : $default;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sipway Campus - Employee Transfer</title>
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
    min-height: 100vh;
  }

  /* ========== NAVBAR ========== */
  .navbar {
    background: rgba(15, 23, 42, 0.95);
    backdrop-filter: blur(16px);
    height: 64px;
    display: flex;
    align-items: center;
    padding: 0 24px;
    color: white;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 4px 24px rgba(0,0,0,0.2);
    border-bottom: 1px solid rgba(255,255,255,0.06);
  }

  .nav-left { display: flex; align-items: center; gap: 14px; }

  .nav-logo {
    font-weight: 800;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
    color: white;
    text-decoration: none;
  }

  .nav-logo .icon {
    width: 28px; height: 28px;
    background: linear-gradient(135deg, #0ea5e9, #8b5cf6);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 800;
    box-shadow: 0 4px 12px rgba(14,165,233,0.4);
  }

  .nav-btn {
    background: transparent;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    padding: 8px;
    border-radius: 10px;
    transition: all 0.25s;
  }
  .nav-btn:hover { background: rgba(255,255,255,0.1); color: white; }
  .nav-btn svg { width: 18px; height: 18px; }

  .search-nav {
    flex: 1;
    max-width: 260px;
    margin: 0 20px;
  }
  .search-nav input {
    width: 100%;
    padding: 9px 16px;
    border-radius: 50px;
    border: 1px solid rgba(255,255,255,0.1);
    background: rgba(255,255,255,0.08);
    color: white;
    font-size: 13.5px;
    outline: none;
    font-family: inherit;
  }
  .search-nav input::placeholder { color: #64748b; }

  .nav-right {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 14px;
    font-size: 13.5px;
    color: #cbd5e1;
  }
  .nav-right strong { color: white; }

  /* ========== MAIN ========== */
  .main {
    padding: 28px 32px 50px;
    max-width: 1200px;
    margin: 0 auto;
  }

  .page-title {
    font-size: 24px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 22px;
    letter-spacing: -0.3px;
  }

  /* Search bar */
  .search-bar {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 24px;
    flex-wrap: wrap;
  }

  .search-bar label {
    font-size: 14px;
    color: var(--text);
    font-weight: 600;
    white-space: nowrap;
  }

  .search-wrap {
    position: relative;
    flex: 1;
    max-width: 380px;
  }

  .search-wrap input {
    width: 100%;
    padding: 12px 16px;
    border: 1.5px solid var(--border);
    border-radius: 12px;
    font-size: 14px;
    font-family: inherit;
    outline: none;
    background: white;
    transition: border-color 0.25s;
  }
  .search-wrap input:focus { border-color: var(--primary); }

  .suggestions {
    position: absolute;
    top: calc(100% + 6px);
    left: 0; right: 0;
    background: white;
    border: 1px solid var(--border);
    border-radius: 12px;
    box-shadow: 0 12px 28px rgba(0,0,0,0.12);
    z-index: 50;
    display: none;
    max-height: 220px;
    overflow-y: auto;
  }
  .suggestions.show { display: block; }
  .suggestions div {
    padding: 12px 16px;
    cursor: pointer;
    font-size: 13.5px;
    border-bottom: 1px solid #f1f5f9;
  }
  .suggestions div:hover { background: #f0f9ff; }

  .btn {
    padding: 11px 22px;
    border: none;
    border-radius: 11px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
    color: white;
    transition: all 0.25s;
  }

  .btn-search {
    background: linear-gradient(135deg, #0ea5e9, #0284c7);
    box-shadow: 0 4px 12px rgba(14,165,233,0.3);
  }
  .btn-search:hover { transform: translateY(-2px); }

  .btn-new {
    background: #94a3b8;
  }
  .btn-new:hover { background: #64748b; }

  .btn-transfer {
    background: linear-gradient(135deg, #1e40af, #1e3a8a);
    padding: 14px 32px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 15px;
    box-shadow: 0 4px 16px rgba(30,64,175,0.3);
  }
  .btn-transfer:hover { transform: translateY(-2px); }

  .alert {
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 18px;
    font-size: 14px;
    font-weight: 500;
  }
  .alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #6ee7b7;
  }
  .alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
  }

  /* Employee Card */
  .emp-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 22px 26px;
    margin-bottom: 28px;
    display: flex;
    align-items: center;
    gap: 22px;
    box-shadow: 0 6px 20px rgba(14,165,233,0.07);
  }

  .emp-avatar {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background: linear-gradient(145deg, #dbeafe, #eff6ff);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border: 4px solid #e0f2fe;
  }
  .emp-avatar svg { width: 54px; height: 54px; }

  .emp-info h2 {
    font-size: 19px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 10px;
  }

  .emp-info table {
    font-size: 13.5px;
    color: var(--text-muted);
  }
  .emp-info td {
    padding: 3px 14px 3px 0;
  }
  .emp-info td:first-child {
    font-weight: 600;
    color: #475569;
    min-width: 130px;
  }

  /* Two columns */
  .transfer-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
  }

  .panel {
    background: white;
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 22px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.04);
  }

  .panel.current {
    background: #f8fafc;
  }

  .panel.new {
    background: #f0fdf4;
    border-color: #bbf7d0;
  }

  .panel h3 {
    font-size: 15.5px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 18px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e2e8f0;
  }

  .panel.new h3 {
    border-color: #bbf7d0;
  }

  .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px 16px;
  }

  .form-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
  }
  .form-group.full { grid-column: 1 / -1; }

  .form-group label {
    font-size: 12.5px;
    color: var(--text-muted);
    font-weight: 600;
  }
  .form-group label .req { color: #ef4444; }

  .form-group input,
  .form-group select,
  .form-group textarea {
    padding: 10px 12px;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    font-size: 13.5px;
    font-family: inherit;
    outline: none;
    background: white;
    transition: border-color 0.25s;
  }
  .form-group input:focus,
  .form-group select:focus {
    border-color: var(--primary);
  }

  .form-group input.readonly {
    background: #f1f5f9;
    color: #64748b;
    border-color: #e2e8f0;
  }

  .form-group input.required-field,
  .form-group select.required-field {
    background: #eff6ff;
  }

  .panel.new .form-group input.required-field,
  .panel.new .form-group select.required-field {
    background: #dcfce7;
  }

  .transfer-btn-wrap {
    text-align: center;
    margin-top: 28px;
  }

  @media (max-width: 900px) {
    .transfer-grid { grid-template-columns: 1fr; }
    .main { padding: 20px 16px 40px; }
    .emp-card { flex-direction: column; text-align: center; }
  }
</style>
</head>
<body>

<nav class="navbar">
  <div class="nav-left">
    <a href="../dashboard.php" class="nav-logo">
      <div class="icon">S</div> SIPWAY CAMPUS
    </a>
    <button class="nav-btn" onclick="history.back()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    </button>
    <button class="nav-btn" onclick="location.href='../dashboard.php'">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
    </button>
  </div>
  <div class="search-nav"><input type="text" placeholder="Search..."></div>
  <div class="nav-right">
    <span>Welcome <strong>Admin</strong></span>
    <button class="nav-btn" onclick="location.href='../index.php'" title="Logout">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
    </button>
  </div>
</nav>

<div class="main">
  <h1 class="page-title">Employee Transfer</h1>

  <?php if ($message): ?>
  <div class="alert alert-<?= $msgType ?>"><?= $message ?></div>
  <?php endif; ?>

  <div class="search-bar">
    <label>Enter Employee</label>
    <div class="search-wrap">
      <input type="text" id="searchInput" placeholder="Emp No or Name..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : v($emp,'emp_no') ?>" autocomplete="off">
      <div class="suggestions" id="suggestions"></div>
    </div>
    <button class="btn btn-search" onclick="doSearch()">Search</button>
    <button class="btn btn-new" onclick="location.href='employee-transfer.php'">New</button>
  </div>

  <?php if ($emp): ?>
  <div class="emp-card">
    <div class="emp-avatar">
      <svg viewBox="0 0 64 64" fill="none">
        <circle cx="32" cy="32" r="32" fill="#3b82f6"/>
        <circle cx="32" cy="24" r="12" fill="white"/>
        <path d="M12 56c0-11 9-16 20-16s20 5 20 16" fill="white"/>
      </svg>
    </div>
    <div class="emp-info">
      <h2><?= v($emp,'full_name') ?: v($emp,'name_with_initials') ?></h2>
      <table>
        <tr><td>Employee No</td><td><?= v($emp,'emp_no') ?></td></tr>
        <tr><td>Designation</td><td><?= v($emp,'designation') ?></td></tr>
        <tr><td>Work Category</td><td><?= v($emp,'work_category') ?></td></tr>
        <tr><td>Job Category</td><td><?= v($emp,'job_category') ?></td></tr>
        <tr><td>Company</td><td><?= v($emp,'company_name') ?></td></tr>
      </table>
    </div>
  </div>

  <form method="POST">
    <input type="hidden" name="action" value="transfer">
    <input type="hidden" name="emp_id" value="<?= v($emp,'id') ?>">
    <input type="hidden" name="old_emp_no" value="<?= v($emp,'emp_no') ?>">

    <div class="transfer-grid">
      <!-- Current -->
      <div class="panel current">
        <h3>Current Employee Details</h3>
        <div class="form-grid">
          <div class="form-group full">
            <label>Company</label>
            <input type="text" class="readonly" readonly value="<?= v($emp,'company_name') ?>">
          </div>
          <div class="form-group">
            <label>Employee No</label>
            <input type="text" class="readonly" readonly value="<?= v($emp,'emp_no') ?>">
          </div>
          <div class="form-group">
            <label>Division Name</label>
            <input type="text" class="readonly" readonly value="<?= v($emp,'division_name') ?>">
          </div>
          <div class="form-group">
            <label>PPF/EPF No</label>
            <input type="text" class="readonly" readonly value="<?= v($emp,'ppf_epf_no') ?>">
          </div>
          <div class="form-group">
            <label>Sub Division Name</label>
            <input type="text" class="readonly" readonly value="<?= v($emp,'sub_division_name') ?>">
          </div>
          <div class="form-group">
            <label>ERP Code</label>
            <input type="text" class="readonly" readonly value="<?= v($emp,'erp_code') ?>">
          </div>
          <div class="form-group">
            <label>Department Name</label>
            <input type="text" class="readonly" readonly value="<?= v($emp,'department_name') ?>">
          </div>
          <div class="form-group">
            <label>Designation</label>
            <input type="text" class="readonly" readonly value="<?= v($emp,'designation') ?>">
          </div>
          <div class="form-group">
            <label>Sub Department Name</label>
            <input type="text" class="readonly" readonly value="<?= v($emp,'sub_department_name') ?>">
          </div>
          <div class="form-group">
            <label>Resign Date</label>
            <input type="text" class="readonly" readonly value="<?= v($emp,'resign_date') ?>">
          </div>
          <div class="form-group">
            <label>Section Name</label>
            <input type="text" class="readonly" readonly value="<?= v($emp,'section_name') ?>">
          </div>
          <div class="form-group">
            <label>Join Date</label>
            <input type="text" class="readonly" readonly value="<?= v($emp,'join_date') ?>">
          </div>
          <div class="form-group">
            <label>Sub Section Name</label>
            <input type="text" class="readonly" readonly value="<?= v($emp,'sub_section_name') ?>">
          </div>
          <div class="form-group">
            <label>Date of Appointment</label>
            <input type="text" class="readonly" readonly value="<?= v($emp,'date_of_appointment') ?>">
          </div>
          <div class="form-group">
            <label>Work Category</label>
            <input type="text" class="readonly" readonly value="<?= v($emp,'work_category') ?>">
          </div>
          <div class="form-group">
            <label>Job Category</label>
            <input type="text" class="readonly" readonly value="<?= v($emp,'job_category') ?>">
          </div>
          <div class="form-group">
            <label>Contract Period (Months)</label>
            <input type="text" class="readonly" readonly value="<?= v($emp,'contract_period_months','0') ?>">
          </div>
          <div class="form-group">
            <label>Probation Period (Months)</label>
            <input type="text" class="readonly" readonly value="<?= v($emp,'probation_period_months','0') ?>">
          </div>
          <div class="form-group">
            <label>Date of Confirmation</label>
            <input type="text" class="readonly" readonly value="<?= v($emp,'date_of_confirmation') ?>">
          </div>
          <div class="form-group">
            <label>Type of Labour Cost</label>
            <input type="text" class="readonly" readonly value="<?= v($emp,'type_of_labor_cost') ?>">
          </div>
          <div class="form-group">
            <label>Grade</label>
            <input type="text" class="readonly" readonly value="<?= v($emp,'grade') ?>">
          </div>
          <div class="form-group">
            <label>Job Level</label>
            <input type="text" class="readonly" readonly value="<?= v($emp,'job_level') ?>">
          </div>
        </div>
      </div>

      <!-- New -->
      <div class="panel new">
        <h3>New Employee Details</h3>
        <div class="form-grid">
          <div class="form-group full">
            <label>Reason to Transfer <span class="req">*</span></label>
            <select name="reason_to_transfer" class="required-field" required>
              <option value="">-Select-</option>
              <option>Promotion</option>
              <option>Department Change</option>
              <option>Location Transfer</option>
              <option>Company Transfer</option>
              <option>Other</option>
            </select>
          </div>
          <div class="form-group full">
            <label>Company <span class="req">*</span></label>
            <select name="new_company" class="required-field">
              <option value="">-select-</option>
              <option value="Sipway Campus">Sipway Campus</option>
              <option value="Sipway Campus Pvt Ltd">Sipway Campus Pvt Ltd</option>
            </select>
          </div>
          <div class="form-group">
            <label>New Employee No <span class="req">*</span></label>
            <input type="text" name="new_emp_no" class="required-field" value="<?= v($emp,'emp_no') ?>">
          </div>
          <div class="form-group">
            <label>Division Name <span class="req">*</span></label>
            <select name="new_division" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['IT','HR','Finance','Operations','Academic'] as $d): ?>
              <option value="<?= $d ?>"><?= $d ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>New PPF/EPF No <span class="req">*</span></label>
            <input type="text" name="new_ppf_epf_no" class="required-field">
          </div>
          <div class="form-group">
            <label>Sub Division Name <span class="req">*</span></label>
            <select name="new_sub_division" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['Development','Support','Admin','None'] as $s): ?>
              <option value="<?= $s ?>"><?= $s ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>ERP Code</label>
            <input type="text" name="new_erp_code">
          </div>
          <div class="form-group">
            <label>Department Name <span class="req">*</span></label>
            <select name="new_department" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['IT Department','HR Department','Finance Department','Academic Department'] as $d): ?>
              <option value="<?= $d ?>"><?= $d ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Designation <span class="req">*</span></label>
            <select name="new_designation" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['Software Developer','IT Assistant','HR Executive','Accountant','Lecturer','Manager'] as $d): ?>
              <option value="<?= $d ?>"><?= $d ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Sub Department Name <span class="req">*</span></label>
            <select name="new_sub_department" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['Software','Hardware','General','None'] as $s): ?>
              <option value="<?= $s ?>"><?= $s ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Resign Date</label>
            <input type="date" name="resign_date">
          </div>
          <div class="form-group">
            <label>Section Name <span class="req">*</span></label>
            <select name="new_section" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['Section A','Section B','Section C','None'] as $s): ?>
              <option value="<?= $s ?>"><?= $s ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Effective Date <span class="req">*</span></label>
            <input type="date" name="effective_date" class="required-field" required>
          </div>
          <div class="form-group">
            <label>Sub Section Name <span class="req">*</span></label>
            <select name="new_sub_section" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['Sub A','Sub B','None'] as $s): ?>
              <option value="<?= $s ?>"><?= $s ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Date of Appointment <span class="req">*</span></label>
            <input type="date" name="date_of_appointment" class="required-field">
          </div>
          <div class="form-group">
            <label>Work Category <span class="req">*</span></label>
            <select name="work_category" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['Permanent','Full Time','Part Time','Contract','Intern'] as $w): ?>
              <option value="<?= $w ?>"><?= $w ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Job Category <span class="req">*</span></label>
            <select name="job_category" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['Staff','Technical','Administrative','Academic','Support'] as $j): ?>
              <option value="<?= $j ?>"><?= $j ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Contract Period (Months) <span class="req">*</span></label>
            <input type="number" name="contract_period_months" class="required-field" value="0">
          </div>
          <div class="form-group">
            <label>Probation Period (Months) <span class="req">*</span></label>
            <input type="number" name="probation_period_months" class="required-field" value="0">
          </div>
          <div class="form-group">
            <label>Type of Labour Cost <span class="req">*</span></label>
            <select name="type_of_labour_cost" class="required-field">
              <option value="">-select-</option>
              <option>Direct</option>
              <option>Indirect</option>
              <option>None</option>
            </select>
          </div>
          <div class="form-group">
            <label>Grade <span class="req">*</span></label>
            <select name="grade" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['A','B','C','D','E','None'] as $g): ?>
              <option value="<?= $g ?>"><?= $g ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Job Level <span class="req">*</span></label>
            <select name="job_level" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['Executive','Non Executive','Manager','Senior Manager','Director'] as $j): ?>
              <option value="<?= $j ?>"><?= $j ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group full">
            <label>Leaving Type <span class="req">*</span></label>
            <select name="leaving_type" class="required-field">
              <option value="">-select-</option>
              <option>Transfer</option>
              <option>Promotion</option>
              <option>Resignation</option>
              <option>Other</option>
            </select>
          </div>
          <div class="form-group full">
            <label>Approved By <span class="req">*</span></label>
            <input type="text" name="approved_by" class="required-field" required>
          </div>
          <div class="form-group full">
            <label>Remark <span class="req">*</span></label>
            <input type="text" name="remark" class="required-field">
          </div>
        </div>
      </div>
    </div>

    <div class="transfer-btn-wrap">
      <button type="submit" class="btn btn-transfer">→ Transfer Employee</button>
    </div>
  </form>
  <?php endif; ?>
</div>

<script>
function doSearch() {
  const q = document.getElementById('searchInput').value.trim();
  if (q) location.href = 'employee-transfer.php?search=1&q=' + encodeURIComponent(q);
}

let timer;
document.getElementById('searchInput').addEventListener('input', function() {
  clearTimeout(timer);
  const q = this.value.trim();
  const box = document.getElementById('suggestions');
  if (q.length < 1) { box.classList.remove('show'); return; }
  timer = setTimeout(() => {
    fetch('employee-transfer.php?ajax_search=' + encodeURIComponent(q))
      .then(r => r.json())
      .then(data => {
        if (!data.length) { box.classList.remove('show'); return; }
        box.innerHTML = data.map(d =>
          '<div onclick="location.href=\'employee-transfer.php?id='+d.id+'\'">'+d.label+'</div>'
        ).join('');
        box.classList.add('show');
      });
  }, 250);
});

document.getElementById('searchInput').addEventListener('keydown', e => {
  if (e.key === 'Enter') { e.preventDefault(); doSearch(); }
});

document.addEventListener('click', e => {
  if (!e.target.closest('.search-wrap')) {
    document.getElementById('suggestions').classList.remove('show');
  }
});
</script>
</body>
</html>