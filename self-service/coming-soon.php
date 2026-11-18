<?php
require 'config.php';
require_login();
$m = $_GET['m'] ?? 'Page';
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($m) ?></title>
<style>body{font-family:Poppins,Arial,sans-serif;text-align:center;padding-top:120px;background:#f5f7fa}
a{color:#2563eb}</style></head><body>
<h1><?= e($m) ?></h1><p>Meka thawa hadala ivara na (coming soon).</p>
<a href="dashboard.php">&larr; Back</a></body></html>
