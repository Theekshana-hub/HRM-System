<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

$conn = getDB();
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

$chk = $conn->query("SELECT COUNT(*) as c FROM employees");
$row = $chk->fetch_assoc();
if ($row['c'] == 0) {
    $conn->query("INSERT INTO employees (emp_no, gender, title, initials, first_name, middle_name, last_name, name_with_initials, full_name, office_use_name, language, marital_status, nationality, race, religion, nic, blood_group, company_name, is_active_employee, salary_process, basic_salary, country, office_email, designation, job_level, join_date) VALUES ('91', 'Male', 'Mr', 'H M T N', 'Theekshana', 'Nipun', 'Jayawardhana', 'H M T N Jayawardhana', 'H M Theekshana Nipun Jayawardhana', 'Theekshana Jayawardhana', 'Sinhala', 'Single', 'Sri Lankan', 'Sinhalese', 'Buddhist', '199912345678', 'O+', 'Sipway Campus', 1, 1, 75000.00, 'Sri Lanka', 'theekshana@sipway.lk', 'Software Developer', 'Executive', '2023-01-15')");
}

$message = '';
$msgType = '';
$emp = [];

if (isset($_GET['search']) && !empty(trim($_GET['q']))) {
    $q = $conn->real_escape_string(trim($_GET['q']));
    $res = $conn->query("SELECT * FROM employees WHERE emp_no LIKE '%$q%' OR full_name LIKE '%$q%' OR name_with_initials LIKE '%$q%' OR nic LIKE '%$q%' LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $emp = $res->fetch_assoc();
        $message = "Employee found: " . htmlspecialchars($emp['full_name']);
        $msgType = 'success';
    } else {
        $message = "No employee found for: " . htmlspecialchars($_GET['q']);
        $msgType = 'error';
    }
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $res = $conn->query("SELECT * FROM employees WHERE id = $id");
    if ($res && $res->num_rows > 0) $emp = $res->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $fields = [
        'emp_no','gender','title','initials','denoted_by_initial','first_name','middle_name','last_name',
        'name_with_initials','full_name','office_use_name','language','passport_no','marital_status',
        'date_of_birth','nationality','age','race','birth_place','religion','nic','blood_group',
        'emp_id_issue_date','distance_from_workplace',
        'employee_office_id','tax_payee_no','company_name','proximity_card_no','job_source','division_name',
        'attendance_account_no','manpower_company','sub_division_name','department_name','ppf_epf_no',
        'salary_period','sub_department_name','employee_fund','basic_salary','section_name','etf_no',
        'statute_name','sub_section_name','next_salary_increment_date','base_customer_site','erp_code',
        'address_no','contact_type','postal_code','work_tel','city','country','office_email','district',
        'tel_no','private_email','province','mobile_no','address_comments',
        'designation','resign_date','reporting_supervisor','join_date','leaving_type','job_level',
        'date_of_appointment','retirement_date','rejoin_date','work_category','grade','date_of_confirmation',
        'job_category','type_of_labor_cost','contract_period_months','probation_period_months',
        'job_comment','job_specification','job_description',
        'attendance_group','att_from_date','att_to_date','employee_work_schedule'
    ];

    $data = [];
    foreach ($fields as $f) {
        $data[$f] = isset($_POST[$f]) ? trim($_POST[$f]) : null;
        if ($data[$f] === '') $data[$f] = null;
    }

    $data['is_active_employee'] = isset($_POST['is_active_employee']) ? 1 : 0;
    $data['salary_process'] = isset($_POST['salary_process']) ? 1 : 0;
    $data['is_tax_payee'] = isset($_POST['is_tax_payee']) ? 1 : 0;
    $data['is_permanent'] = isset($_POST['is_permanent']) ? 1 : 0;
    $data['check_by_timezone'] = isset($_POST['check_by_timezone']) ? 1 : 0;
    $data['allow_temporary_shifts'] = isset($_POST['allow_temporary_shifts']) ? 1 : 0;
    $data['checkin_is_must'] = isset($_POST['checkin_is_must']) ? 1 : 0;
    $data['allow_flexy_attendance'] = isset($_POST['allow_flexy_attendance']) ? 1 : 0;
    $data['checkout_is_must'] = isset($_POST['checkout_is_must']) ? 1 : 0;
    $data['ot_applicable'] = isset($_POST['ot_applicable']) ? 1 : 0;

    if (empty($data['full_name']) && $data['first_name']) {
        $data['full_name'] = trim(($data['first_name'] ?? '') . ' ' . ($data['middle_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
    }

    $emp_id = isset($_POST['emp_id']) ? (int)$_POST['emp_id'] : 0;

    if ($emp_id > 0) {
        $sets = [];
        foreach ($data as $k => $v) {
            if ($v === null) $sets[] = "`$k`=NULL";
            else $sets[] = "`$k`='" . $conn->real_escape_string($v) . "'";
        }
        $sql = "UPDATE employees SET " . implode(',', $sets) . " WHERE id=$emp_id";
        if ($conn->query($sql)) {
            $message = "Employee updated successfully!";
            $msgType = 'success';
            $res = $conn->query("SELECT * FROM employees WHERE id=$emp_id");
            $emp = $res->fetch_assoc();
        } else {
            $message = "Update failed: " . $conn->error;
            $msgType = 'error';
        }
    } else {
        if (empty($data['emp_no'])) {
            $message = "Emp No is required!";
            $msgType = 'error';
        } else {
            $cols = array_keys($data);
            $vals = [];
            foreach ($data as $v) {
                $vals[] = $v === null ? "NULL" : "'" . $conn->real_escape_string($v) . "'";
            }
            $sql = "INSERT INTO employees (`" . implode('`,`', $cols) . "`) VALUES (" . implode(',', $vals) . ")";
            if ($conn->query($sql)) {
                $newId = $conn->insert_id;
                $message = "Employee saved successfully! Emp No: " . htmlspecialchars($data['emp_no']);
                $msgType = 'success';
                $res = $conn->query("SELECT * FROM employees WHERE id=$newId");
                $emp = $res->fetch_assoc();
            } else {
                $message = "Save failed: " . $conn->error;
                $msgType = 'error';
            }
        }
    }
}

if (isset($_GET['ajax_search'])) {
    header('Content-Type: application/json');
    $q = $conn->real_escape_string(trim($_GET['ajax_search']));
    $res = $conn->query("SELECT id, emp_no, full_name, name_with_initials FROM employees WHERE emp_no LIKE '%$q%' OR full_name LIKE '%$q%' OR name_with_initials LIKE '%$q%' LIMIT 10");
    $list = [];
    while ($r = $res->fetch_assoc()) {
        $list[] = [
            'id' => $r['id'],
            'label' => $r['emp_no'] . ' | ' . ($r['full_name'] ?: $r['name_with_initials'])
        ];
    }
    echo json_encode($list);
    exit;
}

function v($emp, $key, $default = '') {
    return isset($emp[$key]) && $emp[$key] !== null ? htmlspecialchars($emp[$key]) : $default;
}
function checked($emp, $key) {
    return (isset($emp[$key]) && $emp[$key]) ? 'checked' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sipway Campus - Employee Profile</title>
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

  /* Navbar */
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
  }

  .nav-btn {
    background: transparent; border: none; color: #94a3b8;
    cursor: pointer; padding: 8px; border-radius: 10px;
    transition: all 0.25s;
  }
  .nav-btn:hover { background: rgba(255,255,255,0.1); color: white; }
  .nav-btn svg { width: 18px; height: 18px; }

  .search-nav { flex: 1; max-width: 280px; margin: 0 20px; }
  .search-nav input {
    width: 100%; padding: 9px 16px; border-radius: 50px;
    border: 1px solid rgba(255,255,255,0.1);
    background: rgba(255,255,255,0.08); color: white;
    font-size: 13.5px; outline: none; font-family: inherit;
  }
  .search-nav input::placeholder { color: #64748b; }

  .nav-right {
    margin-left: auto; display: flex; align-items: center;
    gap: 14px; font-size: 13.5px; color: #cbd5e1;
  }
  .nav-right strong { color: white; }

  /* Main */
  .main {
    padding: 28px 32px 50px;
    max-width: 1100px;
    margin: 0 auto;
  }

  .page-title {
    font-size: 24px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 20px;
    letter-spacing: -0.3px;
  }

  /* Toolbar */
  .toolbar {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
  }

  .search-wrap {
    position: relative;
    flex: 1;
    max-width: 420px;
  }

  .search-wrap input {
    width: 100%;
    padding: 12px 44px 12px 16px;
    border: 1.5px solid var(--border);
    border-radius: 12px;
    font-size: 14px;
    font-family: inherit;
    outline: none;
    background: white;
    transition: border-color 0.25s;
  }
  .search-wrap input:focus { border-color: var(--primary); }

  .search-wrap .s-icon {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--primary);
    font-size: 16px;
  }

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
    max-height: 240px;
    overflow-y: auto;
  }
  .suggestions.show { display: block; }
  .suggestions div {
    padding: 12px 16px;
    cursor: pointer;
    font-size: 13.5px;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.15s;
  }
  .suggestions div:hover { background: #f0f9ff; }

  .btn {
    padding: 11px 20px;
    border: none;
    border-radius: 11px;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: white;
    transition: all 0.25s;
  }

  .btn-search {
    background: linear-gradient(135deg, #0ea5e9, #0284c7);
    box-shadow: 0 4px 12px rgba(14,165,233,0.3);
  }
  .btn-search:hover { transform: translateY(-2px); }

  .btn-save {
    background: linear-gradient(135deg, #1e40af, #1e3a8a);
    box-shadow: 0 4px 12px rgba(30,64,175,0.3);
  }
  .btn-save:hover { transform: translateY(-2px); }

  .btn-new {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    box-shadow: 0 4px 12px rgba(34,197,94,0.3);
  }
  .btn-new:hover { transform: translateY(-2px); }

  /* Photo */
  .photo-box {
    background: white;
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 22px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 24px;
    box-shadow: 0 4px 16px rgba(14,165,233,0.06);
  }

  .photo-preview {
    width: 110px; height: 110px;
    border-radius: 50%;
    background: #e2e8f0;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
    border: 4px solid #e0f2fe;
    flex-shrink: 0;
  }
  .photo-preview svg { width: 56px; height: 56px; fill: #94a3b8; }
  .photo-preview img { width: 100%; height: 100%; object-fit: cover; }

  /* Alerts */
  .alert {
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 16px;
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

  /* Accordion */
  .accordion { margin-bottom: 12px; }

  .acc-header {
    background: linear-gradient(135deg, #0f172a, #1e293b);
    color: white;
    padding: 14px 20px;
    border-radius: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14.5px;
    font-weight: 600;
    user-select: none;
    transition: all 0.25s;
    box-shadow: 0 4px 12px rgba(15,23,42,0.15);
  }
  .acc-header:hover { transform: translateY(-1px); }

  .accordion:nth-child(even) .acc-header {
    background: linear-gradient(135deg, #0284c7, #0ea5e9);
  }

  .acc-header .arrow {
    transition: transform 0.3s;
    font-size: 11px;
  }
  .acc-header.open .arrow { transform: rotate(90deg); }

  .acc-body {
    display: none;
    background: white;
    border: 1px solid var(--border);
    border-top: none;
    border-radius: 0 0 14px 14px;
    padding: 24px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.04);
  }
  .acc-body.open { display: block; }

  .section-label {
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 18px;
    padding-bottom: 8px;
    border-bottom: 2px solid #e0f2fe;
  }

  .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px 22px;
  }
  .form-grid.three {
    grid-template-columns: 1fr 1fr 1fr;
  }

  .form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
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
    padding: 10px 14px;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    font-size: 13.5px;
    font-family: inherit;
    outline: none;
    background: #f8fafc;
    transition: all 0.25s;
  }
  .form-group input:focus,
  .form-group select:focus,
  .form-group textarea:focus {
    border-color: var(--primary);
    background: #f0f9ff;
  }

  .form-group input.required-field,
  .form-group select.required-field {
    background: #eff6ff;
  }

  .form-group textarea {
    min-height: 80px;
    resize: vertical;
  }

  /* Toggle */
  .toggle-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 4px;
  }

  .toggle {
    width: 46px;
    height: 24px;
    background: #ef4444;
    border-radius: 12px;
    position: relative;
    cursor: pointer;
    transition: 0.25s;
  }
  .toggle.on { background: #22c55e; }
  .toggle::after {
    content: '';
    width: 20px;
    height: 20px;
    background: white;
    border-radius: 50%;
    position: absolute;
    top: 2px;
    left: 2px;
    transition: 0.25s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
  }
  .toggle.on::after { left: 24px; }
  .toggle input { display: none; }

  .day-row {
    display: grid;
    grid-template-columns: 90px 1fr 1fr 1fr 1fr;
    gap: 10px;
    align-items: center;
    margin-bottom: 8px;
  }
  .day-row span {
    font-size: 13px;
    color: var(--text-muted);
    font-weight: 500;
  }
  .day-row select {
    padding: 7px 10px;
    font-size: 12.5px;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    background: #f8fafc;
  }

  @media (max-width: 800px) {
    .form-grid, .form-grid.three { grid-template-columns: 1fr; }
    .main { padding: 20px 16px 40px; }
    .day-row { grid-template-columns: 70px 1fr 1fr; }
    .photo-box { flex-direction: column; text-align: center; }
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
  <h1 class="page-title">Employee Profile</h1>

  <?php if ($message): ?>
  <div class="alert alert-<?= $msgType ?>"><?= $message ?></div>
  <?php endif; ?>

  <form method="POST" id="empForm" enctype="multipart/form-data">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="emp_id" value="<?= v($emp, 'id') ?>">

    <div class="toolbar">
      <div class="search-wrap">
        <input type="text" id="searchInput" placeholder="Search by Emp No or Name..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>" autocomplete="off">
        <span class="s-icon">🔍</span>
        <div class="suggestions" id="suggestions"></div>
      </div>
      <button type="button" class="btn btn-search" onclick="doSearch()">Search</button>
      <button type="submit" class="btn btn-save">Save</button>
      <button type="button" class="btn btn-new" onclick="location.href='employee-add-edit.php'">New</button>
    </div>

    <div class="photo-box">
      <div class="photo-preview" id="photoPreview">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/></svg>
      </div>
      <div>
        <input type="file" name="photo" accept="image/*" onchange="previewPhoto(this)">
      </div>
    </div>

    <!-- EMPLOYEE MASTER -->
    <div class="accordion">
      <div class="acc-header open" onclick="toggleAcc(this)">
        <span class="arrow">▶</span> Employee Master
      </div>
      <div class="acc-body open">
        <div class="section-label">Personal Details</div>
        <div class="form-grid">
          <div class="form-group">
            <label>Emp No <span class="req">*</span></label>
            <input type="text" name="emp_no" class="required-field" value="<?= v($emp,'emp_no') ?>" required>
          </div>
          <div class="form-group">
            <label>Gender <span class="req">*</span></label>
            <select name="gender" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['Male','Female','Other'] as $g): ?>
              <option value="<?= $g ?>" <?= v($emp,'gender')===$g?'selected':'' ?>><?= $g ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Title <span class="req">*</span></label>
            <select name="title" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['Mr','Mrs','Ms','Miss','Dr','Prof'] as $t): ?>
              <option value="<?= $t ?>" <?= v($emp,'title')===$t?'selected':'' ?>><?= $t ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Initials <span class="req">*</span></label>
            <input type="text" name="initials" class="required-field" value="<?= v($emp,'initials') ?>">
          </div>
          <div class="form-group full">
            <label>Denoted by Initial</label>
            <input type="text" name="denoted_by_initial" value="<?= v($emp,'denoted_by_initial') ?>">
          </div>
          <div class="form-group full">
            <label>First Name</label>
            <input type="text" name="first_name" value="<?= v($emp,'first_name') ?>">
          </div>
          <div class="form-group full">
            <label>Middle Name</label>
            <input type="text" name="middle_name" value="<?= v($emp,'middle_name') ?>">
          </div>
          <div class="form-group full">
            <label>Last Name <span class="req">*</span></label>
            <input type="text" name="last_name" class="required-field" value="<?= v($emp,'last_name') ?>">
          </div>
          <div class="form-group full">
            <label>Name With Initials <span class="req">*</span></label>
            <input type="text" name="name_with_initials" class="required-field" value="<?= v($emp,'name_with_initials') ?>">
          </div>
          <div class="form-group full">
            <label>Full Name</label>
            <input type="text" name="full_name" value="<?= v($emp,'full_name') ?>">
          </div>
          <div class="form-group">
            <label>Office Use Name <span class="req">*</span></label>
            <input type="text" name="office_use_name" class="required-field" value="<?= v($emp,'office_use_name') ?>">
          </div>
          <div class="form-group">
            <label>Language <span class="req">*</span></label>
            <select name="language" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['Sinhala','Tamil','English'] as $l): ?>
              <option value="<?= $l ?>" <?= v($emp,'language')===$l?'selected':'' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Passport No</label>
            <input type="text" name="passport_no" value="<?= v($emp,'passport_no') ?>">
          </div>
          <div class="form-group">
            <label>Marital Status <span class="req">*</span></label>
            <select name="marital_status" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['Single','Married','Divorced','Widowed'] as $m): ?>
              <option value="<?= $m ?>" <?= v($emp,'marital_status')===$m?'selected':'' ?>><?= $m ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Date Of Birth <span class="req">*</span></label>
            <input type="date" name="date_of_birth" class="required-field" value="<?= v($emp,'date_of_birth') ?>">
          </div>
          <div class="form-group">
            <label>Nationality <span class="req">*</span></label>
            <select name="nationality" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['Sri Lankan','Indian','Other'] as $n): ?>
              <option value="<?= $n ?>" <?= v($emp,'nationality')===$n?'selected':'' ?>><?= $n ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Age</label>
            <input type="number" name="age" value="<?= v($emp,'age') ?>">
          </div>
          <div class="form-group">
            <label>Race <span class="req">*</span></label>
            <select name="race" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['Sinhalese','Tamil','Muslim','Burgher','Other'] as $r): ?>
              <option value="<?= $r ?>" <?= v($emp,'race')===$r?'selected':'' ?>><?= $r ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Birth Place</label>
            <input type="text" name="birth_place" value="<?= v($emp,'birth_place') ?>">
          </div>
          <div class="form-group">
            <label>Religion <span class="req">*</span></label>
            <select name="religion" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['Buddhist','Hindu','Islam','Christian','Other'] as $r): ?>
              <option value="<?= $r ?>" <?= v($emp,'religion')===$r?'selected':'' ?>><?= $r ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>NIC <span class="req">*</span></label>
            <input type="text" name="nic" class="required-field" value="<?= v($emp,'nic') ?>">
          </div>
          <div class="form-group">
            <label>Blood Group <span class="req">*</span></label>
            <select name="blood_group" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $b): ?>
              <option value="<?= $b ?>" <?= v($emp,'blood_group')===$b?'selected':'' ?>><?= $b ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Employee ID Issue Date</label>
            <input type="date" name="emp_id_issue_date" value="<?= v($emp,'emp_id_issue_date') ?>">
          </div>
          <div class="form-group">
            <label>Distance From Work Place <span class="req">*</span></label>
            <select name="distance_from_workplace" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['0-5 km','5-10 km','10-20 km','20-50 km','50+ km'] as $d): ?>
              <option value="<?= $d ?>" <?= v($emp,'distance_from_workplace')===$d?'selected':'' ?>><?= $d ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- OFFICIAL DETAILS -->
    <div class="accordion">
      <div class="acc-header" onclick="toggleAcc(this)">
        <span class="arrow">▶</span> Official Details
      </div>
      <div class="acc-body">
        <div class="section-label">Official Details</div>
        <div class="form-grid three">
          <div class="form-group">
            <label>Employee Office ID No <span class="req">*</span></label>
            <input type="text" name="employee_office_id" class="required-field" value="<?= v($emp,'employee_office_id') ?>">
          </div>
          <div class="form-group">
            <label>Tax Payee No</label>
            <input type="text" name="tax_payee_no" value="<?= v($emp,'tax_payee_no') ?>">
          </div>
          <div class="form-group">
            <label>Company Name <span class="req">*</span></label>
            <select name="company_name" class="required-field">
              <option value="">-select-</option>
              <option value="Sipway Campus" <?= v($emp,'company_name')==='Sipway Campus'?'selected':'' ?>>Sipway Campus</option>
            </select>
          </div>
          <div class="form-group">
            <label>Proximity Card No <span class="req">*</span></label>
            <input type="text" name="proximity_card_no" class="required-field" value="<?= v($emp,'proximity_card_no') ?>">
          </div>
          <div class="form-group">
            <label>Job Source <span class="req">*</span></label>
            <select name="job_source" class="required-field">
              <?php foreach(['Interviews','Referral','Walk-in','Online','Other'] as $j): ?>
              <option value="<?= $j ?>" <?= v($emp,'job_source',$j)===$j?'selected':'' ?>><?= $j ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Division Name <span class="req">*</span></label>
            <select name="division_name" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['IT','HR','Finance','Operations','Academic'] as $d): ?>
              <option value="<?= $d ?>" <?= v($emp,'division_name')===$d?'selected':'' ?>><?= $d ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Attendance Account No <span class="req">*</span></label>
            <input type="text" name="attendance_account_no" class="required-field" value="<?= v($emp,'attendance_account_no') ?>">
          </div>
          <div class="form-group">
            <label>Manpower Company <span class="req">*</span></label>
            <select name="manpower_company" class="required-field">
              <?php foreach(['None','External Agency'] as $m): ?>
              <option value="<?= $m ?>" <?= v($emp,'manpower_company','None')===$m?'selected':'' ?>><?= $m ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Sub Division Name <span class="req">*</span></label>
            <select name="sub_division_name" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['Development','Support','Admin'] as $s): ?>
              <option value="<?= $s ?>" <?= v($emp,'sub_division_name')===$s?'selected':'' ?>><?= $s ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Is Active Employee <span class="req">*</span></label>
            <div class="toggle-row">
              <div class="toggle <?= checked($emp,'is_active_employee')?'on':'' ?>" onclick="this.classList.toggle('on'); this.querySelector('input').checked=this.classList.contains('on')">
                <input type="checkbox" name="is_active_employee" <?= checked($emp,'is_active_employee') ?: 'checked' ?>>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Salary Process <span class="req">*</span></label>
            <div class="toggle-row">
              <div class="toggle <?= checked($emp,'salary_process')?'on':'' ?>" onclick="this.classList.toggle('on'); this.querySelector('input').checked=this.classList.contains('on')">
                <input type="checkbox" name="salary_process" <?= checked($emp,'salary_process') ?: 'checked' ?>>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Department Name <span class="req">*</span></label>
            <select name="department_name" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['IT Department','HR Department','Finance Department','Academic Department'] as $d): ?>
              <option value="<?= $d ?>" <?= v($emp,'department_name')===$d?'selected':'' ?>><?= $d ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>PPF/EPF No <span class="req">*</span></label>
            <input type="text" name="ppf_epf_no" class="required-field" value="<?= v($emp,'ppf_epf_no') ?>">
          </div>
          <div class="form-group">
            <label>Salary Period <span class="req">*</span></label>
            <select name="salary_period" class="required-field">
              <?php foreach(['Monthly','Weekly','Daily'] as $s): ?>
              <option value="<?= $s ?>" <?= v($emp,'salary_period','Monthly')===$s?'selected':'' ?>><?= $s ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Sub Department Name <span class="req">*</span></label>
            <select name="sub_department_name" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['Software','Hardware','General'] as $s): ?>
              <option value="<?= $s ?>" <?= v($emp,'sub_department_name')===$s?'selected':'' ?>><?= $s ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Employee Fund <span class="req">*</span></label>
            <select name="employee_fund" class="required-field">
              <?php foreach(['EPF Applicable','Not Applicable'] as $e): ?>
              <option value="<?= $e ?>" <?= v($emp,'employee_fund','EPF Applicable')===$e?'selected':'' ?>><?= $e ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Basic Salary <span class="req">*</span></label>
            <input type="number" step="0.01" name="basic_salary" class="required-field" value="<?= v($emp,'basic_salary','0') ?>">
          </div>
          <div class="form-group">
            <label>Section Name <span class="req">*</span></label>
            <select name="section_name" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['Section A','Section B','Section C'] as $s): ?>
              <option value="<?= $s ?>" <?= v($emp,'section_name')===$s?'selected':'' ?>><?= $s ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>ETF No <span class="req">*</span></label>
            <input type="text" name="etf_no" class="required-field" value="<?= v($emp,'etf_no') ?>">
          </div>
          <div class="form-group">
            <label>Statute Name <span class="req">*</span></label>
            <select name="statute_name" class="required-field">
              <?php foreach(['Shop & Office','Wages Board','Other'] as $s): ?>
              <option value="<?= $s ?>" <?= v($emp,'statute_name','Shop & Office')===$s?'selected':'' ?>><?= $s ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Sub Section Name <span class="req">*</span></label>
            <select name="sub_section_name" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['Sub A','Sub B'] as $s): ?>
              <option value="<?= $s ?>" <?= v($emp,'sub_section_name')===$s?'selected':'' ?>><?= $s ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Is Tax Payee <span class="req">*</span></label>
            <div class="toggle-row">
              <div class="toggle <?= checked($emp,'is_tax_payee')?'on':'' ?>" onclick="this.classList.toggle('on'); this.querySelector('input').checked=this.classList.contains('on')">
                <input type="checkbox" name="is_tax_payee" <?= checked($emp,'is_tax_payee') ?>>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Next Salary Increment Date</label>
            <input type="date" name="next_salary_increment_date" value="<?= v($emp,'next_salary_increment_date') ?>">
          </div>
          <div class="form-group">
            <label>Base Customer Site</label>
            <input type="text" name="base_customer_site" value="<?= v($emp,'base_customer_site') ?>">
          </div>
          <div class="form-group">
            <label>ERP Code</label>
            <input type="text" name="erp_code" value="<?= v($emp,'erp_code') ?>">
          </div>
        </div>
      </div>
    </div>

    <!-- ADDRESS DETAILS -->
    <div class="accordion">
      <div class="acc-header" onclick="toggleAcc(this)">
        <span class="arrow">▶</span> Address Details
      </div>
      <div class="acc-body">
        <div class="section-label">Address Details</div>
        <div class="form-grid three">
          <div class="form-group full">
            <label>Address No</label>
            <input type="text" name="address_no" value="<?= v($emp,'address_no') ?>">
          </div>
          <div class="form-group">
            <label>Contact Type <span class="req">*</span></label>
            <select name="contact_type" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['Permanent','Temporary','Office'] as $c): ?>
              <option value="<?= $c ?>" <?= v($emp,'contact_type')===$c?'selected':'' ?>><?= $c ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Postal Code / Zip Code</label>
            <input type="text" name="postal_code" value="<?= v($emp,'postal_code') ?>">
          </div>
          <div class="form-group">
            <label>Work Tel</label>
            <input type="text" name="work_tel" value="<?= v($emp,'work_tel') ?>">
          </div>
          <div class="form-group">
            <label>City</label>
            <input type="text" name="city" value="<?= v($emp,'city') ?>">
          </div>
          <div class="form-group">
            <label>Country <span class="req">*</span></label>
            <select name="country" class="required-field">
              <option value="Sri Lanka" <?= v($emp,'country','Sri Lanka')==='Sri Lanka'?'selected':'' ?>>Sri Lanka</option>
              <option value="Other">Other</option>
            </select>
          </div>
          <div class="form-group">
            <label>Office Email <span class="req">*</span></label>
            <input type="email" name="office_email" class="required-field" value="<?= v($emp,'office_email') ?>">
          </div>
          <div class="form-group">
            <label>District <span class="req">*</span></label>
            <select name="district" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['Colombo','Gampaha','Kalutara','Kandy','Galle','Matara','Kurunegala','Anuradhapura','Jaffna','Other'] as $d): ?>
              <option value="<?= $d ?>" <?= v($emp,'district')===$d?'selected':'' ?>><?= $d ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Tel No</label>
            <input type="text" name="tel_no" value="<?= v($emp,'tel_no') ?>">
          </div>
          <div class="form-group">
            <label>Private Email</label>
            <input type="email" name="private_email" value="<?= v($emp,'private_email') ?>">
          </div>
          <div class="form-group">
            <label>Province <span class="req">*</span></label>
            <select name="province" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['Western','Central','Southern','Northern','Eastern','North Western','North Central','Uva','Sabaragamuwa'] as $p): ?>
              <option value="<?= $p ?>" <?= v($emp,'province')===$p?'selected':'' ?>><?= $p ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Mobile No</label>
            <input type="text" name="mobile_no" value="<?= v($emp,'mobile_no') ?>">
          </div>
          <div class="form-group full">
            <label>Comments</label>
            <textarea name="address_comments"><?= v($emp,'address_comments') ?></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- JOB DETAILS -->
    <div class="accordion">
      <div class="acc-header" onclick="toggleAcc(this)">
        <span class="arrow">▶</span> Job Details
      </div>
      <div class="acc-body">
        <div class="section-label">Job Details</div>
        <div class="form-grid three">
          <div class="form-group">
            <label>Designation <span class="req">*</span></label>
            <input type="text" name="designation" class="required-field" value="<?= v($emp,'designation') ?>">
          </div>
          <div class="form-group">
            <label>Resign Date</label>
            <input type="date" name="resign_date" value="<?= v($emp,'resign_date') ?>">
          </div>
          <div class="form-group">
            <label>Reporting Supervisor <span class="req">*</span></label>
            <input type="text" name="reporting_supervisor" class="required-field" value="<?= v($emp,'reporting_supervisor') ?>">
          </div>
          <div class="form-group">
            <label>Join Date <span class="req">*</span></label>
            <input type="date" name="join_date" class="required-field" value="<?= v($emp,'join_date') ?>">
          </div>
          <div class="form-group">
            <label>Leaving Type</label>
            <select name="leaving_type">
              <option value="">-select-</option>
              <?php foreach(['Resignation','Termination','Retirement','End of Contract'] as $l): ?>
              <option value="<?= $l ?>" <?= v($emp,'leaving_type')===$l?'selected':'' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Job Level <span class="req">*</span></label>
            <select name="job_level" class="required-field">
              <?php foreach(['Executive','Non-Executive','Manager','Senior Manager','Director'] as $j): ?>
              <option value="<?= $j ?>" <?= v($emp,'job_level','Executive')===$j?'selected':'' ?>><?= $j ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Date of Appointment / Contract start</label>
            <input type="date" name="date_of_appointment" value="<?= v($emp,'date_of_appointment') ?>">
          </div>
          <div class="form-group">
            <label>Retirement Date</label>
            <input type="date" name="retirement_date" value="<?= v($emp,'retirement_date') ?>">
          </div>
          <div class="form-group">
            <label>Rejoin Date</label>
            <input type="date" name="rejoin_date" value="<?= v($emp,'rejoin_date') ?>">
          </div>
          <div class="form-group">
            <label>Work Category <span class="req">*</span></label>
            <select name="work_category" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['Full Time','Part Time','Contract','Intern'] as $w): ?>
              <option value="<?= $w ?>" <?= v($emp,'work_category')===$w?'selected':'' ?>><?= $w ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Grade</label>
            <select name="grade">
              <option value="">-select-</option>
              <?php foreach(['A','B','C','D','E'] as $g): ?>
              <option value="<?= $g ?>" <?= v($emp,'grade')===$g?'selected':'' ?>><?= $g ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Date of Confirmation / Contract End</label>
            <input type="date" name="date_of_confirmation" value="<?= v($emp,'date_of_confirmation') ?>">
          </div>
          <div class="form-group">
            <label>Job Category <span class="req">*</span></label>
            <select name="job_category" class="required-field">
              <option value="">-select-</option>
              <?php foreach(['Technical','Administrative','Academic','Support'] as $j): ?>
              <option value="<?= $j ?>" <?= v($emp,'job_category')===$j?'selected':'' ?>><?= $j ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Type of Labor Cost</label>
            <select name="type_of_labor_cost">
              <option value="">-select-</option>
              <?php foreach(['Direct','Indirect'] as $t): ?>
              <option value="<?= $t ?>" <?= v($emp,'type_of_labor_cost')===$t?'selected':'' ?>><?= $t ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Contract Period (Months) <span class="req">*</span></label>
            <input type="number" name="contract_period_months" class="required-field" value="<?= v($emp,'contract_period_months') ?>">
          </div>
          <div class="form-group">
            <label>Probation Period (Months) <span class="req">*</span></label>
            <input type="number" name="probation_period_months" class="required-field" value="<?= v($emp,'probation_period_months') ?>">
          </div>
          <div class="form-group full">
            <label>Comment</label>
            <textarea name="job_comment"><?= v($emp,'job_comment') ?></textarea>
          </div>
          <div class="form-group full">
            <label>Job Specification</label>
            <textarea name="job_specification"><?= v($emp,'job_specification') ?></textarea>
          </div>
          <div class="form-group full">
            <label>Job Description</label>
            <textarea name="job_description"><?= v($emp,'job_description') ?></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- ATTENDANCE RULES -->
    <div class="accordion">
      <div class="acc-header" onclick="toggleAcc(this)">
        <span class="arrow">▶</span> Attendance Rules
      </div>
      <div class="acc-body">
        <div class="section-label">Attendance Rules</div>
        <div class="form-grid">
          <div class="form-group full">
            <label>Attendance Group</label>
            <select name="attendance_group">
              <option value="">-select-</option>
              <?php foreach(['General','Shift A','Shift B','Flexible'] as $a): ?>
              <option value="<?= $a ?>" <?= v($emp,'attendance_group')===$a?'selected':'' ?>><?= $a ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>From Date</label>
            <input type="date" name="att_from_date" value="<?= v($emp,'att_from_date') ?>">
          </div>
          <div class="form-group">
            <label>To Date</label>
            <input type="date" name="att_to_date" value="<?= v($emp,'att_to_date') ?>">
          </div>
          <div class="form-group">
            <label>Is Permanent</label>
            <div class="toggle-row">
              <input type="checkbox" name="is_permanent" <?= checked($emp,'is_permanent') ?>>
            </div>
          </div>
          <div class="form-group full">
            <label>Employee Work Schedule</label>
            <select name="employee_work_schedule">
              <option value="">-select-</option>
              <?php foreach(['Standard 8-5','Shift Based','Flexible Hours'] as $w): ?>
              <option value="<?= $w ?>" <?= v($emp,'employee_work_schedule')===$w?'selected':'' ?>><?= $w ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div style="margin-top:18px;">
          <?php
          $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
          foreach ($days as $day): ?>
          <div class="day-row">
            <span><?= $day ?></span>
            <select><option>-select-</option><option>08:00</option><option>09:00</option></select>
            <select><option>-select-</option><option>12:00</option><option>13:00</option></select>
            <select><option>-select-</option><option>13:00</option><option>14:00</option></select>
            <select><option>-select-</option><option>17:00</option><option>18:00</option></select>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="form-grid" style="margin-top:18px;">
          <div class="form-group">
            <label>Check By TimeZone</label>
            <div class="toggle-row">
              <div class="toggle <?= checked($emp,'check_by_timezone')?'on':'' ?>" onclick="this.classList.toggle('on'); this.querySelector('input').checked=this.classList.contains('on')">
                <input type="checkbox" name="check_by_timezone" <?= checked($emp,'check_by_timezone') ?>>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Allow Temporary Shifts</label>
            <div class="toggle-row">
              <div class="toggle <?= checked($emp,'allow_temporary_shifts')?'on':'' ?>" onclick="this.classList.toggle('on'); this.querySelector('input').checked=this.classList.contains('on')">
                <input type="checkbox" name="allow_temporary_shifts" <?= checked($emp,'allow_temporary_shifts') ?>>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>CheckIn Is Must</label>
            <div class="toggle-row">
              <div class="toggle <?= checked($emp,'checkin_is_must')?'on':'' ?>" onclick="this.classList.toggle('on'); this.querySelector('input').checked=this.classList.contains('on')">
                <input type="checkbox" name="checkin_is_must" <?= checked($emp,'checkin_is_must') ?>>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Allow Flexy Attendance</label>
            <div class="toggle-row">
              <div class="toggle <?= checked($emp,'allow_flexy_attendance')?'on':'' ?>" onclick="this.classList.toggle('on'); this.querySelector('input').checked=this.classList.contains('on')">
                <input type="checkbox" name="allow_flexy_attendance" <?= checked($emp,'allow_flexy_attendance') ?>>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>CheckOut Is Must</label>
            <div class="toggle-row">
              <div class="toggle <?= checked($emp,'checkout_is_must')?'on':'' ?>" onclick="this.classList.toggle('on'); this.querySelector('input').checked=this.classList.contains('on')">
                <input type="checkbox" name="checkout_is_must" <?= checked($emp,'checkout_is_must') ?>>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>OT Applicable</label>
            <div class="toggle-row">
              <div class="toggle <?= checked($emp,'ot_applicable')?'on':'' ?>" onclick="this.classList.toggle('on'); this.querySelector('input').checked=this.classList.contains('on')">
                <input type="checkbox" name="ot_applicable" <?= checked($emp,'ot_applicable') ?>>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- REPORTING HIERARCHY -->
    <div class="accordion">
      <div class="acc-header" onclick="toggleAcc(this)">
        <span class="arrow">▶</span> Reporting Hierarchy
      </div>
      <div class="acc-body">
        <div class="section-label">Reporting Hierarchy</div>
        <p style="color:#64748b; font-size:14px; line-height:1.6;">
          Reporting hierarchy configuration can be added here. Currently linked via Reporting Supervisor field in Job Details.
        </p>
      </div>
    </div>

  </form>
