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

$results = [];
$searched = false;
$nic_query = '';

if (isset($_GET['nic']) && trim($_GET['nic']) !== '') {
    $searched = true;
    $nic_query = trim($_GET['nic']);
    $nic = $conn->real_escape_string($nic_query);
    $res = $conn->query("SELECT * FROM employees WHERE nic LIKE '%$nic%' ORDER BY emp_no ASC");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $results[] = $row;
        }
    }
}

function v($row, $key, $default = '') {
    return isset($row[$key]) && $row[$key] !== null && $row[$key] !== '' ? htmlspecialchars($row[$key]) : $default;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sipway Campus - Employee NIC Number Search</title>
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

  .nav-left {
    display: flex;
    align-items: center;
    gap: 14px;
  }

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
    width: 28px;
    height: 28px;
    background: linear-gradient(135deg, #0ea5e9, #8b5cf6);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 800;
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
  .nav-btn:hover {
    background: rgba(255,255,255,0.1);
    color: white;
  }
  .nav-btn svg {
    width: 18px;
    height: 18px;
  }

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
  .search-nav input::placeholder {
    color: #64748b;
  }

  .nav-right {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 14px;
    font-size: 13.5px;
    color: #cbd5e1;
  }
  .nav-right strong {
    color: white;
  }

  /* ========== MAIN ========== */
  .main {
    padding: 32px 36px 50px;
    max-width: 1300px;
    margin: 0 auto;
  }

  .page-title {
    font-size: 24px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 28px;
    letter-spacing: -0.3px;
  }

  /* Search Section */
  .search-card {
    background: white;
    border-radius: 16px;
    padding: 28px 32px;
    box-shadow: 0 6px 20px rgba(14,165,233,0.07);
    border: 1px solid rgba(255,255,255,0.9);
    margin-bottom: 28px;
  }

  .search-row {
    display: flex;
    align-items: center;
    gap: 16px;
    max-width: 640px;
  }

  .search-row label {
    font-size: 14.5px;
    color: var(--text);
    font-weight: 600;
    white-space: nowrap;
    min-width: 110px;
  }

  .search-row input {
    flex: 1;
    padding: 12px 16px;
    border: 1.5px solid var(--border);
    border-radius: 12px;
    font-size: 14.5px;
    font-family: inherit;
    outline: none;
    background: #f8fafc;
    transition: all 0.25s;
  }
  .search-row input:focus {
    border-color: var(--primary);
    background: #f0f9ff;
    box-shadow: 0 0 0 4px rgba(14,165,233,0.12);
  }

  .btn-wrap {
    margin-top: 20px;
  }

  .btn-search {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 28px;
    background: linear-gradient(135deg, #0ea5e9, #0284c7);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 14.5px;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
    box-shadow: 0 4px 14px rgba(14,165,233,0.3);
    transition: all 0.25s;
  }
  .btn-search:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(14,165,233,0.4);
  }

  /* Table */
  .table-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 6px 20px rgba(14,165,233,0.07);
    border: 1px solid rgba(255,255,255,0.9);
  }

  .table-wrap {
    overflow-x: auto;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13.5px;
    min-width: 1100px;
  }

  thead th {
    background: linear-gradient(135deg, #0f172a, #1e293b);
    color: white;
    font-weight: 600;
    padding: 14px 16px;
    text-align: left;
    white-space: nowrap;
    font-size: 12.5px;
    letter-spacing: 0.2px;
  }

  tbody td {
    padding: 13px 16px;
    border-bottom: 1px solid #f1f5f9;
    color: var(--text);
    vertical-align: middle;
  }

  tbody tr:hover {
    background: #f0f9ff;
  }

  tbody tr:last-child td {
    border-bottom: none;
  }

  .record-count {
    padding: 14px 20px;
    background: #f8fafc;
    color: var(--text-muted);
    font-size: 13.5px;
    font-weight: 500;
    border-top: 1px solid var(--border);
  }

  .no-results {
    text-align: center;
    padding: 48px 20px;
    color: #94a3b8;
    font-size: 15px;
  }

  .active-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
  }
  .active-true {
    background: #d1fae5;
    color: #059669;
  }
  .active-false {
    background: #fee2e2;
    color: #dc2626;
  }

  @media (max-width: 700px) {
    .main {
      padding: 20px 16px 40px;
    }
    .search-row {
      flex-direction: column;
      align-items: stretch;
    }
    .search-row label {
      min-width: auto;
    }
    .search-card {
      padding: 20px;
    }
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
  <div class="search-nav">
    <input type="text" placeholder="Search...">
  </div>
  <div class="nav-right">
    <span>Welcome <strong>Admin</strong></span>
    <button class="nav-btn" onclick="location.href='../index.php'" title="Logout">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
    </button>
  </div>
</nav>

<div class="main">
  <h1 class="page-title">Employee NIC Number Search</h1>

  <div class="search-card">
    <form method="GET">
      <div class="search-row">
        <label>NIC Number</label>
        <input type="text" name="nic" value="<?= htmlspecialchars($nic_query) ?>" placeholder="Enter NIC Number..." autofocus>
      </div>
      <div class="btn-wrap">
        <button type="submit" class="btn-search">🔍 Search</button>
      </div>
    </form>
  </div>

  <?php if ($searched): ?>
  <div class="table-card">
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Emp No</th>
            <th>Emp Name</th>
            <th>EPF No</th>
            <th>Site Name</th>
            <th>Designation</th>
            <th>NIC No</th>
            <th>D.O.J</th>
            <th>Company</th>
            <th>Division</th>
            <th>Sub Division</th>
            <th>Is Service Braked</th>
            <th>Service Brake Date</th>
            <th>Is Active</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($results) > 0): ?>
            <?php foreach ($results as $row): ?>
            <tr>
              <td><?= v($row, 'emp_no') ?></td>
              <td><?= v($row, 'full_name') ?: v($row, 'name_with_initials') ?></td>
              <td><?= v($row, 'ppf_epf_no') ?></td>
              <td><?= v($row, 'base_customer_site', '001 | Head Office') ?></td>
              <td><?= v($row, 'designation') ?></td>
              <td><?= v($row, 'nic') ?></td>
              <td>
                <?php
                if (!empty($row['join_date'])) {
                    echo date('n/j/Y', strtotime($row['join_date']));
                }
                ?>
              </td>
              <td><?= v($row, 'company_name') ?></td>
              <td><?= v($row, 'division_name') ?></td>
              <td><?= v($row, 'sub_division_name', 'None') ?></td>
              <td>No</td>
              <td>—</td>
              <td>
                <?php if (!empty($row['is_active_employee'])): ?>
                  <span class="active-badge active-true">True</span>
                <?php else: ?>
                  <span class="active-badge active-false">False</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="13" class="no-results">No employees found for this NIC number.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="record-count">
      <?= count($results) ?> Record<?= count($results) !== 1 ? 's' : '' ?> Found
    </div>
  </div>
  <?php endif; ?>
</div>

</body>
</html>