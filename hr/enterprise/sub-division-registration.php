<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

$conn = getDB();

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
    company_id INT,
    division_id INT,
    sub_division_no VARCHAR(50),
    sub_division_name VARCHAR(200) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (company_id), INDEX (division_id)
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

if (isset($_GET['ajax_divisions'])) {
    header('Content-Type: application/json');
    $cid = (int)$_GET['ajax_divisions'];
    $list = [];
    $res = $conn->query("SELECT id, division_no, division_name FROM divisions WHERE company_id = $cid ORDER BY division_no ASC");
    while ($r = $res->fetch_assoc()) {
        $list[] = ['id' => $r['id'], 'label' => $r['division_name']];
    }
    echo json_encode($list);
    exit;
}

$message = '';
$msgType = '';
$edit = null;

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM sub_divisions WHERE id = $id");
    $message = "Sub Division deleted successfully!";
    $msgType = 'success';
}

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM sub_divisions WHERE id = $id");
    if ($res && $res->num_rows > 0) $edit = $res->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $company_id        = (int)($_POST['company_id'] ?? 0);
    $division_id       = (int)($_POST['division_id'] ?? 0);
    $sub_division_no   = trim($_POST['sub_division_no'] ?? '');
    $sub_division_name = trim($_POST['sub_division_name'] ?? '');
    $rec_id            = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($company_id <= 0) {
        $message = "Please select a Company!";
        $msgType = 'error';
    } elseif ($division_id <= 0) {
        $message = "Please select a Division!";
        $msgType = 'error';
    } elseif ($sub_division_name === '') {
        $message = "Sub Division Name is required!";
        $msgType = 'error';
    } else {
        if ($rec_id > 0) {
            $stmt = $conn->prepare("UPDATE sub_divisions SET company_id=?, division_id=?, sub_division_no=?, sub_division_name=? WHERE id=?");
            $stmt->bind_param("iissi", $company_id, $division_id, $sub_division_no, $sub_division_name, $rec_id);
            if ($stmt->execute()) {
                $message = "Sub Division updated successfully!";
                $msgType = 'success';
                $edit = null;
            } else {
                $message = "Update failed: " . $conn->error;
                $msgType = 'error';
            }
            $stmt->close();
        } else {
            if ($sub_division_no === '') $sub_division_no = '0';
            $stmt = $conn->prepare("INSERT INTO sub_divisions (company_id, division_id, sub_division_no, sub_division_name) VALUES (?,?,?,?)");
            $stmt->bind_param("iiss", $company_id, $division_id, $sub_division_no, $sub_division_name);
            if ($stmt->execute()) {
                $message = "Sub Division saved successfully!";
                $msgType = 'success';
            } else {
                $message = "Save failed: " . $conn->error;
                $msgType = 'error';
            }
            $stmt->close();
        }
    }
}

$chk3 = $conn->query("SELECT COUNT(*) as c FROM sub_divisions");
$row3 = $chk3 ? $chk3->fetch_assoc() : ['c' => 0];
if ((int)$row3['c'] === 0) {
    $dr = $conn->query("SELECT id, company_id FROM divisions");
    while ($d = $dr->fetch_assoc()) {
        $cid = (int)$d['company_id'];
        $did = (int)$d['id'];
        $conn->query("INSERT INTO sub_divisions (company_id, division_id, sub_division_no, sub_division_name) VALUES ($cid, $did, '0', 'None')");
    }
}

$companyList = [];
$cr = $conn->query("SELECT id, company_name FROM companies ORDER BY id ASC");
while ($c = $cr->fetch_assoc()) $companyList[] = $c;

$divisionList = [];
if ($edit && $edit['company_id']) {
    $dr = $conn->query("SELECT id, division_name FROM divisions WHERE company_id = " . (int)$edit['company_id'] . " ORDER BY division_no ASC");
    while ($d = $dr->fetch_assoc()) $divisionList[] = $d;
}

