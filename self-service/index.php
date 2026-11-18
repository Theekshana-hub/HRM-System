<?php
require 'config.php';
if (!empty($_SESSION['emp'])) { header('Location: dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');
    if ($user === '' || $pass === '') {
        $error = 'Please enter username and password.';
    } else {
        try {
            $row = find_employee($user);
            if ($row && !array_key_exists(COL_NIC, $row)) {
                $error = 'Config error: NIC column "'.COL_NIC.'" not found in table.';
            } elseif ($row && $row[COL_NIC] !== null &&
                      hash_equals(strtoupper(trim($row[COL_NIC])), strtoupper($pass))) {
                session_regenerate_id(true);
                $_SESSION['emp'] = $row;
                header('Location: dashboard.php'); exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (Throwable $ex) {
            $error = 'Database connection error.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sipway Campus - Self Service Login</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Poppins',sans-serif;min-height:100vh;display:flex;background:#fff;overflow:hidden}
.left{flex:1;position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;background:#fff}
.left::before{content:'';position:absolute;left:-35%;top:-20%;width:100%;height:150%;background:#4f86f0;border-radius:0 100% 100% 0 / 0 70% 70% 0;z-index:0;clip-path:polygon(0 0,60% 0,100% 100%,0 100%)}
.left>*{position:relative;z-index:1}
.logo{font-size:46px;font-weight:700;background:linear-gradient(90deg,#e63946,#1d3557);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;letter-spacing:-1px}
.tag{color:#1d3557;font-size:15px;margin-bottom:30px}
.hero{width:300px;height:300px}
.right{flex:1;display:flex;align-items:center;justify-content:center;padding:30px}
.box{width:100%;max-width:450px;text-align:center}
.avatar{width:115px;height:115px;border-radius:50%;background:#e6ebff;margin:0 auto 20px;display:flex;align-items:center;justify-content:center}
.box h2{font-size:18px;font-weight:600;margin-bottom:35px}
.field{border-bottom:1.5px solid #ddd;margin-bottom:28px}
.field input{width:100%;border:0;outline:0;padding:14px 14px;font:500 17px 'Poppins',sans-serif;background:transparent}
.field input:focus{background:#eef2ff}
.field input::placeholder{color:#999}
.forgot{display:block;text-align:right;color:#888;font-size:15px;margin:-18px 0 22px;text-decoration:none}
.btn{width:100%;padding:16px;border:0;border-radius:40px;color:#fff;font:500 22px 'Poppins',sans-serif;cursor:pointer;background:linear-gradient(90deg,#14568a,#1a8fcb);transition:.2s}
.btn:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(20,86,138,.3)}
.err{background:#fde8ea;color:#b4232f;border-radius:8px;padding:10px;font-size:14px;margin-bottom:20px}
.foot{font-size:13px;margin-top:20px}
.back{display:inline-block;margin-top:14px;font-size:13px;color:#2563eb}
@media(max-width:850px){.left{display:none}}
</style>
</head>
<body>
<div class="left">
  <div class="logo">Sipway Campus</div>
  <div class="tag">Self Service Portal</div>
  <svg class="hero" viewBox="0 0 300 300" fill="none">
    <rect x="95" y="40" width="110" height="200" rx="16" fill="#fff" stroke="#1d2d6b" stroke-width="5"/>
    <rect x="108" y="95" width="84" height="70" rx="4" fill="#eef2ff"/>
    <rect x="108" y="70" width="84" height="16" rx="3" fill="#1d2d6b"/>
    <rect x="40" y="55" width="90" height="45" rx="10" fill="#3dbdff"/>
    <rect x="170" y="150" width="90" height="40" rx="10" fill="#3dbdff"/>
    <circle cx="190" cy="215" r="50" fill="#ff7b72" stroke="#ff5a4f" stroke-width="5"/>
    <path d="M190 215V180M190 215l18 22" stroke="#fff" stroke-width="5" stroke-linecap="round"/>
    <circle cx="80" cy="170" r="22" fill="#ff7b72"/><rect x="62" y="195" width="36" height="55" rx="8" fill="#ff7b72"/>
    <rect x="66" y="245" width="12" height="40" fill="#0f1a47"/><rect x="82" y="245" width="12" height="40" fill="#0f1a47"/>
  </svg>
</div>
<div class="right">
  <form class="box" method="post" autocomplete="off">
    <div class="avatar">
      <svg width="70" height="70" viewBox="0 0 70 70"><circle cx="35" cy="22" r="14" fill="#3b82f6"/><path d="M8 62a27 27 0 0 1 54 0z" fill="#3b82f6"/></svg>
    </div>
    <h2>Welcome to Self Service Portal</h2>
    <?php if ($error): ?><div class="err"><?= e($error) ?></div><?php endif; ?>
    <div class="field"><input type="text" name="username" placeholder="Username (Employee No)" value="<?= e($_POST['username'] ?? '') ?>" required autofocus></div>
    <div class="field"><input type="password" name="password" placeholder="Password (NIC No)" required></div>
    <a class="forgot" href="#" onclick="alert('Password eka oyage NIC number eka. Help ekata HR ekka contact karanna.');return false;">Forgot Password?</a>
    <button class="btn" type="submit">LOGIN</button>
    <div class="foot">Copyrights Sipway Campus &copy; 2024</div>
    <a class="back" href="../index.php">&larr; Back to Selection</a>
  </form>
</div>
</body>
</html>
