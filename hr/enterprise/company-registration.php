<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

$conn = getDB();

// Create companies table
$conn->query("CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_no VARCHAR(50) UNIQUE,
    company_name VARCHAR(200) NOT NULL,
    phone_no VARCHAR(50),
    address TEXT,
    registration_year VARCHAR(20),
    epf_registration_no VARCHAR(50),
    tin_no VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Seed sample data if empty
$chk = $conn->query("SELECT COUNT(*) as c FROM companies");
$row = $chk ? $chk->fetch_assoc() : ['c' => 0];
if ((int)$row['c'] === 0) {
    $conn->query("INSERT INTO companies (company_no, company_name, phone_no, address, registration_year, epf_registration_no, tin_no) VALUES
        ('1', 'Sipway Campus Pvt Ltd', '', 'No. 1/10A, Rajapihala, Sarwodaya Mawatha, Kurunegala, Sri Lanka.', '2025', 'Y/24049', ''),
        ('2', 'Sipway Higher Education Pvt Ltd', '', '', '2025', '', ''),
        ('3', 'Sipway Online School', '', '', '2025', '', ''),
        ('4', 'Sipway English Academy Pvt Ltd', '', '', '2025', '', '')
    ");
}

$message = '';
$msgType = '';
$edit = null;

// ========== DELETE ==========
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM companies WHERE id = $id");
    $message = "Company deleted successfully!";
    $msgType = 'success';
}

// ========== LOAD FOR EDIT ==========
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM companies WHERE id = $id");
    if ($res && $res->num_rows > 0) {
        $edit = $res->fetch_assoc();
    }
}

// ========== SAVE / UPDATE ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $company_no   = trim($_POST['company_no'] ?? '');
    $company_name = trim($_POST['company_name'] ?? '');
    $phone_no     = trim($_POST['phone_no'] ?? '');
    $address      = trim($_POST['address'] ?? '');
    $reg_year     = trim($_POST['registration_year'] ?? '');
    $epf_no       = trim($_POST['epf_registration_no'] ?? '');
    $tin_no       = trim($_POST['tin_no'] ?? '');
    $emp_id       = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($company_name === '') {
        $message = "Company Name is required!";
        $msgType = 'error';
    } else {
        if ($emp_id > 0) {
            // UPDATE
            $stmt = $conn->prepare("UPDATE companies SET company_no=?, company_name=?, phone_no=?, address=?, registration_year=?, epf_registration_no=?, tin_no=? WHERE id=?");
            $stmt->bind_param("sssssssi", $company_no, $company_name, $phone_no, $address, $reg_year, $epf_no, $tin_no, $emp_id);
            if ($stmt->execute()) {
                $message = "Company updated successfully!";
                $msgType = 'success';
                $edit = null;
            } else {
                $message = "Update failed: " . $conn->error;
                $msgType = 'error';
            }
            $stmt->close();
        } else {
            // Auto company_no if empty
            if ($company_no === '') {
                $r = $conn->query("SELECT MAX(CAST(company_no AS UNSIGNED)) as m FROM companies");
                $mx = $r->fetch_assoc();
                $company_no = (string)(((int)($mx['m'] ?? 0)) + 1);
            }
            // INSERT
            $stmt = $conn->prepare("INSERT INTO companies (company_no, company_name, phone_no, address, registration_year, epf_registration_no, tin_no) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param("sssssss", $company_no, $company_name, $phone_no, $address, $reg_year, $epf_no, $tin_no);
            if ($stmt->execute()) {
                $message = "Company saved successfully!";
                $msgType = 'success';
            } else {
                $message = "Save failed: " . $conn->error;
                $msgType = 'error';
            }
            $stmt->close();
        }
    }
}

