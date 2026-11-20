<?php
require 'config.php';
require_login();
$emp  = $_SESSION['emp'];
$name = $emp['name_with_initials'] ?? $emp['full_name'] ?? $emp[COL_EMP_NO];

$tiles = [
  [
    'title' => 'Employee Profile',
    'link'  => 'profile.php',
    'img'   => 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png',
    'color' => '#3b82f6'
  ],
  [
    'title' => 'Attendance Display',
    'link'  => 'attendance-display.php',
    'img'   => 'https://cdn-icons-png.flaticon.com/512/942/942748.png',
    'color' => '#0ea5e9'
  ],
  [
    'title' => 'Leave Application',
    'link'  => 'leave-application.php',
    'img'   => 'https://cdn-icons-png.flaticon.com/512/3652/3652191.png',
    'color' => '#8b5cf6'
  ],
  [
    'title' => 'Apply Manual Attendance',
    'link'  => 'manual-attendance.php',
    'img'   => 'https://cdn-icons-png.flaticon.com/512/2921/2921222.png',
    'color' => '#ec4899'
  ],
  [
    'title' => 'Notification Center',
    'link'  => 'coming-soon.php?m=Notification+Center',
    'img'   => 'https://cdn-icons-png.flaticon.com/512/1827/1827349.png',
    'color' => '#f59e0b'
  ],
  [
    'title' => 'Salary Details',
    'link'  => 'coming-soon.php?m=Salary+Details',
    'img'   => 'https://cdn-icons-png.flaticon.com/512/3135/3135706.png',
    'color' => '#10b981'
  ],
  [
    'title' => 'Log Out',
    'link'  => 'logout.php',
    'img'   => 'https://cdn-icons-png.flaticon.com/512/1828/1828479.png',
    'color' => '#ef4444'
  ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Self Service - Sipway Campus</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --primary: #0ea5e9;
    --bg: #f1f5f9;
    --card: #ffffff;
    --text: #0f172a;
    --text-muted: #64748b;
  }

  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    background-image: 
      radial-gradient(at 0% 0%, rgba(14, 165, 233, 0.07) 0px, transparent 50%),
      radial-gradient(at 100% 0%, rgba(56, 189, 248, 0.05) 0px, transparent 50%);
  }

  /* ========== NAVBAR ========== */
  nav {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(16px);
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 40px;
    border-bottom: 1px solid rgba(14, 165, 233, 0.1);
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
  }

  nav .l, nav .r {
    display: flex;
    align-items: center;
    gap: 28px;
  }

  nav a {
    color: var(--text-muted);
    text-decoration: none;
    font-size: 14.5px;
    font-weight: 500;
    transition: color 0.25s ease;
  }

  nav a:hover {
    color: var(--primary);
  }

  .brand {
    font-weight: 800;
    font-size: 18px;
    color: var(--text) !important;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .brand span {
    background: linear-gradient(135deg, #0ea5e9, #38bdf8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  .user {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    font-weight: 600;
    background: #f1f5f9;
    padding: 5px 14px 5px 5px;
    border-radius: 50px;
  }

  .user i {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0ea5e9, #38bdf8);
    display: inline-block;
  }

  .logout-btn {
    background: linear-gradient(135deg, #ef4444, #f87171);
    color: white !important;
    padding: 8px 18px;
    border-radius: 10px;
    font-weight: 600 !important;
    font-size: 13.5px !important;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
    transition: all 0.3s ease !important;
  }

  .logout-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(239, 68, 68, 0.35);
  }

  /* ========== MAIN ========== */
  main {
    max-width: 1280px;
    margin: 0 auto;
    padding: 40px 30px 60px;
  }

  .header {
    margin-bottom: 36px;
  }

  .header h2 {
    font-size: 30px;
    font-weight: 800;
    letter-spacing: -0.5px;
    margin-bottom: 6px;
  }

  .header p {
    color: var(--text-muted);
    font-size: 15px;
    font-weight: 500;
  }

  /* ========== GRID ========== */
  .grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 22px;
  }

  /* ========== TILE ========== */
  .tile {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 260px;
    border-radius: 20px;
    text-decoration: none;
    color: var(--text);
    background: var(--card);
    box-shadow: 0 8px 25px rgba(14, 165, 233, 0.1);
    transition: all 0.4s cubic-bezier(0.22, 1, 0.36, 1);
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.9);
  }

  .tile::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: var(--tile-color);
  }

  .tile:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(14, 165, 233, 0.18);
  }

  .tile .img-box {
    width: 100px;
    height: 100px;
    border-radius: 24px;
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    transition: all 0.4s ease;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
  }

  .tile:hover .img-box {
    transform: scale(1.1) rotate(3deg);
    background: #f0f9ff;
  }

  .tile img {
    width: 58px;
    height: 58px;
    object-fit: contain;
    transition: transform 0.4s ease;
  }

  .tile:hover img {
    transform: scale(1.08);
  }

  .tile .label {
    font-size: 16px;
    font-weight: 700;
    text-align: center;
    padding: 0 16px;
    letter-spacing: -0.2px;
  }

  /* Animation */
  .tile {
    opacity: 0;
    animation: fadeUp 0.55s cubic-bezier(0.22, 1, 0.36, 1) forwards;
  }

  .tile:nth-child(1) { animation-delay: 0.05s; }
  .tile:nth-child(2) { animation-delay: 0.10s; }
  .tile:nth-child(3) { animation-delay: 0.15s; }
  .tile:nth-child(4) { animation-delay: 0.20s; }
  .tile:nth-child(5) { animation-delay: 0.25s; }
  .tile:nth-child(6) { animation-delay: 0.30s; }
  .tile:nth-child(7) { animation-delay: 0.35s; }

  @keyframes fadeUp {
    from {
      opacity: 0;
      transform: translateY(28px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* ========== RESPONSIVE ========== */
  @media (max-width: 900px) {
    nav {
      padding: 0 20px;
    }

    main {
      padding: 30px 20px 50px;
    }

    .header h2 {
      font-size: 26px;
    }

    .grid {
      grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
      gap: 18px;
    }

    .tile {
      height: 240px;
    }
  }

  @media (max-width: 600px) {
    nav {
      height: 62px;
      padding: 0 14px;
    }

    nav .l, nav .r {
      gap: 14px;
    }

    .brand {
      font-size: 15px;
    }

    .user span {
      display: none;
    }

    .header h2 {
      font-size: 23px;
    }

    .grid {
      grid-template-columns: 1fr 1fr;
      gap: 14px;
    }

    .tile {
      height: 200px;
      border-radius: 16px;
    }

    .tile .img-box {
      width: 78px;
      height: 78px;
      border-radius: 18px;
      margin-bottom: 14px;
    }

    .tile img {
      width: 44px;
      height: 44px;
    }

    .tile .label {
      font-size: 13.5px;
      padding: 0 10px;
    }
  }

  @media (max-width: 400px) {
    .grid {
      grid-template-columns: 1fr;
    }

    .tile {
      height: 210px;
    }
  }
</style>
</head>
<body>

<nav>
  <div class="l">
    <a class="brand" href="dashboard.php">
      &#9638; <span>Smart HRIS</span>
    </a>
    <a href="../index.php">&#8630; Back</a>
    <a href="dashboard.php">Home</a>
  </div>
  <div class="r">
    <div class="user">
      <i></i>
      <span>Hello, <?= e($name) ?></span>
    </div>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>
</nav>

<main>
  <div class="header">
    <h2>Self Service</h2>
    <p>Quick access to your personal HR tools</p>
  </div>

  <div class="grid">
    <?php foreach ($tiles as $tile): ?>
      <a class="tile" href="<?= $tile['link'] ?>" style="--tile-color: <?= $tile['color'] ?>">
        <div class="img-box">
          <img src="<?= $tile['img'] ?>" alt="<?= e($tile['title']) ?>">
        </div>
        <div class="label"><?= e($tile['title']) ?></div>
      </a>
    <?php endforeach; ?>
  </div>
</main>

</body>
</html>