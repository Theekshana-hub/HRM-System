<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sipway Campus - HR Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
        }

        /* Top Navbar */
        .navbar {
            background: #1e293b;
            height: 52px;
            display: flex;
            align-items: center;
            padding: 0 20px;
            color: white;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .nav-left {
            display: flex;
            align-items: center;
            gap: 18px;
        }
        .nav-logo {
            font-weight: 700;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            letter-spacing: 0.3px;
        }
        .nav-logo .icon {
            width: 22px; height: 22px;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            border-radius: 4px;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px;
        }
        .nav-btn {
            background: transparent;
            border: none;
            color: #cbd5e1;
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            transition: all 0.2s;
        }
        .nav-btn:hover { background: rgba(255,255,255,0.1); color: white; }
        .nav-btn svg { width: 18px; height: 18px; }
        .support-dropdown {
            position: relative;
        }
        .support-btn {
            background: transparent;
            border: none;
            color: #cbd5e1;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 6px 10px;
            border-radius: 6px;
        }
        .support-btn:hover { background: rgba(255,255,255,0.1); color: white; }
        .search-box {
            flex: 1;
            max-width: 320px;
            margin: 0 20px;
        }
        .search-box input {
            width: 100%;
            padding: 7px 14px;
            border-radius: 20px;
            border: none;
            background: rgba(255,255,255,0.12);
            color: white;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            outline: none;
        }
        .search-box input::placeholder { color: #94a3b8; }
        .nav-right {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-left: auto;
        }
        .welcome-text {
            font-size: 13px;
            color: #e2e8f0;
        }
        .user-menu {
            position: relative;
        }
        .user-menu-btn {
            background: transparent;
            border: none;
            color: #cbd5e1;
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            display: flex;
            align-items: center;
        }
        .user-menu-btn:hover { background: rgba(255,255,255,0.1); color: white; }
        .dropdown-menu {
            position: absolute;
            top: 40px;
            right: 0;
            background: #1e293b;
            border-radius: 8px;
            min-width: 160px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
            display: none;
            overflow: hidden;
            z-index: 200;
        }
        .dropdown-menu.show { display: block; }
        .dropdown-menu a {
            display: block;
            padding: 11px 18px;
            color: #e2e8f0;
            text-decoration: none;
            font-size: 13px;
            transition: background 0.2s;
        }
        .dropdown-menu a:hover { background: rgba(255,255,255,0.1); }

        /* Main Content */
        .main-content {
            padding: 30px 40px;
            max-width: 1400px;
            margin: 0 auto;
        }
        .page-title {
            font-size: 22px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 25px;
        }

        /* Cards Grid */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 22px;
        }
        .module-card {
            background: #ffffff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid #e2e8f0;
            text-decoration: none;
            display: flex;
            flex-direction: column;
        }
        .module-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 28px rgba(59,130,246,0.15);
            border-color: #93c5fd;
        }
        .card-image {
            height: 160px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            background: linear-gradient(145deg, #eff6ff, #dbeafe);
            position: relative;
            overflow: hidden;
        }
        .card-image svg {
            width: 100%;
            height: 100%;
            max-width: 180px;
        }
        .card-footer {
            padding: 14px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: white;
            border-top: 1px solid #f1f5f9;
        }
        .card-title {
            font-size: 14px;
            font-weight: 500;
            color: #334155;
        }
        .card-arrow {
            width: 28px; height: 28px;
            background: #f1f5f9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        .module-card:hover .card-arrow {
            background: #3b82f6;
        }
        .module-card:hover .card-arrow svg {
            stroke: white;
        }
        .card-arrow svg {
            width: 14px; height: 14px;
            stroke: #64748b;
            transition: stroke 0.3s;
        }

        /* Responsive */
        @media (max-width: 1100px) {
            .cards-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 800px) {
            .cards-grid { grid-template-columns: repeat(2, 1fr); }
            .main-content { padding: 20px; }
            .search-box { display: none; }
        }
        @media (max-width: 500px) {
            .cards-grid { grid-template-columns: 1fr; }
            .welcome-text { display: none; }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-left">
            <div class="nav-logo">
                <div class="icon">S</div>
                SIPWAY CAMPUS
            </div>
            <button class="nav-btn" onclick="history.back()" title="Back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            </button>
            <button class="nav-btn" onclick="location.href='dashboard.php'" title="Home">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </button>
            <div class="support-dropdown">
                <button class="support-btn">
                    Support
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
            </div>
        </div>

        <div class="search-box">
            <input type="text" placeholder="Search modules...">
        </div>

        <div class="nav-right">
            <span class="welcome-text">Welcome <strong id="displayName">Admin</strong></span>
            <div class="user-menu">
                <button class="user-menu-btn" onclick="toggleMenu()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 01-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>
                    </svg>
                </button>
                <div class="dropdown-menu" id="userDropdown">
                    <a href="#" onclick="alert('Change Password feature coming soon')">Change Password</a>
                    <a href="#" onclick="logout()">Log Out</a>
                </div>
            </div>
            <button class="nav-btn" title="Language">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/>
                </svg>
            </button>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <h1 class="page-title">HR Panel</h1>

        <div class="cards-grid">

            <!-- 1. Manage Employee -->
            <a href="manage-employee/manage-employee.php" class="module-card">
                <div class="card-image">
                    <svg viewBox="0 0 200 160" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <ellipse cx="100" cy="140" rx="80" ry="15" fill="#bfdbfe" opacity="0.5"/>
                        <!-- Desk -->
                        <rect x="30" y="100" width="140" height="8" rx="2" fill="#93c5fd"/>
                        <!-- Person 1 - standing with laptop -->
                        <circle cx="70" cy="55" r="14" fill="#1e40af"/>
                        <rect x="58" y="70" width="24" height="30" rx="4" fill="#3b82f6"/>
                        <rect x="50" y="75" width="12" height="8" rx="2" fill="#60a5fa"/>
                        <rect x="80" y="78" width="18" height="12" rx="2" fill="#1e293b"/>
                        <!-- Person 2 - sitting -->
                        <circle cx="130" cy="70" r="12" fill="#f97316"/>
                        <rect x="120" y="82" width="20" height="22" rx="3" fill="#ea580c"/>
                        <rect x="115" y="95" width="30" height="6" rx="2" fill="#94a3b8"/>
                        <!-- Gear -->
                        <circle cx="160" cy="40" r="18" stroke="#60a5fa" stroke-width="3" fill="none"/>
                        <circle cx="160" cy="40" r="8" fill="#93c5fd"/>
                        <!-- Small people -->
                        <circle cx="45" cy="90" r="6" fill="#8b5cf6"/>
                        <rect x="40" y="96" width="10" height="10" rx="2" fill="#7c3aed"/>
                        <circle cx="155" cy="95" r="5" fill="#10b981"/>
                        <rect x="151" y="100" width="8" height="8" rx="1" fill="#059669"/>
                        <!-- Plant -->
                        <rect x="25" y="85" width="8" height="15" rx="1" fill="#78716c"/>
                        <ellipse cx="29" cy="80" rx="10" ry="12" fill="#22c55e"/>
                    </svg>
                </div>
                <div class="card-footer">
                    <span class="card-title">Manage Employee</span>
                    <div class="card-arrow">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
                    </div>
                </div>
            </a>

            <!-- 2. Enterprise -->
            <a href="enterprise/index.php" class="module-card" onclick="alert('Enterprise module coming soon')">
                <div class="card-image">
                    <svg viewBox="0 0 200 160" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <ellipse cx="100" cy="145" rx="75" ry="12" fill="#bfdbfe" opacity="0.4"/>
                        <!-- Big monitor -->
                        <rect x="40" y="25" width="120" height="80" rx="6" fill="#1e40af"/>
                        <rect x="48" y="33" width="104" height="58" rx="3" fill="#dbeafe"/>
                        <!-- Charts on screen -->
                        <rect x="55" y="40" width="25" height="20" rx="2" fill="#93c5fd"/>
                        <circle cx="67" cy="50" r="8" fill="#3b82f6" opacity="0.5"/>
                        <rect x="88" y="55" width="8" height="25" rx="1" fill="#f59e0b"/>
                        <rect x="100" y="45" width="8" height="35" rx="1" fill="#3b82f6"/>
                        <rect x="112" y="50" width="8" height="30" rx="1" fill="#10b981"/>
                        <rect x="124" y="40" width="8" height="40" rx="1" fill="#8b5cf6"/>
                        <circle cx="145" cy="50" r="10" fill="#f97316" opacity="0.6"/>
                        <circle cx="145" cy="50" r="6" fill="#fb923c"/>
                        <!-- People -->
                        <circle cx="55" cy="120" r="10" fill="#e11d48"/>
                        <rect x="47" y="130" width="16" height="18" rx="3" fill="#be123c"/>
                        <circle cx="90" cy="115" r="10" fill="#3b82f6"/>
                        <rect x="82" y="125" width="16" height="20" rx="3" fill="#2563eb"/>
                        <circle cx="130" cy="118" r="10" fill="#f59e0b"/>
                        <rect x="122" y="128" width="16" height="18" rx="3" fill="#d97706"/>
                        <circle cx="160" cy="122" r="9" fill="#8b5cf6"/>
                        <rect x="153" y="131" width="14" height="16" rx="3" fill="#7c3aed"/>
                    </svg>
                </div>
                <div class="card-footer">
                    <span class="card-title">Enterprise</span>
                    <div class="card-arrow">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
                    </div>
                </div>
            </a>

            <!-- 3. Attendance Management -->
            <a href="#" class="module-card" onclick="alert('Attendance Management module coming soon')">
                <div class="card-image">
                    <svg viewBox="0 0 200 160" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <ellipse cx="100" cy="145" rx="70" ry="12" fill="#bfdbfe" opacity="0.4"/>
                        <!-- Phone / Tablet -->
                        <rect x="70" y="20" width="90" height="120" rx="10" fill="#1e40af"/>
                        <rect x="78" y="30" width="74" height="100" rx="4" fill="#eff6ff"/>
                        <!-- Calendar grid -->
                        <rect x="85" y="40" width="60" height="50" rx="3" fill="white" stroke="#93c5fd" stroke-width="1"/>
                        <line x1="85" y1="52" x2="145" y2="52" stroke="#93c5fd" stroke-width="1"/>
                        <line x1="105" y1="40" x2="105" y2="90" stroke="#dbeafe" stroke-width="1"/>
                        <line x1="125" y1="40" x2="125" y2="90" stroke="#dbeafe" stroke-width="1"/>
                        <line x1="85" y1="65" x2="145" y2="65" stroke="#dbeafe" stroke-width="1"/>
                        <line x1="85" y1="78" x2="145" y2="78" stroke="#dbeafe" stroke-width="1"/>
                        <circle cx="95" cy="58" r="3" fill="#3b82f6"/>
                        <circle cx="115" cy="71" r="3" fill="#10b981"/>
                        <circle cx="135" cy="58" r="3" fill="#f59e0b"/>
                        <!-- Clock -->
                        <circle cx="50" cy="60" r="25" fill="#dbeafe" stroke="#3b82f6" stroke-width="3"/>
                        <circle cx="50" cy="60" r="3" fill="#1e40af"/>
                        <line x1="50" y1="60" x2="50" y2="45" stroke="#1e40af" stroke-width="2.5" stroke-linecap="round"/>
                        <line x1="50" y1="60" x2="62" y2="60" stroke="#3b82f6" stroke-width="2" stroke-linecap="round"/>
                        <!-- Person pointing -->
                        <circle cx="155" cy="100" r="12" fill="#f97316"/>
                        <rect x="145" y="112" width="20" height="25" rx="4" fill="#ea580c"/>
                        <line x1="145" y1="120" x2="130" y2="100" stroke="#f97316" stroke-width="4" stroke-linecap="round"/>
                        <!-- Plant -->
                        <rect x="30" y="120" width="10" height="14" rx="1" fill="#78716c"/>
                        <ellipse cx="35" cy="112" rx="12" ry="14" fill="#22c55e"/>
                    </svg>
                </div>
                <div class="card-footer">
                    <span class="card-title">Attendance Management</span>
                    <div class="card-arrow">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
                    </div>
                </div>
            </a>

            <!-- 4. Salary Processing -->
            <a href="#" class="module-card" onclick="alert('Salary Processing module coming soon')">
                <div class="card-image">
                    <svg viewBox="0 0 200 160" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <ellipse cx="100" cy="145" rx="70" ry="12" fill="#bfdbfe" opacity="0.4"/>
                        <!-- Money bag -->
                        <ellipse cx="55" cy="90" rx="30" ry="35" fill="#fbbf24"/>
                        <path d="M40 60 Q55 45 70 60" stroke="#f59e0b" stroke-width="4" fill="none"/>
                        <text x="48" y="98" font-size="28" font-weight="bold" fill="#92400e">$</text>
                        <!-- Document / Loan card -->
                        <rect x="100" y="30" width="70" height="90" rx="6" fill="#1e40af"/>
                        <rect x="108" y="40" width="54" height="12" rx="2" fill="#3b82f6"/>
                        <text x="115" y="50" font-size="9" fill="white" font-weight="600">LOAN</text>
                        <rect x="108" y="58" width="40" height="5" rx="1" fill="#60a5fa"/>
                        <rect x="108" y="68" width="50" height="5" rx="1" fill="#60a5fa"/>
                        <rect x="108" y="78" width="35" height="5" rx="1" fill="#60a5fa"/>
                        <rect x="108" y="88" width="45" height="5" rx="1" fill="#60a5fa"/>
                        <rect x="108" y="100" width="30" height="10" rx="2" fill="#f59e0b"/>
                        <!-- Calendar icon -->
                        <rect x="155" y="25" width="25" height="22" rx="3" fill="#ef4444"/>
                        <rect x="157" y="32" width="21" height="13" rx="1" fill="white"/>
                        <!-- Percentage -->
                        <circle cx="175" cy="70" r="12" fill="#10b981"/>
                        <text x="169" y="75" font-size="12" fill="white" font-weight="bold">%</text>
                        <!-- People -->
                        <circle cx="90" cy="120" r="8" fill="#3b82f6"/>
                        <rect x="84" y="128" width="12" height="12" rx="2" fill="#2563eb"/>
                        <circle cx="130" cy="125" r="8" fill="#8b5cf6"/>
                        <rect x="124" y="133" width="12" height="12" rx="2" fill="#7c3aed"/>
                    </svg>
                </div>
                <div class="card-footer">
                    <span class="card-title">Salary Processing</span>
                    <div class="card-arrow">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
                    </div>
                </div>
            </a>

            <!-- 5. Reports -->
            <a href="#" class="module-card" onclick="alert('Reports module coming soon')">
                <div class="card-image">
                    <svg viewBox="0 0 200 160" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <ellipse cx="100" cy="145" rx="70" ry="12" fill="#bfdbfe" opacity="0.4"/>
                        <!-- Folder -->
                        <path d="M40 50 L40 120 Q40 128 48 128 L150 128 Q158 128 158 120 L158 60 Q158 52 150 52 L90 52 L80 40 L48 40 Q40 40 40 48 Z" fill="#f59e0b"/>
                        <path d="M40 60 L158 60 L158 120 Q158 128 150 128 L48 128 Q40 128 40 120 Z" fill="#fbbf24"/>
                        <!-- Documents -->
                        <rect x="60" y="70" width="50" height="40" rx="3" fill="white" opacity="0.9"/>
                        <rect x="65" y="78" width="30" height="3" rx="1" fill="#93c5fd"/>
                        <rect x="65" y="85" width="40" height="3" rx="1" fill="#bfdbfe"/>
                        <rect x="65" y="92" width="25" height="3" rx="1" fill="#bfdbfe"/>
                        <!-- Magnifying glass -->
                        <circle cx="130" cy="55" r="20" stroke="#8b5cf6" stroke-width="4" fill="none"/>
                        <line x1="145" y1="70" x2="160" y2="85" stroke="#8b5cf6" stroke-width="5" stroke-linecap="round"/>
                        <!-- Charts floating -->
                        <rect x="145" y="95" width="30" height="20" rx="3" fill="#3b82f6" opacity="0.8"/>
                        <rect x="149" y="105" width="5" height="8" fill="#93c5fd"/>
                        <rect x="157" y="100" width="5" height="13" fill="#60a5fa"/>
                        <rect x="165" y="102" width="5" height="11" fill="#93c5fd"/>
                        <!-- Person -->
                        <circle cx="55" cy="115" r="9" fill="#e11d48"/>
                        <rect x="48" y="124" width="14" height="15" rx="3" fill="#be123c"/>
                    </svg>
                </div>
                <div class="card-footer">
                    <span class="card-title">Reports</span>
                    <div class="card-arrow">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
                    </div>
                </div>
            </a>

            <!-- 6. Self Service Portal -->
            <a href="#" class="module-card" onclick="alert('Self Service Portal module coming soon')">
                <div class="card-image">
                    <svg viewBox="0 0 200 160" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <ellipse cx="100" cy="145" rx="70" ry="12" fill="#bfdbfe" opacity="0.4"/>
                        <!-- Kiosk / Standee screen -->
                        <rect x="75" y="20" width="55" height="90" rx="6" fill="#0ea5e9"/>
                        <rect x="82" y="28" width="41" height="70" rx="3" fill="#e0f2fe"/>
                        <rect x="90" y="40" width="25" height="4" rx="1" fill="#38bdf8"/>
                        <rect x="90" y="50" width="25" height="4" rx="1" fill="#7dd3fc"/>
                        <rect x="90" y="60" width="18" height="4" rx="1" fill="#7dd3fc"/>
                        <rect x="95" y="75" width="15" height="12" rx="2" fill="#0ea5e9"/>
                        <!-- Stand base -->
                        <rect x="95" y="110" width="15" height="20" fill="#64748b"/>
                        <ellipse cx="102" cy="132" rx="25" ry="6" fill="#475569"/>
                        <!-- Person interacting -->
                        <circle cx="145" cy="70" r="14" fill="#f97316"/>
                        <rect x="133" y="85" width="24" height="30" rx="4" fill="#ea580c"/>
                        <line x1="133" y1="95" x2="115" y2="80" stroke="#f97316" stroke-width="4" stroke-linecap="round"/>
                        <!-- Decorative circles -->
                        <circle cx="45" cy="50" r="15" fill="#a5b4fc" opacity="0.4"/>
                        <circle cx="50" cy="100" r="10" fill="#c4b5fd" opacity="0.4"/>
                    </svg>
                </div>
                <div class="card-footer">
                    <span class="card-title">Self Service Portal</span>
                    <div class="card-arrow">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
                    </div>
                </div>
            </a>

            <!-- 7. Work Flow Hierarchy -->
            <a href="#" class="module-card" onclick="alert('Work Flow Hierarchy module coming soon')">
                <div class="card-image">
                    <svg viewBox="0 0 200 160" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <ellipse cx="100" cy="145" rx="70" ry="12" fill="#bfdbfe" opacity="0.4"/>
                        <!-- Circular arrows -->
                        <circle cx="100" cy="75" r="40" stroke="#3b82f6" stroke-width="6" fill="none" stroke-dasharray="20 8"/>
                        <path d="M100 35 L110 45 L90 45 Z" fill="#3b82f6"/>
                        <path d="M135 100 L125 90 L125 110 Z" fill="#3b82f6"/>
                        <!-- Center people -->
                        <circle cx="85" cy="70" r="12" fill="#e11d48"/>
                        <rect x="76" y="82" width="18" height="18" rx="3" fill="#be123c"/>
                        <circle cx="120" cy="75" r="11" fill="#3b82f6"/>
                        <rect x="112" y="86" width="16" height="16" rx="3" fill="#2563eb"/>
                        <!-- Side icons -->
                        <circle cx="40" cy="60" r="14" fill="#dbeafe" stroke="#3b82f6" stroke-width="2"/>
                        <rect x="34" y="54" width="12" height="8" rx="1" fill="#3b82f6"/>
                        <circle cx="160" cy="55" r="14" fill="#dbeafe" stroke="#3b82f6" stroke-width="2"/>
                        <circle cx="160" cy="55" r="5" fill="#3b82f6"/>
                        <circle cx="45" cy="110" r="12" fill="#dbeafe" stroke="#10b981" stroke-width="2"/>
                        <path d="M40 110 L45 115 L52 105" stroke="#10b981" stroke-width="2" fill="none"/>
                        <circle cx="160" cy="110" r="12" fill="#dbeafe" stroke="#f59e0b" stroke-width="2"/>
                        <rect x="155" y="105" width="10" height="10" rx="1" fill="#f59e0b"/>
                        <!-- Connecting lines -->
                        <line x1="54" y1="60" x2="73" y2="68" stroke="#93c5fd" stroke-width="2"/>
                        <line x1="146" y1="55" x2="131" y2="68" stroke="#93c5fd" stroke-width="2"/>
                    </svg>
                </div>
                <div class="card-footer">
                    <span class="card-title">Work Flow Hierarchy</span>
                    <div class="card-arrow">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
                    </div>
                </div>
            </a>

        </div>
    </div>

    <script>
        // Check login
        if (sessionStorage.getItem('hr_logged_in') !== 'true') {
            window.location.href = 'index.php';
        }

        // Display username
        const username = sessionStorage.getItem('hr_username') || 'Admin';
        document.getElementById('displayName').textContent = username.charAt(0).toUpperCase() + username.slice(1);

        function toggleMenu() {
            document.getElementById('userDropdown').classList.toggle('show');
        }

        function logout() {
            sessionStorage.removeItem('hr_logged_in');
            sessionStorage.removeItem('hr_username');
            window.location.href = 'index.php';
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const menu = document.getElementById('userDropdown');
            const btn = document.querySelector('.user-menu-btn');
            if (!btn.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('show');
            }
        });
    </script>
</body>
</html>
