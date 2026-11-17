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

$conn->query("CREATE TABLE IF NOT EXISTS company_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    grace_period VARCHAR(20) DEFAULT '00:00:00',
    late_arrival VARCHAR(20) DEFAULT NULL,
    early_departure VARCHAR(20) DEFAULT NULL,
    half_day_hours VARCHAR(20) DEFAULT NULL,
    full_day_hours VARCHAR(20) DEFAULT NULL,
    ot_start_after VARCHAR(20) DEFAULT NULL,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (company_id)
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

$message = '';
$msgType = '';
$edit = null;

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM company_rules WHERE id = $id");
    $message = "Company rule deleted successfully!";
    $msgType = 'success';
}

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM company_rules WHERE id = $id");
    if ($res && $res->num_rows > 0) $edit = $res->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $company_id      = (int)($_POST['company_id'] ?? 0);
    $grace_period    = trim($_POST['grace_period'] ?? '');
    $late_arrival    = trim($_POST['late_arrival'] ?? '');
    $early_departure = trim($_POST['early_departure'] ?? '');
    $half_day_hours  = trim($_POST['half_day_hours'] ?? '');
    $full_day_hours  = trim($_POST['full_day_hours'] ?? '');
    $ot_start_after  = trim($_POST['ot_start_after'] ?? '');
    $remarks         = trim($_POST['remarks'] ?? '');
    $rec_id          = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($company_id <= 0) {
        $message = "Please select a Company!";
        $msgType = 'error';
    } elseif ($grace_period === '') {
        $message = "Grace Period is required!";
        $msgType = 'error';
    } else {
        if ($rec_id > 0) {
            $stmt = $conn->prepare("UPDATE company_rules SET company_id=?, grace_period=?, late_arrival=?, early_departure=?, half_day_hours=?, full_day_hours=?, ot_start_after=?, remarks=? WHERE id=?");
            $stmt->bind_param("isssssssi", $company_id, $grace_period, $late_arrival, $early_departure, $half_day_hours, $full_day_hours, $ot_start_after, $remarks, $rec_id);
            if ($stmt->execute()) {
                $message = "Company rule updated successfully!";
                $msgType = 'success';
                $edit = null;
            } else {
                $message = "Update failed: " . $conn->error;
                $msgType = 'error';
            }
            $stmt->close();
        } else {
            $stmt = $conn->prepare("INSERT INTO company_rules (company_id, grace_period, late_arrival, early_departure, half_day_hours, full_day_hours, ot_start_after, remarks) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->bind_param("isssssss", $company_id, $grace_period, $late_arrival, $early_departure, $half_day_hours, $full_day_hours, $ot_start_after, $remarks);
            if ($stmt->execute()) {
                $message = "Company rule saved successfully!";
                $msgType = 'success';
            } else {
                $message = "Save failed: " . $conn->error;
                $msgType = 'error';
            }
            $stmt->close();
        }
    }
}

$companyList = [];
$cr = $conn->query("SELECT id, company_name FROM companies ORDER BY id ASC");
while ($c = $cr->fetch_assoc()) $companyList[] = $c;