</div>

<script>
function toggleAcc(el) {
  el.classList.toggle('open');
  el.nextElementSibling.classList.toggle('open');
}

function previewPhoto(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      document.getElementById('photoPreview').innerHTML = '<img src="'+e.target.result+'">';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function doSearch() {
  const q = document.getElementById('searchInput').value.trim();
  if (q) location.href = 'employee-add-edit.php?search=1&q=' + encodeURIComponent(q);
}

let timer;
document.getElementById('searchInput').addEventListener('input', function() {
  clearTimeout(timer);
  const q = this.value.trim();
  const box = document.getElementById('suggestions');
  if (q.length < 1) { box.classList.remove('show'); return; }
  timer = setTimeout(() => {
    fetch('employee-add-edit.php?ajax_search=' + encodeURIComponent(q))
      .then(r => r.json())
      .then(data => {
        if (data.length === 0) { box.classList.remove('show'); return; }
        box.innerHTML = data.map(d =>
          '<div onclick="location.href=\'employee-add-edit.php?id='+d.id+'\'">'+d.label+'</div>'
        ).join('');
        box.classList.add('show');
      });
  }, 250);
});

document.addEventListener('click', e => {
  if (!e.target.closest('.search-wrap')) {
    document.getElementById('suggestions').classList.remove('show');
  }
});

document.getElementById('searchInput').addEventListener('keydown', e => {
  if (e.key === 'Enter') { e.preventDefault(); doSearch(); }
});
</script>
</body>
</html>