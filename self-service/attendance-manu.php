<?php
require 'attendance-lib.php';
require_login();
$emp = $_SESSION['emp'];
$navName = $emp['name_with_initials'] ?? $emp['full_name'] ?? $emp[COL_EMP_NO];
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Attendance</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<?php att_css(); ?></head><body>
<?php nav_bar($navName); ?>
<div class="wrap">
  <div class="cards">
    <a class="mcard" href="attendance-display.php">
      <i><svg width="54" height="54" viewBox="0 0 54 54"><rect x="4" y="6" width="46" height="32" rx="3" fill="#fff"/><rect x="12" y="24" width="5" height="10" fill="#f59e0b"/><rect x="21" y="18" width="5" height="16" fill="#22c55e"/><rect x="30" y="12" width="5" height="22" fill="#3b82f6"/><rect x="16" y="42" width="22" height="5" rx="2" fill="#fff"/></svg></i>
      <span>Attendance Display</span>
    </a>
    <a class="mcard" href="subordinate-attendance.php">
      <i><svg width="54" height="54" viewBox="0 0 54 54"><rect x="4" y="8" width="46" height="32" rx="3" fill="#1f2937"/><rect x="8" y="12" width="38" height="24" fill="#9ec5e8"/><rect x="18" y="44" width="18" height="3" rx="1" fill="#1f2937"/></svg></i>
      <span>Subordinate Attendance Display</span>
    </a>
  </div>
</div></body></html>