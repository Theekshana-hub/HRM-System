<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sipway Campus - Enterprise</title>
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
    background-image:
      radial-gradient(at 0% 0%, rgba(14, 165, 233, 0.05) 0px, transparent 50%),
      radial-gradient(at 100% 0%, rgba(45, 212, 191, 0.04) 0px, transparent 50%);
  }

  /* ========== NAVBAR ========== */
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

  /* ========== MAIN ========== */
  .main {
    padding: 32px 36px 50px;
    max-width: 1100px;
    margin: 0 auto;
  }

  .page-header {
    margin-bottom: 32px;
  }

  .page-title {
    font-size: 26px;
    font-weight: 800;
    color: var(--text);
    letter-spacing: -0.4px;
    margin-bottom: 4px;
  }

  .page-subtitle {
    font-size: 14.5px;
    color: var(--text-muted);
    font-weight: 500;
  }

  /* Section */
  .section {
    margin-bottom: 36px;
  }

  .section-title {
    display: inline-flex;
    align-items: center;
    background: linear-gradient(135deg, #14b8a6, #0d9488);
    color: white;
    font-size: 13.5px;
    font-weight: 700;
    padding: 8px 20px;
    border-radius: 50px;
    margin-bottom: 18px;
    box-shadow: 0 4px 14px rgba(20, 184, 166, 0.3);
    letter-spacing: 0.2px;
  }

  /* Menu Grid */
  .menu-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
  }

  .menu-card {
    background: var(--card);
    border-radius: 16px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 6px 18px rgba(14, 165, 233, 0.06);
    border: 1px solid rgba(255,255,255,0.9);
    text-decoration: none;
    transition: all 0.35s cubic-bezier(0.22, 1, 0.36, 1);
    cursor: pointer;
    position: relative;
    overflow: hidden;
  }

  .menu-card::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(180deg, #14b8a6, #0d9488);
    opacity: 0;
    transition: opacity 0.3s ease;
  }

  .menu-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 32px rgba(20, 184, 166, 0.15);
    border-color: rgba(20, 184, 166, 0.25);
  }

  .menu-card:hover::before {
    opacity: 1;
  }

  .menu-icon {
    width: 54px;
    height: 54px;
    border-radius: 16px;
    background: linear-gradient(145deg, #ccfbf1, #f0fdfa);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1);
  }

  .menu-card:hover .menu-icon {
    transform: scale(1.08);
  }

  .menu-icon svg {
    width: 28px;
    height: 28px;
  }

  .menu-label {
    font-size: 14.5px;
    font-weight: 600;
    color: var(--text);
    line-height: 1.35;
    letter-spacing: -0.2px;
  }

  /* Staggered animation */
  .menu-card {
    opacity: 0;
    animation: fadeUp 0.5s cubic-bezier(0.22, 1, 0.36, 1) forwards;
  }

  .section:nth-child(1) .menu-card:nth-child(1) { animation-delay: 0.05s; }
  .section:nth-child(1) .menu-card:nth-child(2) { animation-delay: 0.08s; }
  .section:nth-child(1) .menu-card:nth-child(3) { animation-delay: 0.11s; }
  .section:nth-child(1) .menu-card:nth-child(4) { animation-delay: 0.14s; }
  .section:nth-child(1) .menu-card:nth-child(5) { animation-delay: 0.17s; }
  .section:nth-child(1) .menu-card:nth-child(6) { animation-delay: 0.20s; }
  .section:nth-child(1) .menu-card:nth-child(7) { animation-delay: 0.23s; }
  .section:nth-child(2) .menu-card:nth-child(1) { animation-delay: 0.26s; }
  .section:nth-child(2) .menu-card:nth-child(2) { animation-delay: 0.29s; }
  .section:nth-child(2) .menu-card:nth-child(3) { animation-delay: 0.32s; }
  .section:nth-child(2) .menu-card:nth-child(4) { animation-delay: 0.35s; }
  .section:nth-child(2) .menu-card:nth-child(5) { animation-delay: 0.38s; }
  .section:nth-child(2) .menu-card:nth-child(6) { animation-delay: 0.41s; }
  .section:nth-child(2) .menu-card:nth-child(7) { animation-delay: 0.44s; }
  .section:nth-child(2) .menu-card:nth-child(8) { animation-delay: 0.47s; }
  .section:nth-child(2) .menu-card:nth-child(9) { animation-delay: 0.50s; }
  .section:nth-child(3) .menu-card:nth-child(1) { animation-delay: 0.53s; }

  @keyframes fadeUp {
    from {
      opacity: 0;
      transform: translateY(18px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* Responsive */
  @media (max-width: 900px) {
    .menu-grid {
      grid-template-columns: repeat(2, 1fr);
    }
    .main {
      padding: 24px 20px 50px;
    }
  }

  @media (max-width: 560px) {
    .menu-grid {
      grid-template-columns: 1fr;
    }
    .navbar {
      padding: 0 16px;
      height: 58px;
    }
    .page-title {
      font-size: 22px;
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
    <input type="text" placeholder="Search modules...">
  </div>
  <div class="nav-right">
    <span>Welcome <strong>Admin</strong></span>
    <button class="nav-btn" onclick="location.href='../index.php'" title="Logout">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
    </button>
  </div>
</nav>

<div class="main">

  <div class="page-header">
    <h1 class="page-title">Enterprise</h1>
    <p class="page-subtitle">Company hierarchy, rules & organizational setup</p>
  </div>

  <!-- Company Hierarchy -->
  <div class="section">
    <div class="section-title">Company Hierarchy</div>
    <div class="menu-grid">

      <a href="company-registration.php" class="menu-card">
        <div class="menu-icon">
          <svg viewBox="0 0 48 48" fill="none">
            <rect x="10" y="14" width="28" height="26" rx="2" fill="#0d9488"/>
            <rect x="14" y="18" width="8" height="6" rx="1" fill="#99f6e4"/>
            <rect x="26" y="18" width="8" height="6" rx="1" fill="#99f6e4"/>
            <rect x="14" y="28" width="8" height="6" rx="1" fill="#99f6e4"/>
            <rect x="26" y="28" width="8" height="6" rx="1" fill="#99f6e4"/>
            <circle cx="18" cy="10" r="5" fill="#14b8a6"/>
            <circle cx="30" cy="10" r="5" fill="#14b8a6"/>
          </svg>
        </div>
        <span class="menu-label">Company Registration</span>
      </a>

      <a href="division-registration.php" class="menu-card">
        <div class="menu-icon">
          <svg viewBox="0 0 48 48" fill="none">
            <rect x="8" y="20" width="12" height="16" rx="2" fill="#0d9488"/>
            <rect x="18" y="12" width="12" height="24" rx="2" fill="#14b8a6"/>
            <rect x="28" y="20" width="12" height="16" rx="2" fill="#0d9488"/>
            <line x1="14" y1="20" x2="24" y2="12" stroke="#0f766e" stroke-width="2"/>
            <line x1="34" y1="20" x2="24" y2="12" stroke="#0f766e" stroke-width="2"/>
          </svg>
        </div>
        <span class="menu-label">Division Registration</span>
      </a>

      <a href="sub-division-registration.php" class="menu-card">
        <div class="menu-icon">
          <svg viewBox="0 0 48 48" fill="none">
            <rect x="18" y="8" width="12" height="10" rx="2" fill="#0d9488"/>
            <rect x="8" y="28" width="10" height="10" rx="2" fill="#14b8a6"/>
            <rect x="19" y="28" width="10" height="10" rx="2" fill="#14b8a6"/>
            <rect x="30" y="28" width="10" height="10" rx="2" fill="#14b8a6"/>
            <line x1="24" y1="18" x2="24" y2="28" stroke="#0f766e" stroke-width="2"/>
            <line x1="13" y1="28" x2="24" y2="22" stroke="#0f766e" stroke-width="2"/>
            <line x1="35" y1="28" x2="24" y2="22" stroke="#0f766e" stroke-width="2"/>
          </svg>
        </div>
        <span class="menu-label">Sub Division Registration</span>
      </a>

      <a href="department-registration.php" class="menu-card">
        <div class="menu-icon">
          <svg viewBox="0 0 48 48" fill="none">
            <circle cx="24" cy="16" r="8" fill="#0d9488"/>
            <circle cx="12" cy="34" r="6" fill="#14b8a6"/>
            <circle cx="36" cy="34" r="6" fill="#14b8a6"/>
            <line x1="24" y1="24" x2="12" y2="28" stroke="#0f766e" stroke-width="2"/>
            <line x1="24" y1="24" x2="36" y2="28" stroke="#0f766e" stroke-width="2"/>
          </svg>
        </div>
        <span class="menu-label">Department Registration</span>
      </a>

      <a href="sub-department-registration.php" class="menu-card">
        <div class="menu-icon">
          <svg viewBox="0 0 48 48" fill="none">
            <circle cx="24" cy="12" r="6" fill="#0d9488"/>
            <circle cx="12" cy="28" r="5" fill="#14b8a6"/>
            <circle cx="24" cy="32" r="5" fill="#14b8a6"/>
            <circle cx="36" cy="28" r="5" fill="#14b8a6"/>
            <line x1="24" y1="18" x2="12" y2="23" stroke="#0f766e" stroke-width="2"/>
            <line x1="24" y1="18" x2="24" y2="27" stroke="#0f766e" stroke-width="2"/>
            <line x1="24" y1="18" x2="36" y2="23" stroke="#0f766e" stroke-width="2"/>
          </svg>
        </div>
        <span class="menu-label">Sub Department Registration</span>
      </a>

      <a href="section-registration.php" class="menu-card">
        <div class="menu-icon">
          <svg viewBox="0 0 48 48" fill="none">
            <rect x="8" y="12" width="14" height="24" rx="3" fill="#0d9488"/>
            <rect x="26" y="12" width="14" height="24" rx="3" fill="#14b8a6"/>
            <rect x="12" y="16" width="6" height="4" rx="1" fill="#99f6e4"/>
            <rect x="30" y="16" width="6" height="4" rx="1" fill="#ccfbf1"/>
          </svg>
        </div>
        <span class="menu-label">Section Registration</span>
      </a>

      <a href="sub-section-registration.php" class="menu-card">
        <div class="menu-icon">
          <svg viewBox="0 0 48 48" fill="none">
            <rect x="8" y="10" width="14" height="12" rx="2" fill="#0d9488"/>
            <rect x="26" y="10" width="14" height="12" rx="2" fill="#0d9488"/>
            <rect x="8" y="26" width="14" height="12" rx="2" fill="#14b8a6"/>
            <rect x="26" y="26" width="14" height="12" rx="2" fill="#14b8a6"/>
          </svg>
        </div>
        <span class="menu-label">Sub Section Registration</span>
      </a>

    </div>
  </div>

  <!-- Enterprise -->
  <div class="section">
    <div class="section-title">Enterprise</div>
    <div class="menu-grid">

      <a href="registration-years.php" class="menu-card">
        <div class="menu-icon">
          <svg viewBox="0 0 48 48" fill="none">
            <rect x="10" y="12" width="28" height="28" rx="3" fill="#0d9488"/>
            <rect x="10" y="12" width="28" height="10" rx="3" fill="#14b8a6"/>
            <text x="16" y="34" font-size="14" font-weight="bold" fill="white">01</text>
          </svg>
        </div>
        <span class="menu-label">Registration Years</span>
      </a>

      <a href="company-rules.php" class="menu-card">
        <div class="menu-icon">
          <svg viewBox="0 0 48 48" fill="none">
            <rect x="14" y="8" width="20" height="28" rx="2" fill="#0d9488"/>
            <rect x="18" y="14" width="12" height="2" fill="#99f6e4"/>
            <rect x="18" y="20" width="12" height="2" fill="#99f6e4"/>
            <rect x="18" y="26" width="8" height="2" fill="#99f6e4"/>
            <path d="M28 32l4 8 4-8h-8z" fill="#f59e0b"/>
          </svg>
        </div>
        <span class="menu-label">Company Rules</span>
      </a>

      <a href="#" class="menu-card" onclick="alert('Company Bank Account Details - coming soon')">
        <div class="menu-icon">
          <svg viewBox="0 0 48 48" fill="none">
            <rect x="8" y="18" width="32" height="20" rx="3" fill="#0d9488"/>
            <path d="M8 22h32" stroke="#14b8a6" stroke-width="3"/>
            <circle cx="24" cy="30" r="5" fill="#fbbf24"/>
            <text x="21" y="33" font-size="10" fill="#78350f" font-weight="bold">$</text>
            <path d="M14 18V14l10-6 10 6v4" stroke="#0d9488" stroke-width="2" fill="none"/>
          </svg>
        </div>
        <span class="menu-label">Company Bank Account Details</span>
      </a>

      <a href="#" class="menu-card" onclick="alert('Carder Budgetting - coming soon')">
        <div class="menu-icon">
          <svg viewBox="0 0 48 48" fill="none">
            <rect x="12" y="10" width="24" height="28" rx="2" fill="#0d9488"/>
            <rect x="16" y="16" width="16" height="3" rx="1" fill="#99f6e4"/>
            <rect x="16" y="22" width="12" height="3" rx="1" fill="#99f6e4"/>
            <circle cx="32" cy="32" r="8" fill="#14b8a6"/>
            <text x="28" y="36" font-size="10" fill="white" font-weight="bold">$</text>
          </svg>
        </div>
        <span class="menu-label">Carder Budgetting</span>
      </a>

      <a href="#" class="menu-card" onclick="alert('Customer Registration - coming soon')">
        <div class="menu-icon">
          <svg viewBox="0 0 48 48" fill="none">
            <rect x="8" y="12" width="32" height="24" rx="3" fill="#0d9488"/>
            <circle cx="20" cy="22" r="6" fill="#99f6e4"/>
            <rect x="28" y="18" width="8" height="2" rx="1" fill="#99f6e4"/>
            <rect x="28" y="24" width="6" height="2" rx="1" fill="#99f6e4"/>
          </svg>
        </div>
        <span class="menu-label">Customer Registration</span>
      </a>

      <a href="#" class="menu-card" onclick="alert('Company Location Registration - coming soon')">
        <div class="menu-icon">
          <svg viewBox="0 0 48 48" fill="none">
            <rect x="12" y="16" width="24" height="22" rx="2" fill="#0d9488"/>
            <rect x="16" y="20" width="6" height="5" rx="1" fill="#99f6e4"/>
            <rect x="26" y="20" width="6" height="5" rx="1" fill="#99f6e4"/>
            <rect x="16" y="28" width="6" height="5" rx="1" fill="#99f6e4"/>
            <path d="M24 8l4 8h-8l4-8z" fill="#ef4444"/>
            <circle cx="24" cy="10" r="3" fill="#fca5a5"/>
          </svg>
        </div>
        <span class="menu-label">Company Location Registration</span>
      </a>

      <a href="#" class="menu-card" onclick="alert('Customer Location Registration - coming soon')">
        <div class="menu-icon">
          <svg viewBox="0 0 48 48" fill="none">
            <circle cx="24" cy="18" r="10" fill="#0d9488"/>
            <circle cx="24" cy="16" r="5" fill="#99f6e4"/>
            <path d="M14 40c0-6 4-10 10-10s10 4 10 10" fill="#14b8a6"/>
            <circle cx="34" cy="14" r="6" fill="#ef4444"/>
            <circle cx="34" cy="14" r="2.5" fill="white"/>
          </svg>
        </div>
        <span class="menu-label">Customer Location Registration</span>
      </a>

      <a href="#" class="menu-card" onclick="alert('Create Grades - coming soon')">
        <div class="menu-icon">
          <svg viewBox="0 0 48 48" fill="none">
            <circle cx="24" cy="24" r="14" fill="#0d9488"/>
            <text x="17" y="30" font-size="16" font-weight="bold" fill="white">A+</text>
          </svg>
        </div>
        <span class="menu-label">Create Grades</span>
      </a>

      <a href="#" class="menu-card" onclick="alert('Lock Sites - coming soon')">
        <div class="menu-icon">
          <svg viewBox="0 0 48 48" fill="none">
            <rect x="12" y="22" width="24" height="18" rx="3" fill="#0d9488"/>
            <path d="M16 22v-6a8 8 0 0116 0v6" stroke="#14b8a6" stroke-width="3" fill="none"/>
            <circle cx="24" cy="31" r="3" fill="#fbbf24"/>
          </svg>
        </div>
        <span class="menu-label">Lock Sites</span>
      </a>

    </div>
  </div>

  <!-- Allowances -->
  <div class="section">
    <div class="section-title">Allowances</div>
    <div class="menu-grid">

      <a href="#" class="menu-card" onclick="alert('Customer Site Allowances - coming soon')">
        <div class="menu-icon">
          <svg viewBox="0 0 48 48" fill="none">
            <circle cx="24" cy="24" r="16" fill="#0d9488"/>
            <path d="M16 28c0-4 3-6 8-6s8 2 8 6" fill="#99f6e4"/>
            <rect x="18" y="14" width="12" height="8" rx="2" fill="#fbbf24"/>
            <text x="20" y="21" font-size="8" fill="#78350f" font-weight="bold">$</text>
          </svg>
        </div>
        <span class="menu-label">Customer Site Allowances</span>
      </a>

    </div>
  </div>

</div>

<script>
if (sessionStorage.getItem('hr_logged_in') !== 'true') {
}
</script>
</body>
</html>