// Fetch all companies
$companies = [];
$res = $conn->query("SELECT * FROM companies ORDER BY CAST(company_no AS UNSIGNED) ASC, id ASC");
if ($res) {
    while ($r = $res->fetch_assoc()) $companies[] = $r;
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
    <title>Sipway Campus - Company Registration</title>
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

        .main { padding: 24px 40px; max-width: 1100px; margin: 0 auto; }
        .page-title {
            font-size: 20px; font-weight: 600; color: #334155;
            margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;
        }

        .alert {
            padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px;
        }
        .alert-success { background: #f0fdf4; color: #16a34a; border-left: 4px solid #16a34a; }
        .alert-error { background: #fef2f2; color: #dc2626; border-left: 4px solid #dc2626; }

        .form-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 14px 40px;
            max-width: 800px; margin-bottom: 20px;
        }
        .form-group { display: flex; align-items: flex-start; gap: 12px; }
        .form-group.full { grid-column: 1 / -1; }
        .form-group label {
            font-size: 13px; color: #334155; font-weight: 500;
            min-width: 130px; padding-top: 8px; white-space: nowrap;
        }
        .form-group input, .form-group select, .form-group textarea {
            flex: 1; padding: 8px 12px; border: 1px solid #94a3b8; border-radius: 4px;
            font-size: 13px; font-family: inherit; outline: none; max-width: 260px;
        }
        .form-group textarea { max-width: 320px; min-height: 80px; resize: vertical; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
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
        tbody td {
            padding: 11px 14px; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: middle;
        }
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

        @media (max-width: 700px) {
            .form-grid { grid-template-columns: 1fr; }
            .main { padding: 16px; }
            .form-group { flex-direction: column; gap: 4px; }
            .form-group label { min-width: auto; padding-top: 0; }
            .form-group input, .form-group select, .form-group textarea { max-width: 100%; }
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
    <h1 class="page-title">Company Registration</h1>

    <?php if ($message): ?>
    <div class="alert alert-<?= $msgType ?>"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST" id="companyForm">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= v($edit, 'id') ?>">

        <div class="form-grid">
            <div class="form-group">
                <label>Company No</label>
                <input type="text" name="company_no" value="<?= v($edit, 'company_no') ?>" placeholder="">
            </div>
            <div class="form-group">
                <label>Registration Years</label>
                <select name="registration_year">
                    <option value="">-select-</option>
                    <?php for ($y = 2020; $y <= 2030; $y++): ?>
                    <option value="<?= $y ?>" <?= v($edit, 'registration_year') == (string)$y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Company Name</label>
                <input type="text" name="company_name" value="<?= v($edit, 'company_name') ?>" required>
            </div>
            <div class="form-group">
                <label>EPF Registration No</label>
                <input type="text" name="epf_registration_no" value="<?= v($edit, 'epf_registration_no') ?>">
            </div>
            <div class="form-group">
                <label>Phone No</label>
                <input type="text" name="phone_no" value="<?= v($edit, 'phone_no') ?>">
            </div>
            <div class="form-group">
                <label>Tin No</label>
                <input type="text" name="tin_no" value="<?= v($edit, 'tin_no') ?>">
            </div>
            <div class="form-group full">
                <label>Address</label>
                <textarea name="address"><?= v($edit, 'address') ?></textarea>
            </div>
        </div>

        <div class="btn-row">
            <button type="submit" class="btn btn-save">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save
            </button>
            <button type="button" class="btn btn-new" onclick="location.href='company-registration.php'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                New
            </button>
        </div>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Company No</th>
                    <th>Company Name</th>
                    <th>Company Tel</th>
                    <th>Company Address</th>
                    <th>Company Year</th>
                    <th>EPF No</th>
                    <th>Tin No</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($companies) === 0): ?>
                <tr><td colspan="8" style="text-align:center;padding:30px;color:#94a3b8;">No companies registered yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($companies as $c): ?>
                    <tr>
                        <td><?= v($c, 'company_no') ?></td>
                        <td><?= v($c, 'company_name') ?></td>
                        <td><?= v($c, 'phone_no') ?></td>
                        <td><?= v($c, 'address') ?></td>
                        <td><?= v($c, 'registration_year') ?></td>
                        <td><?= v($c, 'epf_registration_no') ?></td>
                        <td><?= v($c, 'tin_no') ?></td>
                        <td>
                            <a href="?edit=<?= $c['id'] ?>"><button type="button" class="btn-edit">Edit</button></a>
                            <a href="?delete=<?= $c['id'] ?>" onclick="return confirm('Delete this company?')"><button type="button" class="btn-del">Del</button></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
