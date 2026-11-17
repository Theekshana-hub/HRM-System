<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

$conn = getDB();

$conn->query("CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_no VARCHAR(50) UNIQUE, company_name VARCHAR(200) NOT NULL,
    phone_no VARCHAR(50), address TEXT, registration_year VARCHAR(20),
    epf_registration_no VARCHAR(50), tin_no VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS divisions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT, division_no VARCHAR(50), division_name VARCHAR(200) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (company_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS sub_divisions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT, division_id INT,
    sub_division_no VARCHAR(50), sub_division_name VARCHAR(200) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (company_id), INDEX (division_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT, division_id INT, sub_division_id INT,
    department_no VARCHAR(50), department_name VARCHAR(200) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (company_id), INDEX (division_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS sub_departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT, division_id INT, sub_division_id INT, department_id INT,
    sub_department_no VARCHAR(50), sub_department_name VARCHAR(200) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (company_id), INDEX (department_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT, division_id INT, sub_division_id INT, department_id INT, sub_department_id INT,
    section_no VARCHAR(50), section_name VARCHAR(200) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (company_id), INDEX (division_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS sub_sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT, division_id INT, sub_division_id INT, department_id INT,
    sub_department_id INT, section_id INT,
    sub_section_no VARCHAR(50), sub_section_name VARCHAR(200) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (company_id), INDEX (section_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$chk = $conn->query("SELECT COUNT(*) as c FROM companies");
$row = $chk ? $chk->fetch_assoc() : ['c' => 0];
if ((int)$row['c'] === 0) {
    $conn->query("INSERT INTO companies (company_no, company_name, registration_year) VALUES
        ('1', 'Sipway Campus Pvt Ltd', '2025'),
        ('2', 'Sipway Higher Education Pvt Ltd', '2025'),
        ('3', 'Sipway Online School', '2025'),
        ('4', 'Sipway English Academy Pvt Ltd', '2025')");
}

// AJAX
if (isset($_GET['ajax_divisions'])) {
    header('Content-Type: application/json');
    $cid = (int)$_GET['ajax_divisions'];
    $list = [];
    $res = $conn->query("SELECT id, division_name FROM divisions WHERE company_id = $cid ORDER BY division_no ASC");
    while ($r = $res->fetch_assoc()) $list[] = ['id' => $r['id'], 'label' => $r['division_name']];
    echo json_encode($list); exit;
}
if (isset($_GET['ajax_sub_divisions'])) {
    header('Content-Type: application/json');
    $did = (int)$_GET['ajax_sub_divisions'];
    $list = [];
    $res = $conn->query("SELECT id, sub_division_name FROM sub_divisions WHERE division_id = $did ORDER BY sub_division_no ASC");
    while ($r = $res->fetch_assoc()) $list[] = ['id' => $r['id'], 'label' => $r['sub_division_name']];
    echo json_encode($list); exit;
}
if (isset($_GET['ajax_departments'])) {
    header('Content-Type: application/json');
    $did = (int)$_GET['ajax_departments'];
    $list = [];
    $res = $conn->query("SELECT id, department_name FROM departments WHERE division_id = $did ORDER BY department_no ASC");
    while ($r = $res->fetch_assoc()) $list[] = ['id' => $r['id'], 'label' => $r['department_name']];
    echo json_encode($list); exit;
}
if (isset($_GET['ajax_sub_departments'])) {
    header('Content-Type: application/json');
    $depid = (int)$_GET['ajax_sub_departments'];
    $list = [];
    $res = $conn->query("SELECT id, sub_department_name FROM sub_departments WHERE department_id = $depid ORDER BY sub_department_no ASC");
    while ($r = $res->fetch_assoc()) $list[] = ['id' => $r['id'], 'label' => $r['sub_department_name']];
    echo json_encode($list); exit;
}
if (isset($_GET['ajax_sections'])) {
    header('Content-Type: application/json');
    $depid = (int)$_GET['ajax_sections'];
    $list = [];
    $res = $conn->query("SELECT id, section_name FROM sections WHERE department_id = $depid OR (department_id = 0 AND division_id = $depid) ORDER BY section_no ASC");
    if ($res->num_rows == 0) {
        $res = $conn->query("SELECT id, section_name FROM sections WHERE division_id = $depid ORDER BY section_no ASC");
    }
    while ($r = $res->fetch_assoc()) $list[] = ['id' => $r['id'], 'label' => $r['section_name']];
    echo json_encode($list); exit;
}
if (isset($_GET['ajax_sections_by_division'])) {
    header('Content-Type: application/json');
    $did = (int)$_GET['ajax_sections_by_division'];
    $list = [];
    $res = $conn->query("SELECT id, section_name FROM sections WHERE division_id = $did ORDER BY section_no ASC");
    while ($r = $res->fetch_assoc()) $list[] = ['id' => $r['id'], 'label' => $r['section_name']];
    echo json_encode($list); exit;
}

$message = '';
$msgType = '';
$edit = null;

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM sub_sections WHERE id = $id");
    $message = "Sub Section deleted successfully!";
    $msgType = 'success';
}

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM sub_sections WHERE id = $id");
    if ($res && $res->num_rows > 0) $edit = $res->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $company_id        = (int)($_POST['company_id'] ?? 0);
    $division_id       = (int)($_POST['division_id'] ?? 0);
    $sub_division_id   = (int)($_POST['sub_division_id'] ?? 0);
    $department_id     = (int)($_POST['department_id'] ?? 0);
    $sub_department_id = (int)($_POST['sub_department_id'] ?? 0);
    $section_id        = (int)($_POST['section_id'] ?? 0);
    $sub_section_no    = trim($_POST['sub_section_no'] ?? '');
    $sub_section_name  = trim($_POST['sub_section_name'] ?? '');
    $rec_id            = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($company_id <= 0) {
        $message = "Please select a Company!";
        $msgType = 'error';
    } elseif ($sub_section_name === '') {
        $message = "SubSection Name is required!";
        $msgType = 'error';
    } else {
        if ($sub_section_no === '') $sub_section_no = '0';
        if ($rec_id > 0) {
            $stmt = $conn->prepare("UPDATE sub_sections SET company_id=?, division_id=?, sub_division_id=?, department_id=?, sub_department_id=?, section_id=?, sub_section_no=?, sub_section_name=? WHERE id=?");
            $stmt->bind_param("iiiiiissi", $company_id, $division_id, $sub_division_id, $department_id, $sub_department_id, $section_id, $sub_section_no, $sub_section_name, $rec_id);
            if ($stmt->execute()) {
                $message = "Sub Section updated successfully!";
                $msgType = 'success';
                $edit = null;
            } else {
                $message = "Update failed: " . $conn->error;
                $msgType = 'error';
            }
            $stmt->close();
        } else {
            $stmt = $conn->prepare("INSERT INTO sub_sections (company_id, division_id, sub_division_id, department_id, sub_department_id, section_id, sub_section_no, sub_section_name) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->bind_param("iiiiiiss", $company_id, $division_id, $sub_division_id, $department_id, $sub_department_id, $section_id, $sub_section_no, $sub_section_name);
            if ($stmt->execute()) {
                $message = "Sub Section saved successfully!";
                $msgType = 'success';
            } else {
                $message = "Save failed: " . $conn->error;
                $msgType = 'error';
            }
            $stmt->close();
        }
    }
}

// Seed None if empty
$chkSS = $conn->query("SELECT COUNT(*) as c FROM sub_sections");
$rowSS = $chkSS ? $chkSS->fetch_assoc() : ['c' => 0];
if ((int)$rowSS['c'] === 0) {
    $dr = $conn->query("SELECT id, company_id FROM divisions");
    if ($dr) {
        while ($d = $dr->fetch_assoc()) {
            $cid = (int)$d['company_id']; $did = (int)$d['id'];
            $conn->query("INSERT INTO sub_sections (company_id, division_id, sub_division_id, department_id, sub_department_id, section_id, sub_section_no, sub_section_name) VALUES ($cid, $did, 0, 0, 0, 0, '0', 'None')");
        }
    }
}

$companyList = [];
$cr = $conn->query("SELECT id, company_name FROM companies ORDER BY id ASC");
while ($c = $cr->fetch_assoc()) $companyList[] = $c;

$divisionList = []; $subDivisionList = []; $departmentList = []; $subDepartmentList = []; $sectionList = [];
if ($edit && $edit['company_id']) {
    $dr = $conn->query("SELECT id, division_name FROM divisions WHERE company_id = " . (int)$edit['company_id'] . " ORDER BY division_no ASC");
    while ($d = $dr->fetch_assoc()) $divisionList[] = $d;
}
if ($edit && $edit['division_id']) {
    $sr = $conn->query("SELECT id, sub_division_name FROM sub_divisions WHERE division_id = " . (int)$edit['division_id'] . " ORDER BY sub_division_no ASC");
    while ($s = $sr->fetch_assoc()) $subDivisionList[] = $s;
    $dpr = $conn->query("SELECT id, department_name FROM departments WHERE division_id = " . (int)$edit['division_id'] . " ORDER BY department_no ASC");
    while ($dp = $dpr->fetch_assoc()) $departmentList[] = $dp;
    $secr = $conn->query("SELECT id, section_name FROM sections WHERE division_id = " . (int)$edit['division_id'] . " ORDER BY section_no ASC");
    while ($sec = $secr->fetch_assoc()) $sectionList[] = $sec;
}
if ($edit && $edit['department_id']) {
    $sdr = $conn->query("SELECT id, sub_department_name FROM sub_departments WHERE department_id = " . (int)$edit['department_id'] . " ORDER BY sub_department_no ASC");
    while ($sd = $sdr->fetch_assoc()) $subDepartmentList[] = $sd;
}

$subSections = [];
$res = $conn->query("SELECT ss.*, c.company_name, d.division_name, dep.department_name, sec.section_name
    FROM sub_sections ss
    LEFT JOIN companies c ON ss.company_id = c.id
    LEFT JOIN divisions d ON ss.division_id = d.id
    LEFT JOIN departments dep ON ss.department_id = dep.id
    LEFT JOIN sections sec ON ss.section_id = sec.id
    ORDER BY c.id ASC, d.division_no ASC, ss.id ASC");
if ($res) {
    while ($r = $res->fetch_assoc()) $subSections[] = $r;
}

function v($arr, $key, $default = '') {
    return isset($arr[$key]) && $arr[$key] !== null ? htmlspecialchars($arr[$key]) : $default;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sipway Campus - Sub Section Registration</title>
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
        .nav-btn { background: transparent; border: none; color: #cbd5e1; cursor: pointer; padding: 5px; border-radius: 4px; }
        .nav-btn:hover { background: rgba(255,255,255,0.1); color: white; }
        .nav-btn svg { width: 16px; height: 16px; }
        .search-nav { flex: 1; max-width: 240px; margin: 0 16px; }
        .search-nav input {
            width: 100%; padding: 6px 12px; border-radius: 16px; border: none;
            background: rgba(255,255,255,0.12); color: white; font-size: 12px; outline: none; font-family: inherit;
        }
        .nav-right { margin-left: auto; display: flex; align-items: center; gap: 10px; font-size: 12px; }

        .main { padding: 24px 40px; max-width: 1200px; margin: 0 auto; }
        .page-title {
            font-size: 20px; font-weight: 600; color: #334155;
            margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;
        }
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; }
        .alert-success { background: #f0fdf4; color: #16a34a; border-left: 4px solid #16a34a; }
        .alert-error { background: #fef2f2; color: #dc2626; border-left: 4px solid #dc2626; }

        .form-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 12px 40px;
            max-width: 900px; margin-bottom: 16px;
        }
        .form-group { display: flex; align-items: center; gap: 12px; }
        .form-group label {
            font-size: 13px; color: #334155; font-weight: 500; min-width: 140px; white-space: nowrap;
        }
        .form-group input, .form-group select {
            flex: 1; padding: 8px 12px; border: 1px solid #94a3b8; border-radius: 4px;
            font-size: 13px; font-family: inherit; outline: none; max-width: 260px;
        }
        .form-group input:focus, .form-group select:focus {
            border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59,130,246,0.15);
        }

        .btn-row { display: flex; gap: 12px; margin-bottom: 28px; }
        .btn {
            width: 70px; height: 70px; border: none; border-radius: 10px;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 4px; cursor: pointer; color: white; font-size: 12px; font-weight: 500;
            font-family: inherit; transition: 0.2s;
        }
        .btn-save { background: #22c55e; }
        .btn-save:hover { background: #16a34a; }
        .btn-new { background: #f97316; }
        .btn-new:hover { background: #ea580c; }
        .btn svg { width: 22px; height: 22px; }

        .table-wrap { overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; min-width: 950px; }
        thead th {
            background: #3b82f6; color: white; font-weight: 500;
            padding: 12px 14px; text-align: left; white-space: nowrap;
        }
        tbody td { padding: 10px 14px; border-bottom: 1px solid #f1f5f9; color: #334155; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody tr:hover { background: #eff6ff; }

        .btn-edit {
            padding: 4px 14px; background: #1e40af; color: white; border: none;
            border-radius: 4px; font-size: 12px; cursor: pointer; font-family: inherit;
        }
        .btn-edit:hover { background: #1e3a8a; }
        .btn-del {
            padding: 4px 10px; background: #ef4444; color: white; border: none;
            border-radius: 4px; font-size: 12px; cursor: pointer; font-family: inherit; margin-left: 4px;
        }
        .btn-del:hover { background: #dc2626; }

        @media (max-width: 800px) {
            .form-grid { grid-template-columns: 1fr; }
            .main { padding: 16px; }
            .form-group { flex-direction: column; align-items: stretch; gap: 4px; }
            .form-group label { min-width: auto; }
            .form-group input, .form-group select { max-width: 100%; }
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
    <h1 class="page-title">Sub Section Registration</h1>

    <?php if ($message): ?>
    <div class="alert alert-<?= $msgType ?>"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= v($edit, 'id') ?>">

        <div class="form-grid">
            <div class="form-group">
                <label>Company Name</label>
                <select name="company_id" id="companySelect" required onchange="loadDivisions(this.value)">
                    <option value="">-select-</option>
                    <?php foreach ($companyList as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= v($edit, 'company_id') == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['company_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>SubDepartment Name</label>
                <select name="sub_department_id" id="subDepartmentSelect">
                    <option value="">-select-</option>
                    <?php foreach ($subDepartmentList as $sd): ?>
                    <option value="<?= $sd['id'] ?>" <?= v($edit, 'sub_department_id') == $sd['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sd['sub_department_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Division Name</label>
                <select name="division_id" id="divisionSelect" onchange="onDivisionChange(this.value)">
                    <option value="">-select-</option>
                    <?php foreach ($divisionList as $d): ?>
                    <option value="<?= $d['id'] ?>" <?= v($edit, 'division_id') == $d['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($d['division_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Section Name</label>
                <select name="section_id" id="sectionSelect">
                    <option value="">-select-</option>
                    <?php foreach ($sectionList as $sec): ?>
                    <option value="<?= $sec['id'] ?>" <?= v($edit, 'section_id') == $sec['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sec['section_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Sub Division Name</label>
                <select name="sub_division_id" id="subDivisionSelect">
                    <option value="">-select-</option>
                    <?php foreach ($subDivisionList as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= v($edit, 'sub_division_id') == $s['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['sub_division_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>SubSection No</label>
                <input type="text" name="sub_section_no" value="<?= v($edit, 'sub_section_no') ?>">
            </div>
            <div class="form-group">
                <label>Department Name</label>
                <select name="department_id" id="departmentSelect" onchange="onDepartmentChange(this.value)">
                    <option value="">-select-</option>
                    <?php foreach ($departmentList as $dp): ?>
                    <option value="<?= $dp['id'] ?>" <?= v($edit, 'department_id') == $dp['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($dp['department_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>SubSection Name</label>
                <input type="text" name="sub_section_name" value="<?= v($edit, 'sub_section_name') ?>" required>
            </div>
        </div>

        <div class="btn-row">
            <button type="submit" class="btn btn-save">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save
            </button>
            <button type="button" class="btn btn-new" onclick="location.href='sub-section-registration.php'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                New
            </button>
        </div>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>SubSection Name</th>
                    <th>SubSection No</th>
                    <th>Company Name</th>
                    <th>Division Name</th>
                    <th>Department Name</th>
                    <th>Section</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($subSections) === 0): ?>
                <tr><td colspan="7" style="text-align:center;padding:30px;color:#94a3b8;">No sub sections registered yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($subSections as $ss): ?>
                    <tr>
                        <td><?= v($ss, 'sub_section_name') ?></td>
                        <td><?= v($ss, 'sub_section_no') ?></td>
                        <td><?= v($ss, 'company_name') ?></td>
                        <td><?= v($ss, 'division_name', 'None') ?></td>
                        <td><?= v($ss, 'department_name', 'None') ?></td>
                        <td><?= v($ss, 'section_name', 'None') ?></td>
                        <td>
                            <a href="?edit=<?= $ss['id'] ?>"><button type="button" class="btn-edit">Edit</button></a>
                            <a href="?delete=<?= $ss['id'] ?>" onclick="return confirm('Delete this sub section?')"><button type="button" class="btn-del">Del</button></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function clearSelects(ids) {
    ids.forEach(id => { document.getElementById(id).innerHTML = '<option value="">-select-</option>'; });
}

function loadDivisions(companyId) {
    clearSelects(['divisionSelect','subDivisionSelect','departmentSelect','subDepartmentSelect','sectionSelect']);
    if (!companyId) return;
    fetch('sub-section-registration.php?ajax_divisions=' + companyId)
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('divisionSelect');
            data.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.id; opt.textContent = d.label;
                sel.appendChild(opt);
            });
        });
}

function onDivisionChange(divisionId) {
    clearSelects(['subDivisionSelect','departmentSelect','subDepartmentSelect','sectionSelect']);
    if (!divisionId) return;
    fetch('sub-section-registration.php?ajax_sub_divisions=' + divisionId)
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('subDivisionSelect');
            data.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.id; opt.textContent = d.label;
                sel.appendChild(opt);
            });
        });
    fetch('sub-section-registration.php?ajax_departments=' + divisionId)
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('departmentSelect');
            data.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.id; opt.textContent = d.label;
                sel.appendChild(opt);
            });
        });
    fetch('sub-section-registration.php?ajax_sections_by_division=' + divisionId)
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('sectionSelect');
            data.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.id; opt.textContent = d.label;
                sel.appendChild(opt);
            });
        });
}

function onDepartmentChange(departmentId) {
    clearSelects(['subDepartmentSelect']);
    const sectionSel = document.getElementById('sectionSelect');
    // keep sections from division, also try by department
    if (!departmentId) return;
    fetch('sub-section-registration.php?ajax_sub_departments=' + departmentId)
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('subDepartmentSelect');
            data.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.id; opt.textContent = d.label;
                sel.appendChild(opt);
            });
        });
    fetch('sub-section-registration.php?ajax_sections=' + departmentId)
        .then(r => r.json())
        .then(data => {
            if (data.length) {
                sectionSel.innerHTML = '<option value="">-select-</option>';
                data.forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d.id; opt.textContent = d.label;
                    sectionSel.appendChild(opt);
                });
            }
        });
}
</script>
</body>
</html>
