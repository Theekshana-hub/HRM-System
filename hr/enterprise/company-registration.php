<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

$conn = getDB();

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

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM companies WHERE id = $id");
    $message = "Company deleted successfully!";
    $msgType = 'success';
}

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM companies WHERE id = $id");
    if ($res && $res->num_rows > 0) {
        $edit = $res->fetch_assoc();
    }
}

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
            if ($company_no === '') {
                $r = $conn->query("SELECT MAX(CAST(company_no AS UNSIGNED)) as m FROM companies");
                $mx = $r->fetch_assoc();
                $company_no = (string)(((int)($mx['m'] ?? 0)) + 1);
            }
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
    grid-template-columns: 1fr 1fr;
    gap: 18px 32px;
    margin-bottom: 24px;
  }

  .form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }
  .form-group.full {
    grid-column: 1 / -1;
  }

  .form-group label {
    font-size: 13px;
    color: var(--text-muted);
    font-weight: 600;
  }

  .form-group input,
  .form-group select,
  .form-group textarea {
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
  .form-group select:focus,
  .form-group textarea:focus {
    border-color: var(--primary);
    background: #f0f9ff;
  }

  .form-group textarea {
    min-height: 90px;
    resize: vertical;
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
    min-width: 900px;
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

  @media (max-width: 700px) {
    .form-grid {
      grid-template-columns: 1fr;
    }
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
  <h1 class="page-title">Company Registration</h1>

  <?php if ($message): ?>
  <div class="alert alert-<?= $msgType ?>"><?= $message ?></div>
  <?php endif; ?>

  <div class="form-card">
    <form method="POST" id="companyForm">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= v($edit, 'id') ?>">

      <div class="form-grid">
        <div class="form-group">
          <label>Company No</label>
          <input type="text" name="company_no" value="<?= v($edit, 'company_no') ?>">
        </div>
        <div class="form-group">
          <label>Registration Year</label>
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
        <button type="submit" class="btn btn-save">Save</button>
        <button type="button" class="btn btn-new" onclick="location.href='company-registration.php'">New</button>
      </div>
    </form>
  </div>

  <div class="table-card">
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
          <tr>
            <td colspan="8" style="text-align:center;padding:40px;color:#94a3b8;">No companies registered yet.</td>
          </tr>
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
</div>

</body>
</html>