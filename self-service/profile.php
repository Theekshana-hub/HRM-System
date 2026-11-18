<?php
require 'config.php';
require_login();
$emp = find_employee($_SESSION['emp'][COL_EMP_NO]) ?? [];
unset($emp[COL_NIC]); // password column eka pennanne na
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Employee Profile</title>
<style>
body{font-family:Poppins,Arial,sans-serif;margin:0;background:#f5f7fa}
nav{background:#1ba1e2;height:64px;display:flex;align-items:center;padding:0 40px}
nav a{color:#fff;text-decoration:none}
.wrap{max-width:900px;margin:30px auto;background:#fff;border-radius:10px;padding:25px;box-shadow:0 2px 10px rgba(0,0,0,.1)}
table{width:100%;border-collapse:collapse}
td{padding:10px;border-bottom:1px solid #eee;font-size:14px}
td:first-child{font-weight:600;width:35%;text-transform:capitalize;color:#374151}
</style></head><body>
<nav><a href="dashboard.php">&larr; Back to Self Service</a></nav>
<div class="wrap"><h2>Employee Profile</h2>
<table>
<?php foreach ($emp as $k => $v): if ($v === null || $v === '') continue; ?>
<tr><td><?= e(str_replace('_',' ',$k)) ?></td><td><?= e($v) ?></td></tr>
<?php endforeach; ?>
</table></div></body></html>
