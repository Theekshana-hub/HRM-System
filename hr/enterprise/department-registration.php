<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

$conn = getDB();

// Parent tables
$conn->query("CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_no VARCHAR(50) UNIQUE,
    company_name VARCHAR(200) NOT NULL,
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
    company_id INT,
    division_id INT,
    sub_division_id INT,
    department_no VARCHAR(50),
    department_name VARCHAR(200) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (company_id), INDEX (division_id), INDEX (sub_division_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Seed companies if empty
$chk = $conn->query("SELECT COUNT(*) as c FROM companies");
$row = $chk ? $chk->fetch_assoc() : ['c' => 0];
if ((int)$row['c'] === 0) {
    $conn->query("INSERT INTO companies (company_no, company_name, registration_year) VALUES
        ('1', 'Sipway Campus Pvt Ltd', '2025'),
        ('2', 'Sipway Higher Education Pvt Ltd', '2025'),
        ('3', 'Sipway Online School', '2025'),
        ('4', 'Sipway English Academy Pvt Ltd', '2025')");
}

// AJAX helpers
if (isset($_GET['ajax_divisions'])) {
    header('Content-Type: application/json');
    $cid = (int)$_GET['ajax_divisions'];
    $list = [];
    $res = $conn->query("SELECT id, division_name FROM divisions WHERE company_id = $cid ORDER BY division_no ASC");
    while ($r = $res->fetch_assoc()) $list[] = ['id' => $r['id'], 'label' => $r['division_name']];
    echo json_encode($list);
    exit;
}
if (isset($_GET['ajax_sub_divisions'])) {
    header('Content-Type: application/json');
    $did = (int)$_GET['ajax_sub_divisions'];
    $list = [];
    $res = $conn->query("SELECT id, sub_division_name FROM sub_divisions WHERE division_id = $did ORDER BY sub_division_no ASC");
    while ($r = $res->fetch_assoc()) $list[] = ['id' => $r['id'], 'label' => $r['sub_division_name']];
    echo json_encode($list);
    exit;
}

$message = '';
$msgType = '';
$edit = null;

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM departments WHERE id = $id");
    $message = "Department deleted successfully!";
    $msgType = 'success';
}

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM departments WHERE id = $id");
    if ($res && $res->num_rows > 0) $edit = $res->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $company_id      = (int)($_POST['company_id'] ?? 0);
    $division_id     = (int)($_POST['division_id'] ?? 0);
    $sub_division_id = (int)($_POST['sub_division_id'] ?? 0);
    $department_no   = trim($_POST['department_no'] ?? '');
    $department_name = trim($_POST['department_name'] ?? '');
    $rec_id          = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($company_id <= 0) {
        $message = "Please select a Company!";
        $msgType = 'error';
    } elseif ($department_name === '') {
        $message = "Department Name is required!";
        $msgType = 'error';
    } else {
        if ($department_no === '') $department_no = '0';
        if ($rec_id > 0) {
            $stmt = $conn->prepare("UPDATE departments SET company_id=?, division_id=?, sub_division_id=?, department_no=?, department_name=? WHERE id=?");
            $stmt->bind_param("iiissi", $company_id, $division_id, $sub_division_id, $department_no, $department_name, $rec_id);
            if ($stmt->execute()) {
                $message = "Department updated successfully!";
                $msgType = 'success';
                $edit = null;
            } else {
                $message = "Update failed: " . $conn->error;
                $msgType = 'error';
            }
            $stmt->close();
        } else {
            $stmt = $conn->prepare("INSERT INTO departments (company_id, division_id, sub_division_id, department_no, department_name) VALUES (?,?,?,?,?)");
            $stmt->bind_param("iiiss", $company_id, $division_id, $sub_division_id, $department_no, $department_name);
            if ($stmt->execute()) {
                $message = "Department saved successfully!";
                $msgType = 'success';
            } else {
                $message = "Save failed: " . $conn->error;
                $msgType = 'error';
            }
            $stmt->close();
        }
    }
}

// Seed default "None" departments for each sub_division if empty
$chkD = $conn->query("SELECT COUNT(*) as c FROM departments");
$rowD = $chkD ? $chkD->fetch_assoc() : ['c' => 0];
if ((int)$rowD['c'] === 0) {
    $sr = $conn->query("SELECT id, company_id, division_id FROM sub_divisions");
    if ($sr && $sr->num_rows > 0) {
        while ($s = $sr->fetch_assoc()) {
            $cid = (int)$s['company_id'];
            $did = (int)$s['division_id'];
            $sid = (int)$s['id'];
            $conn->query("INSERT INTO departments (company_id, division_id, sub_division_id, department_no, department_name) VALUES ($cid, $did, $sid, '0', 'None')");
        }
    } else {
        // fallback: one None per division
        $dr = $conn->query("SELECT id, company_id FROM divisions");
        while ($d = $dr->fetch_assoc()) {
            $cid = (int)$d['company_id'];
            $did = (int)$d['id'];
            $conn->query("INSERT INTO departments (company_id, division_id, sub_division_id, department_no, department_name) VALUES ($cid, $did, 0, '0', 'None')");
        }
    }
}

$companyList = [];
$cr = $conn->query("SELECT id, company_name FROM companies ORDER BY id ASC");
while ($c = $cr->fetch_assoc()) $companyList[] = $c;

$divisionList = [];
$subDivisionList = [];
if ($edit && $edit['company_id']) {
    $dr = $conn->query("SELECT id, division_name FROM divisions WHERE company_id = " . (int)$edit['company_id'] . " ORDER BY division_no ASC");
    while ($d = $dr->fetch_assoc()) $divisionList[] = $d;
}
if ($edit && $edit['division_id']) {
    $sr = $conn->query("SELECT id, sub_division_name FROM sub_divisions WHERE division_id = " . (int)$edit['division_id'] . " ORDER BY sub_division_no ASC");
    while ($s = $sr->fetch_assoc()) $subDivisionList[] = $s;
}

$departments = [];
$res = $conn->query("SELECT dep.*, c.company_name, d.division_name, sd.sub_division_name
    FROM departments dep
    LEFT JOIN companies c ON dep.company_id = c.id
    LEFT JOIN divisions d ON dep.division_id = d.id
    LEFT JOIN sub_divisions sd ON dep.sub_division_id = sd.id
    ORDER BY c.id ASC, d.division_no ASC, dep.id ASC");
if ($res) {
    while ($r = $res->fetch_assoc()) $departments[] = $r;
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
    <title>Sipway Campus - Department Registration</title>
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
            font-size: 13px; color: #334155; font-weight: 500; min-width: 130px; white-space: nowrap;
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
        table { width: 100%; border-collapse: collapse; font-size: 13px; min-width: 900px; }
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
    <h1 class="page-title">Department Registration</h1>

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
                <label>Department No</label>
                <input type="text" name="department_no" value="<?= v($edit, 'department_no') ?>">
            </div>
            <div class="form-group">
                <label>Division Name</label>
                <select name="division_id" id="divisionSelect" onchange="loadSubDivisions(this.value)">
                    <option value="">-select-</option>
                    <?php foreach ($divisionList as $d): ?>
                    <option value="<?= $d['id'] ?>" <?= v($edit, 'division_id') == $d['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($d['division_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Department Name</label>
                <input type="text" name="department_name" value="<?= v($edit, 'department_name') ?>" required>
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
        </div>

        <div class="btn-row">
            <button type="submit" class="btn btn-save">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save
            </button>
            <button type="button" class="btn btn-new" onclick="location.href='department-registration.php'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                New
            </button>
        </div>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Department Name</th>
                    <th>Department No</th>
                    <th>Company Name</th>
                    <th>Division Name</th>
                    <th>Sub Division</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($departments) === 0): ?>
                <tr><td colspan="6" style="text-align:center;padding:30px;color:#94a3b8;">No departments registered yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($departments as $dep): ?>
                    <tr>
                        <td><?= v($dep, 'department_name') ?></td>
                        <td><?= v($dep, 'department_no') ?></td>
                        <td><?= v($dep, 'company_name') ?></td>
                        <td><?= v($dep, 'division_name', 'None') ?></td>
                        <td><?= v($dep, 'sub_division_name', 'None') ?></td>
                        <td>
                            <a href="?edit=<?= $dep['id'] ?>"><button type="button" class="btn-edit">Edit</button></a>
                            <a href="?delete=<?= $dep['id'] ?>" onclick="return confirm('Delete this department?')"><button type="button" class="btn-del">Del</button></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function loadDivisions(companyId) {
    const sel = document.getElementById('divisionSelect');
    const subSel = document.getElementById('subDivisionSelect');
    sel.innerHTML = '<option value="">-select-</option>';
    subSel.innerHTML = '<option value="">-select-</option>';
    if (!companyId) return;
    fetch('department-registration.php?ajax_divisions=' + companyId)
        .then(r => r.json())
        .then(data => {
            data.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.id;
                opt.textContent = d.label;
                sel.appendChild(opt);
            });
        });
}

function loadSubDivisions(divisionId) {
    const sel = document.getElementById('subDivisionSelect');
    sel.innerHTML = '<option value="">-select-</option>';
    if (!divisionId) return;
    fetch('department-registration.php?ajax_sub_divisions=' + divisionId)
        .then(r => r.json())
        .then(data => {
            data.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.id;
                opt.textContent = d.label;
                sel.appendChild(opt);
            });
        });
}
</script>
</body>
</html>