$subDivisions = [];
$res = $conn->query("SELECT sd.*, c.company_name, d.division_name 
    FROM sub_divisions sd
    LEFT JOIN companies c ON sd.company_id = c.id
    LEFT JOIN divisions d ON sd.division_id = d.id
    ORDER BY c.id ASC, d.division_no ASC, sd.id ASC");
if ($res) {
    while ($r = $res->fetch_assoc()) $subDivisions[] = $r;
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
<title>Sipway Campus - Sub Division Registration</title>
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

  .main {
    padding: 32px 36px 50px;
    max-width: 1100px;
    margin: 0 auto;
  }

  .page-title {
    font-size: 24px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 24px;
    letter-spacing: -0.3px;
  }

  .alert {
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 20px;
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

  .form-card {
    background: white;
    border-radius: 16px;
    padding: 28px 32px;
    box-shadow: 0 6px 20px rgba(14,165,233,0.07);
    border: 1px solid rgba(255,255,255,0.9);
    margin-bottom: 28px;
  }

  .form-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
    max-width: 480px;
    margin-bottom: 24px;
  }

  .form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .form-group label {
    font-size: 13px;
    color: var(--text-muted);
    font-weight: 600;
  }

  .form-group input,
  .form-group select {
    padding: 11px 14px;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    font-size: 14px;
    font-family: inherit;
    outline: none;
    background: #f8fafc;
    transition: all 0.25s;
  }
  .form-group input:focus,
  .form-group select:focus {
    border-color: var(--primary);
    background: #f0f9ff;
  }

  .btn-row {
    display: flex;
    gap: 12px;
  }

  .btn {
    padding: 12px 28px;
    border: none;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
    color: white;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.25s;
  }

  .btn-save {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    box-shadow: 0 4px 14px rgba(34,197,94,0.3);
  }
  .btn-save:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(34,197,94,0.4);
  }

  .btn-new {
    background: linear-gradient(135deg, #f97316, #ea580c);
    box-shadow: 0 4px 14px rgba(249,115,22,0.3);
  }
  .btn-new:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(249,115,22,0.4);
  }

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
    min-width: 800px;
  }

  thead th {
    background: linear-gradient(135deg, #0f172a, #1e293b);
    color: white;
    font-weight: 600;
    padding: 14px 16px;
    text-align: left;
    white-space: nowrap;
    font-size: 12.5px;
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

  .btn-edit {
    padding: 6px 14px;
    background: linear-gradient(135deg, #1e40af, #1e3a8a);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
    transition: all 0.2s;
  }
  .btn-edit:hover {
    transform: translateY(-1px);
  }

  .btn-del {
    padding: 6px 12px;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
    margin-left: 6px;
    transition: all 0.2s;
  }
  .btn-del:hover {
    transform: translateY(-1px);
  }

  @media (max-width: 600px) {
    .main {
      padding: 20px 16px 40px;
    }
    .form-card {
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
  <h1 class="page-title">Sub Division Registration</h1>

  <?php if ($message): ?>
  <div class="alert alert-<?= $msgType ?>"><?= $message ?></div>
  <?php endif; ?>

  <div class="form-card">
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
          <label>Division Name</label>
          <select name="division_id" id="divisionSelect" required>
            <option value="">-select-</option>
            <?php foreach ($divisionList as $d): ?>
            <option value="<?= $d['id'] ?>" <?= v($edit, 'division_id') == $d['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($d['division_name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Sub Division No</label>
          <input type="text" name="sub_division_no" value="<?= v($edit, 'sub_division_no') ?>">
        </div>
        <div class="form-group">
          <label>Sub Division Name</label>
          <input type="text" name="sub_division_name" value="<?= v($edit, 'sub_division_name') ?>" required>
        </div>
      </div>

      <div class="btn-row">
        <button type="submit" class="btn btn-save">Save</button>
        <button type="button" class="btn btn-new" onclick="location.href='sub-division-registration.php'">New</button>
      </div>
    </form>
  </div>

  <div class="table-card">
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>SubDivision Name</th>
            <th>SubDivision No</th>
            <th>Company Name</th>
            <th>Division Name</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($subDivisions) === 0): ?>
          <tr>
            <td colspan="5" style="text-align:center;padding:40px;color:#94a3b8;">No sub divisions registered yet.</td>
          </tr>
          <?php else: ?>
            <?php foreach ($subDivisions as $sd): ?>
            <tr>
              <td><?= v($sd, 'sub_division_name') ?></td>
              <td><?= v($sd, 'sub_division_no') ?></td>
              <td><?= v($sd, 'company_name') ?></td>
              <td><?= v($sd, 'division_name') ?></td>
              <td>
                <a href="?edit=<?= $sd['id'] ?>"><button type="button" class="btn-edit">Edit</button></a>
                <a href="?delete=<?= $sd['id'] ?>" onclick="return confirm('Delete this sub division?')"><button type="button" class="btn-del">Del</button></a>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
function loadDivisions(companyId, selectedId) {
  const sel = document.getElementById('divisionSelect');
  sel.innerHTML = '<option value="">-select-</option>';
  if (!companyId) return;
  fetch('sub-division-registration.php?ajax_divisions=' + companyId)
    .then(r => r.json())
    .then(data => {
      data.forEach(d => {
        const opt = document.createElement('option');
        opt.value = d.id;
        opt.textContent = d.label;
        if (selectedId && selectedId == d.id) opt.selected = true;
        sel.appendChild(opt);
      });
    });
}
</script>
</body>
</html>