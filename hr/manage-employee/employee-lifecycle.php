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

// Seed sample
$chk = $conn->query("SELECT COUNT(*) as c FROM employees");
$row = $chk ? $chk->fetch_assoc() : ['c' => 0];
if ((int)$row['c'] === 0) {
    $conn->query("INSERT INTO employees (emp_no, gender, title, initials, first_name, middle_name, last_name, name_with_initials, full_name, office_use_name, language, marital_status, nationality, race, religion, nic, blood_group, company_name, is_active_employee, salary_process, basic_salary, country, office_email, designation, job_level, join_date, division_name, sub_division_name, ppf_epf_no, work_category, job_category, base_customer_site) VALUES ('91', 'Male', 'Mr', 'H M T N', 'Theekshana', 'Nipun', 'Jayawardhana', 'H M T N Jayawardhana', 'H M Theekshana Nipun Jayawardhana', 'Theekshana Jayawardhana', 'Sinhala', 'Single', 'Sri Lankan', 'Sinhalese', 'Buddhist', '200333900373', 'O+', 'Sipway Campus Pvt Ltd', 1, 1, 75000.00, 'Sri Lanka', 'theekshana@sipway.lk', 'IT Assistant', 'Non Executive', '2026-07-01', 'IT', 'None', '47', 'Permanent', 'Staff', '001 | Head Office')");
}

$emp = null;

// AJAX search
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

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $res = $conn->query("SELECT * FROM employees WHERE id = $id");
    if ($res && $res->num_rows > 0) $emp = $res->fetch_assoc();
} elseif (isset($_GET['q']) && trim($_GET['q']) !== '') {
    $q = $conn->real_escape_string(trim($_GET['q']));
    $res = $conn->query("SELECT * FROM employees WHERE emp_no LIKE '%$q%' OR full_name LIKE '%$q%' OR name_with_initials LIKE '%$q%' LIMIT 1");
    if ($res && $res->num_rows > 0) $emp = $res->fetch_assoc();
}