$rules = [];
$res = $conn->query("SELECT cr.*, c.company_name FROM company_rules cr LEFT JOIN companies c ON cr.company_id = c.id ORDER BY c.company_name ASC");
if ($res) {
    while ($r = $res->fetch_assoc()) $rules[] = $r;
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
    <title>Sipway Campus - Company Rules</title>
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

        .main { padding: 24px 40px; max-width: 900px; margin: 0 auto; }
        .page-title {
            font-size: 20px; font-weight: 600; color: #334155;
            margin-bottom: 24px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;
        }
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; }
        .alert-success { background: #f0fdf4; color: #16a34a; border-left: 4px solid #16a34a; }
        .alert-error { background: #fef2f2; color: #dc2626; border-left: 4px solid #dc2626; }

        .form-area { max-width: 420px; margin-bottom: 16px; }
        .form-group { margin-bottom: 16px; }
        .form-group label {
            display: block; font-size: 13px; color: #334155; font-weight: 500; margin-bottom: 6px;
        }
        .form-group label .req { color: #ef4444; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; max-width: 280px; padding: 8px 12px; border: 1px solid #94a3b8;
            border-radius: 4px; font-size: 13px; font-family: inherit; outline: none;
        }
        .form-group textarea { max-width: 360px; min-height: 60px; resize: vertical; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59,130,246,0.15);
        }
        .hint { font-size: 11px; color: #64748b; margin-top: 2px; }

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
        table { width: 100%; border-collapse: collapse; font-size: 13px; min-width: 700px; }
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
    <h1 class="page-title">Company Rules</h1>

    <?php if ($message): ?>
    <div class="alert alert-<?= $msgType ?>"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= v($edit, 'id') ?>">

        <div class="form-area">
            <div class="form-group">
                <label>Company Name <span class="req">*</span></label>
                <select name="company_id" required>
                    <option value="">-select-</option>
                    <?php foreach ($companyList as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= v($edit, 'company_id') == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['company_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Grace Period <span class="hint">(hh:mm:ss)</span> <span class="req">*</span></label>
                <input type="text" name="grace_period" value="<?= v($edit, 'grace_period') ?>" placeholder="00:15:00" required>
            </div>
            <div class="form-group">
                <label>Late Arrival <span class="hint">(hh:mm:ss)</span></label>
                <input type="text" name="late_arrival" value="<?= v($edit, 'late_arrival') ?>" placeholder="00:30:00">
            </div>
            <div class="form-group">
                <label>Early Departure <span class="hint">(hh:mm:ss)</span></label>
                <input type="text" name="early_departure" value="<?= v($edit, 'early_departure') ?>" placeholder="00:30:00">
            </div>
            <div class="form-group">
                <label>Half Day Hours <span class="hint">(hh:mm:ss)</span></label>
                <input type="text" name="half_day_hours" value="<?= v($edit, 'half_day_hours') ?>" placeholder="04:00:00">
            </div>
            <div class="form-group">
                <label>Full Day Hours <span class="hint">(hh:mm:ss)</span></label>
                <input type="text" name="full_day_hours" value="<?= v($edit, 'full_day_hours') ?>" placeholder="08:00:00">
            </div>
            <div class="form-group">
                <label>OT Start After <span class="hint">(hh:mm:ss)</span></label>
                <input type="text" name="ot_start_after" value="<?= v($edit, 'ot_start_after') ?>" placeholder="08:00:00">
            </div>
            <div class="form-group">
                <label>Remarks</label>
                <textarea name="remarks"><?= v($edit, 'remarks') ?></textarea>
            </div>
        </div>

        <div class="btn-row">
            <button type="submit" class="btn btn-save">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save
            </button>
            <button type="button" class="btn btn-new" onclick="location.href='company-rules.php'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                New
            </button>
        </div>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Company Name</th>
                    <th>Grace Period</th>
                    <th>Late Arrival</th>
                    <th>Early Departure</th>
                    <th>Half Day</th>
                    <th>Full Day</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($rules) === 0): ?>
                <tr><td colspan="7" style="text-align:center;padding:30px;color:#94a3b8;">No company rules saved yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($rules as $r): ?>
                    <tr>
                        <td><?= v($r, 'company_name') ?></td>
                        <td><?= v($r, 'grace_period') ?></td>
                        <td><?= v($r, 'late_arrival') ?></td>
                        <td><?= v($r, 'early_departure') ?></td>
                        <td><?= v($r, 'half_day_hours') ?></td>
                        <td><?= v($r, 'full_day_hours') ?></td>
                        <td>
                            <a href="?edit=<?= $r['id'] ?>"><button type="button" class="btn-edit">Edit</button></a>
                            <a href="?delete=<?= $r['id'] ?>" onclick="return confirm('Delete this rule?')"><button type="button" class="btn-del">Del</button></a>
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
