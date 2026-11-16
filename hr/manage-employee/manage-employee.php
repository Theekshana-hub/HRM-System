<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sipway Campus - Manage Employee</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8fafc;
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
            gap: 16px;
        }
        .nav-logo {
            font-weight: 700;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            letter-spacing: 0.3px;
            color: white;
            text-decoration: none;
        }
        .nav-logo .icon {
            width: 22px; height: 22px;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            border-radius: 4px;
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 700;
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
            max-width: 280px;
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
        .welcome-text { font-size: 13px; color: #e2e8f0; }
        .user-menu { position: relative; }
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
            padding: 30px 50px;
            max-width: 1200px;
        }

        /* Section */
        .section {
            margin-bottom: 32px;
        }
        .section-title {
            display: inline-block;
            background: #38bdf8;
            color: white;
            font-size: 14px;
            font-weight: 500;
            padding: 8px 22px;
            border-radius: 25px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(56, 189, 248, 0.3);
        }

        /* Menu Cards Grid */
        .menu-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
        }
        .menu-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 18px 22px;
            display: flex;
            align-items: center;
            gap: 16px;
            min-width: 280px;
            max-width: 340px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            text-decoration: none;
            transition: all 0.25s ease;
            cursor: pointer;
        }
        .menu-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.12);
            border-color: #93c5fd;
        }
        .menu-icon {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .menu-icon svg {
            width: 28px;
            height: 28px;
        }
        .menu-label {
            font-size: 14px;
            font-weight: 500;
            color: #1e3a8a;
            line-height: 1.3;
        }

        /* Icon background colors */
        .bg-blue { background: #dbeafe; }
        .bg-cyan { background: #cffafe; }
        .bg-indigo { background: #e0e7ff; }
        .bg-teal { background: #ccfbf1; }
        .bg-sky { background: #e0f2fe; }

        /* Responsive */
        @media (max-width: 800px) {
            .main-content { padding: 20px; }
            .search-box { display: none; }
            .menu-card { min-width: 100%; max-width: 100%; }
        }
        @media (max-width: 500px) {
            .welcome-text { display: none; }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-left">
            <a href="../dashboard.php" class="nav-logo">
                <div class="icon">S</div>
                SIPWAY CAMPUS
            </a>
            <button class="nav-btn" onclick="history.back()" title="Back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            </button>
            <button class="nav-btn" onclick="location.href='../dashboard.php'" title="Home">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </button>
            <button class="support-btn">
                Support
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
        </div>

        <div class="search-box">
            <input type="text" placeholder="Search...">
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

        <!-- Section 1: Manage Employee -->
        <div class="section">
            <div class="section-title">Manage Employee</div>
            <div class="menu-grid">

                <a href="employee-add-edit.php" class="menu-card">
                    <div class="menu-icon bg-blue">
                        <svg viewBox="0 0 48 48" fill="none">
                            <circle cx="24" cy="16" r="10" fill="#3b82f6"/>
                            <path d="M8 42c0-8 7-12 16-12s16 4 16 12" fill="#3b82f6"/>
                            <rect x="18" y="20" width="12" height="3" rx="1" fill="#1e40af"/>
                        </svg>
                    </div>
                    <span class="menu-label">Employee (Add / Edit)</span>
                </a>

                <a href="employee-transfer.php" class="menu-card" >
                    <div class="menu-icon bg-cyan">
                        <svg viewBox="0 0 48 48" fill="none">
                            <path d="M8 20h20l-4-6" stroke="#0ea5e9" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                            <path d="M40 28H20l4 6" stroke="#0284c7" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                            <circle cx="34" cy="18" r="5" fill="#38bdf8"/>
                            <circle cx="14" cy="32" r="5" fill="#0ea5e9"/>
                        </svg>
                    </div>
                    <span class="menu-label">Employee Transfer and Lifecycle</span>
                </a>

                <a href="employee-nic-search.php" class="menu-card" >
                    <div class="menu-icon bg-indigo">
                        <svg viewBox="0 0 48 48" fill="none">
                            <rect x="10" y="8" width="22" height="28" rx="3" fill="#6366f1"/>
                            <rect x="14" y="14" width="14" height="3" rx="1" fill="#c7d2fe"/>
                            <rect x="14" y="20" width="10" height="2.5" rx="1" fill="#a5b4fc"/>
                            <rect x="14" y="25" width="12" height="2.5" rx="1" fill="#a5b4fc"/>
                            <circle cx="32" cy="32" r="10" stroke="#4f46e5" stroke-width="3" fill="#e0e7ff"/>
                            <line x1="39" y1="39" x2="44" y2="44" stroke="#4f46e5" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <span class="menu-label">Employee NIC Number Search</span>
                </a>

            </div>
        </div>

        <!-- Section 2: Employee Life Cycle Management -->
        <div class="section">
            <div class="section-title">Employee Life Cycle Management</div>
            <div class="menu-grid">

                <a href="employee-lifecycle.php" class="menu-card">
                    <div class="menu-icon bg-teal">
                        <svg viewBox="0 0 48 48" fill="none">
                            <circle cx="18" cy="28" r="8" fill="#14b8a6"/>
                            <circle cx="32" cy="18" r="8" fill="#2dd4bf"/>
                            <path d="M18 20v-6M32 26v6" stroke="#0f766e" stroke-width="2.5" stroke-linecap="round"/>
                            <path d="M24 24l4-4" stroke="#0d9488" stroke-width="2.5" stroke-linecap="round"/>
                            <circle cx="18" cy="28" r="3" fill="white"/>
                            <circle cx="32" cy="18" r="3" fill="white"/>
                        </svg>
                    </div>
                    <span class="menu-label">Employee Lifecycle Detail View</span>
                </a>

            </div>
        </div>

        <!-- Section 3: Master Setup -->
        <div class="section">
            <div class="section-title">Master Setup</div>
            <div class="menu-grid">

                <a href="#" class="menu-card" >
                    <div class="menu-icon bg-sky">
                        <svg viewBox="0 0 48 48" fill="none">
                            <rect x="8" y="14" width="32" height="24" rx="3" fill="#0ea5e9"/>
                            <rect x="12" y="18" width="24" height="14" rx="2" fill="#e0f2fe"/>
                            <circle cx="24" cy="10" r="6" fill="#0284c7"/>
                            <path d="M24 7v6M21 10h6" stroke="white" stroke-width="2" stroke-linecap="round"/>
                            <!-- Gear teeth hint -->
                            <circle cx="24" cy="25" r="5" stroke="#0284c7" stroke-width="2" fill="none"/>
                            <circle cx="24" cy="25" r="2" fill="#0284c7"/>
                        </svg>
                    </div>
                    <span class="menu-label">Employee Master Setup</span>
                </a>

            </div>
        </div>

        <!-- Section 4: Employee Profile Setting -->
        <div class="section">
            <div class="section-title">Employee Profile Setting</div>
            <div class="menu-grid">

                <a href="#" class="menu-card" onclick="alert('Employee Profile Settings coming soon')">
                    <div class="menu-icon bg-blue">
                        <svg viewBox="0 0 48 48" fill="none">
                            <circle cx="24" cy="16" r="9" fill="#3b82f6"/>
                            <path d="M10 40c0-7 6-11 14-11s14 4 14 11" fill="#3b82f6"/>
                            <circle cx="36" cy="34" r="8" fill="#60a5fa"/>
                            <path d="M36 30v8M32 34h8" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <span class="menu-label">Profile Settings</span>
                </a>

                <a href="#" class="menu-card" onclick="alert('Document Upload coming soon')">
                    <div class="menu-icon bg-indigo">
                        <svg viewBox="0 0 48 48" fill="none">
                            <path d="M14 8h14l10 10v22a3 3 0 01-3 3H14a3 3 0 01-3-3V11a3 3 0 013-3z" fill="#6366f1"/>
                            <path d="M28 8v10h10" fill="#818cf8"/>
                            <path d="M20 28l4-4 4 4M24 24v10" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <span class="menu-label">Document Upload</span>
                </a>

            </div>
        </div>

    </div>

    <script>
        // Check login
        if (sessionStorage.getItem('hr_logged_in') !== 'true') {
            window.location.href = '../index.php';
        }

        const username = sessionStorage.getItem('hr_username') || 'Admin';
        document.getElementById('displayName').textContent = username.charAt(0).toUpperCase() + username.slice(1);

        function toggleMenu() {
            document.getElementById('userDropdown').classList.toggle('show');
        }

        function logout() {
            sessionStorage.removeItem('hr_logged_in');
            sessionStorage.removeItem('hr_username');
            window.location.href = '../index.php';
        }

        document.addEventListener('click', function(e) {
            const menu = document.getElementById('userDropdown');
            const btn = document.querySelector('.user-menu-btn');
            if (btn && menu && !btn.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('show');
            }
        });
    </script>
</body>
</html>
