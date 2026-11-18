<?php
require 'config.php';
require_login();
$emp  = $_SESSION['emp'];
$name = $emp['name_with_initials'] ?? $emp['full_name'] ?? $emp[COL_EMP_NO];

$tiles = [
  ['Employee Profile',      'profile.php',          '#e0e7ff', '<circle cx="50" cy="35" r="16"/><path d="M20 90a30 30 0 0 1 60 0z"/>'],
  ['Attendance Display',    'coming-soon.php?m=Attendance+Display', '#dbeafe', '<rect x="15" y="20" width="70" height="60" rx="6" fill="none" stroke="currentColor" stroke-width="6"/><path d="M15 40h70M35 12v14M65 12v14" stroke="currentColor" stroke-width="6" fill="none"/>'],
  ['Leave Application',     'coming-soon.php?m=Leave+Application',  '#ede9fe', '<rect x="12" y="35" width="76" height="50" rx="8"/><path d="M38 35v-10h24v10" fill="none" stroke="currentColor" stroke-width="6"/>'],
  ['Apply Manual Attendance','manual-attendance.php','#fce7f3','<rect x="28" y="10" width="44" height="80" rx="8" fill="none" stroke="currentColor" stroke-width="6"/><circle cx="50" cy="75" r="4"/>'],
  ['Notification Center',   'coming-soon.php?m=Notification+Center','#dbeafe', '<path d="M50 12a22 22 0 0 0-22 22v22l-8 10h60l-8-10V34A22 22 0 0 0 50 12z"/><circle cx="50" cy="86" r="7"/>'],
  ['Salary Details',        'coming-soon.php?m=Salary+Details',     '#e0f2fe', '<rect x="12" y="25" width="76" height="50" rx="6" fill="none" stroke="currentColor" stroke-width="6"/><circle cx="50" cy="50" r="11"/>'],
  ['Log Out',               'logout.php',           '#fee2e2', '<rect x="15" y="40" width="70" height="48" rx="6"/><path d="M32 40v-8a18 18 0 0 1 36 0v8" fill="none" stroke="currentColor" stroke-width="6"/>'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Self Service - Sipway Campus</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Poppins',sans-serif;background:#fff;color:#111}
nav{background:#1ba1e2;color:#fff;height:64px;display:flex;align-items:center;justify-content:space-between;padding:0 40px}
nav .l,nav .r{display:flex;align-items:center;gap:26px}
nav a{color:#fff;text-decoration:none;font-size:15px}
.brand{font-weight:600}
.user{display:flex;align-items:center;gap:10px;font-size:15px}
.user i{width:36px;height:36px;border-radius:50%;background:#fff;display:inline-block}
main{max-width:1450px;margin:0 auto;padding:30px 40px}
h2{font-size:22px;font-weight:600;margin-bottom:22px}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:15px;max-width:1300px}
.tile{display:flex;flex-direction:column;justify-content:flex-end;height:310px;border-radius:8px;text-decoration:none;color:#111;background:linear-gradient(#9fd0f5,#f4faff 85%);box-shadow:0 0 6px rgba(0,0,0,.25);transition:.25s;position:relative;overflow:hidden}
.tile:hover{transform:translateY(-6px);box-shadow:0 12px 24px rgba(27,161,226,.35)}
.tile svg{position:absolute;top:45px;left:50%;width:130px;height:130px;margin-left:-65px;color:#6366f1;fill:#6366f1}
.tile span{text-align:center;padding:14px;font-size:17px;font-weight:500}
</style>
</head>
<body>
<nav>
  <div class="l"><a class="brand" href="dashboard.php">&#9638; Smart HRIS</a><a href="../index.php">&#8630;</a><a href="dashboard.php">Home</a></div>
  <div class="r"><div class="user"><i></i>Hello, <?= e($name) ?></div><a href="logout.php">Logout</a></div>
</nav>
<main>
  <h2>Self Service</h2>
  <div class="grid">
    <?php foreach ($tiles as [$t,$href,$bg,$svg]): ?>
      <a class="tile" href="<?= $href ?>"><svg viewBox="0 0 100 100"><?= $svg ?></svg><span><?= e($t) ?></span></a>
    <?php endforeach; ?>
  </div>
</main>
</body>
</html>