function v($emp, $key, $default = '') {
    return ($emp && isset($emp[$key]) && $emp[$key] !== null && $emp[$key] !== '') ? htmlspecialchars($emp[$key]) : $default;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sipway Campus - Employee History Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #ffffff; min-height: 100vh; }

        .navbar {
            background: #1e293b; height: 50px; display: flex; align-items: center;
            padding: 0 16px; color: white; position: sticky; top: 0; z-index: 100;
        }
        .nav-left { display: flex; align-items: center; gap: 12px; }
        .nav-logo {
            font-weight: 700; font-size: 13px; display: flex; align-items: center;
            gap: 6px; color: white; text-decoration: none;
        }
        .nav-logo .icon {
            width: 20px; height: 20px; background: linear-gradient(135deg,#3b82f6,#8b5cf6);
            border-radius: 3px; display: flex; align-items: center; justify-content: center; font-size: 10px;
        }
        .nav-btn {
            background: transparent; border: none; color: #cbd5e1; cursor: pointer;
            padding: 5px; border-radius: 4px;
        }
        .nav-btn:hover { background: rgba(255,255,255,0.1); color: white; }
        .nav-btn svg { width: 16px; height: 16px; }
        .search-nav { flex: 1; max-width: 240px; margin: 0 16px; }
        .search-nav input {
            width: 100%; padding: 6px 12px; border-radius: 16px; border: none;
            background: rgba(255,255,255,0.12); color: white; font-size: 12px;
            outline: none; font-family: inherit;
        }
        .nav-right { margin-left: auto; display: flex; align-items: center; gap: 10px; font-size: 12px; }

        .main { padding: 28px 40px; max-width: 1000px; margin: 0 auto; }
        .page-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 24px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;
        }
        .page-title { font-size: 20px; font-weight: 600; color: #334155; }
        .btn-print {
            padding: 10px 20px; background: #e2e8f0; color: #475569; border: none;
            border-radius: 8px; font-size: 13px; font-weight: 500; cursor: pointer;
            font-family: inherit; transition: 0.2s;
        }
        .btn-print:hover { background: #cbd5e1; }

        .search-wrap {
            position: relative; max-width: 480px; margin-bottom: 30px;
        }
        .search-wrap input {
            width: 100%; padding: 12px 44px 12px 16px; border: 1px solid #cbd5e1;
            border-radius: 8px; font-size: 14px; font-family: inherit; outline: none;
            color: #3b82f6;
        }
        .search-wrap input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.12); }
        .search-wrap .s-icon {
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            color: #3b82f6; font-size: 16px; pointer-events: none;
        }
        .suggestions {
            position: absolute; top: 100%; left: 0; right: 0; background: white;
            border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            z-index: 50; display: none; max-height: 200px; overflow-y: auto;
        }
        .suggestions.show { display: block; }
        .suggestions div {
            padding: 10px 14px; cursor: pointer; font-size: 13px; border-bottom: 1px solid #f1f5f9;
        }
        .suggestions div:hover { background: #eff6ff; }

        .profile-card {
            display: flex; align-items: flex-start; gap: 32px; padding: 10px 0;
        }
        .avatar {
            width: 120px; height: 120px; border-radius: 50%; background: #dbeafe;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .avatar svg { width: 80px; height: 80px; }

        .details-table { flex: 1; }
        .details-table table { border-collapse: collapse; }
        .details-table td {
            padding: 8px 0; font-size: 14px; vertical-align: top;
        }
        .details-table td:first-child {
            color: #64748b; font-weight: 500; min-width: 140px; padding-right: 24px;
        }
        .details-table td:last-child { color: #1e293b; }

        .empty-state {
            text-align: center; padding: 60px 20px; color: #94a3b8; font-size: 14px;
        }

        @media print {
            .navbar, .btn-print, .search-wrap { display: none !important; }
            .main { padding: 0; }
        }

        @media (max-width: 600px) {
            .main { padding: 16px; }
            .profile-card { flex-direction: column; align-items: center; text-align: center; }
            .details-table td:first-child { min-width: 110px; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-left">
        <a href="../dashboard.php" class="nav-logo"><div class="icon">S</div> SIPWAY CAMPUS</a>
        <button class="nav-btn" onclick="history.back()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        </button>
        <button class="nav-btn" onclick="location.href='../dashboard.php'">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        </button>
    </div>
    <div class="search-nav"><input type="text" placeholder=""></div>
    <div class="nav-right">
        <span>Welcome <strong>Admin</strong></span>
        <button class="nav-btn" onclick="location.href='../index.php'">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
        </button>
    </div>
</nav>

<div class="main">
    <div class="page-header">
        <h1 class="page-title">Employee History Details</h1>
        <button class="btn-print" onclick="window.print()">Print Page</button>
    </div>

    <div class="search-wrap">
        <input type="text" id="searchInput"
               value="<?= $emp ? (v($emp,'emp_no') . ' | ' . (v($emp,'full_name') ?: v($emp,'name_with_initials'))) : '' ?>"
               placeholder="Search employee by number or name..." autocomplete="off">
        <span class="s-icon">🔍</span>
        <div class="suggestions" id="suggestions"></div>
    </div>

    <?php if ($emp): ?>
    <div class="profile-card">
        <div class="avatar">
            <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="50" cy="50" r="50" fill="#93c5fd"/>
                <ellipse cx="50" cy="38" rx="18" ry="20" fill="#1e3a5f"/>
                <path d="M22 88c0-16 12-26 28-26s28 10 28 26" fill="#1e40af"/>
                <rect x="42" y="58" width="16" height="22" rx="2" fill="#dc2626"/>
                <path d="M38 55h24v4H38z" fill="#1e3a5f"/>
            </svg>
        </div>
        <div class="details-table">
            <table>
                <tr>
                    <td>Employee Name</td>
                    <td><?= v($emp,'emp_no') ?> | <?= v($emp,'full_name') ?: v($emp,'name_with_initials') ?></td>
                </tr>
                <tr>
                    <td>Company</td>
                    <td><?= v($emp,'company_name') ?></td>
                </tr>
                <tr>
                    <td>Designation</td>
                    <td><?= v($emp,'designation') ?></td>
                </tr>
                <tr>
                    <td>Work Category</td>
                    <td><?= v($emp,'work_category') ?></td>
                </tr>
                <tr>
                    <td>Job Category</td>
                    <td><?= v($emp,'job_category') ?></td>
                </tr>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="empty-state">
        Search an employee above to view history details.
    </div>
    <?php endif; ?>
</div>

<script>
let timer;
const input = document.getElementById('searchInput');
const box = document.getElementById('suggestions');

input.addEventListener('input', function() {
    clearTimeout(timer);
    const q = this.value.trim();
    // strip "91 | Name" format for search
    const searchQ = q.includes('|') ? q.split('|')[0].trim() : q;
    if (searchQ.length < 1) { box.classList.remove('show'); return; }
    timer = setTimeout(() => {
        fetch('employee-lifecycle.php?ajax_search=' + encodeURIComponent(searchQ))
            .then(r => r.json())
            .then(data => {
                if (!data.length) { box.classList.remove('show'); return; }
                box.innerHTML = data.map(d =>
                    '<div onclick="location.href=\'employee-lifecycle.php?id='+d.id+'\'">'+d.label+'</div>'
                ).join('');
                box.classList.add('show');
            });
    }, 250);
});

input.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const q = this.value.trim();
        const searchQ = q.includes('|') ? q.split('|')[0].trim() : q;
        if (searchQ) location.href = 'employee-lifecycle.php?q=' + encodeURIComponent(searchQ);
    }
});

document.addEventListener('click', e => {
    if (!e.target.closest('.search-wrap')) box.classList.remove('show');
});
</script>
</body>
</html>
