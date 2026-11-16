<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sipway Campus - HR Panel Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            overflow: hidden;
        }
        .left-side {
            width: 50%;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 50%, #1e40af 100%);
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            overflow: hidden;
        }
        .left-side::before {
            content: '';
            position: absolute;
            top: -100px; left: -100px;
            width: 400px; height: 400px;
            background: rgba(255,255,255,0.08);
            border-radius: 50%;
        }
        .left-side::after {
            content: '';
            position: absolute;
            bottom: -150px; right: -100px;
            width: 450px; height: 450px;
            background: rgba(255,255,255,0.06);
            border-radius: 50%;
        }
        .logo-area { text-align: center; z-index: 2; margin-bottom: 30px; }
        .logo-text {
            font-size: 42px; font-weight: 700; color: white;
            letter-spacing: -1px; display: inline-block;
        }
        .logo-text span {
            background: linear-gradient(90deg, #f87171, #ffffff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .tagline { color: rgba(255,255,255,0.85); font-size: 14px; margin-top: 6px; }
        .illustration { z-index: 2; width: 100%; max-width: 420px; }
        .illustration svg { width: 100%; height: auto; }
        .right-side {
            width: 50%; background: #ffffff;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 40px; position: relative;
        }
        .user-icon {
            width: 80px; height: 80px; background: #dbeafe;
            border-radius: 50%; display: flex;
            align-items: center; justify-content: center; margin-bottom: 20px;
        }
        .user-icon svg { width: 45px; height: 45px; }
        .welcome-text { font-size: 22px; font-weight: 600; color: #1e293b; margin-bottom: 30px; }
        .login-form { width: 100%; max-width: 340px; }
        .input-group { margin-bottom: 20px; }
        .input-group input {
            width: 100%; padding: 14px 16px; border: none;
            border-bottom: 2px solid #e2e8f0; background: #f8fafc;
            border-radius: 8px 8px 0 0; font-size: 15px;
            font-family: 'Poppins', sans-serif; color: #1e293b;
            outline: none; transition: all 0.3s ease;
        }
        .input-group input:focus { border-bottom-color: #3b82f6; background: #f0f9ff; }
        .input-group input::placeholder { color: #94a3b8; }
        .forgot-password { text-align: right; margin-bottom: 25px; }
        .forgot-password a { color: #64748b; font-size: 13px; text-decoration: none; }
        .forgot-password a:hover { color: #3b82f6; }
        .login-btn {
            width: 100%; padding: 14px;
            background: linear-gradient(90deg, #1e40af, #3b82f6);
            color: white; border: none; border-radius: 50px;
            font-size: 16px; font-weight: 600; font-family: 'Poppins', sans-serif;
            cursor: pointer; transition: all 0.3s ease; letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(59,130,246,0.35);
        }
        .login-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(59,130,246,0.45); }
        .error-msg {
            background: #fef2f2; color: #dc2626; padding: 10px 14px;
            border-radius: 8px; font-size: 13px; margin-bottom: 15px;
            display: none; border-left: 4px solid #dc2626;
        }
        .error-msg.show { display: block; }
        .success-msg {
            background: #f0fdf4; color: #16a34a; padding: 10px 14px;
            border-radius: 8px; font-size: 13px; margin-bottom: 15px;
            display: none; border-left: 4px solid #16a34a;
        }
        .success-msg.show { display: block; }
        .footer-text { position: absolute; bottom: 25px; font-size: 12px; color: #94a3b8; }
        .back-link {
            position: absolute; top: 25px; left: 25px;
            color: #64748b; text-decoration: none; font-size: 14px;
            display: flex; align-items: center; gap: 6px;
        }
        .back-link:hover { color: #3b82f6; }
        @media (max-width: 900px) {
            body { flex-direction: column; }
            .left-side { width: 100%; min-height: 260px; padding: 30px 20px; }
            .logo-text { font-size: 32px; }
            .illustration { max-width: 240px; }
            .right-side { width: 100%; padding: 40px 20px; }
        }
    </style>
</head>
<body>
    <div class="left-side">
        <div class="logo-area">
            <div class="logo-text">Sipway <span>Campus</span></div>
            <div class="tagline">Choice of the Smart Generation</div>
        </div>
        <div class="illustration">
            <svg viewBox="0 0 500 380" fill="none" xmlns="http://www.w3.org/2000/svg">
                <ellipse cx="80" cy="200" rx="40" ry="80" fill="#60a5fa" opacity="0.3"/>
                <ellipse cx="420" cy="180" rx="50" ry="90" fill="#60a5fa" opacity="0.25"/>
                <ellipse cx="450" cy="250" rx="35" ry="70" fill="#93c5fd" opacity="0.3"/>
                <ellipse cx="50" cy="280" rx="30" ry="60" fill="#93c5fd" opacity="0.25"/>
                <rect x="80" y="100" width="340" height="220" rx="16" fill="#1e3a5f"/>
                <rect x="95" y="115" width="310" height="180" rx="6" fill="#1e40af"/>
                <rect x="110" y="130" width="50" height="55" rx="6" fill="#3b82f6"/>
                <circle cx="135" cy="148" r="12" fill="#93c5fd"/>
                <rect x="120" y="165" width="30" height="8" rx="2" fill="#60a5fa"/>
                <rect x="170" y="130" width="50" height="55" rx="6" fill="#3b82f6"/>
                <circle cx="195" cy="148" r="12" fill="#93c5fd"/>
                <rect x="180" y="165" width="30" height="8" rx="2" fill="#60a5fa"/>
                <rect x="230" y="130" width="50" height="55" rx="6" fill="#2563eb"/>
                <circle cx="255" cy="148" r="12" fill="#93c5fd"/>
                <rect x="240" y="165" width="30" height="8" rx="2" fill="#60a5fa"/>
                <rect x="290" y="130" width="50" height="55" rx="6" fill="#3b82f6"/>
                <circle cx="315" cy="148" r="12" fill="#93c5fd"/>
                <rect x="300" y="165" width="30" height="8" rx="2" fill="#60a5fa"/>
                <rect x="350" y="130" width="40" height="55" rx="6" fill="#3b82f6"/>
                <circle cx="370" cy="148" r="10" fill="#93c5fd"/>
                <rect x="358" y="165" width="24" height="8" rx="2" fill="#60a5fa"/>
                <rect x="110" y="200" width="140" height="80" rx="6" fill="#1e3a5f"/>
                <rect x="120" y="212" width="80" height="6" rx="2" fill="#60a5fa"/>
                <rect x="120" y="225" width="100" height="5" rx="2" fill="#3b82f6"/>
                <rect x="120" y="238" width="90" height="5" rx="2" fill="#3b82f6"/>
                <rect x="120" y="251" width="70" height="5" rx="2" fill="#3b82f6"/>
                <rect x="120" y="264" width="85" height="5" rx="2" fill="#3b82f6"/>
                <rect x="270" y="200" width="70" height="55" rx="8" fill="#f59e0b"/>
                <text x="292" y="235" font-size="28" fill="white">👍</text>
                <circle cx="280" cy="155" r="28" stroke="#fbbf24" stroke-width="5" fill="none"/>
                <line x1="300" y1="175" x2="320" y2="195" stroke="#fbbf24" stroke-width="6" stroke-linecap="round"/>
                <circle cx="130" cy="310" r="14" fill="#f97316"/>
                <rect x="118" y="325" width="24" height="30" rx="6" fill="#ea580c"/>
                <circle cx="200" cy="250" r="12" fill="#e11d48"/>
                <rect x="190" y="262" width="20" height="25" rx="5" fill="#be123c"/>
                <line x1="185" y1="290" x2="185" y2="350" stroke="#94a3b8" stroke-width="3"/>
                <line x1="215" y1="290" x2="215" y2="350" stroke="#94a3b8" stroke-width="3"/>
                <line x1="185" y1="300" x2="215" y2="300" stroke="#94a3b8" stroke-width="2"/>
                <line x1="185" y1="315" x2="215" y2="315" stroke="#94a3b8" stroke-width="2"/>
                <line x1="185" y1="330" x2="215" y2="330" stroke="#94a3b8" stroke-width="2"/>
                <circle cx="270" cy="300" r="13" fill="#0ea5e9"/>
                <rect x="258" y="314" width="24" height="28" rx="5" fill="#0284c7"/>
                <circle cx="340" cy="295" r="13" fill="#8b5cf6"/>
                <rect x="328" y="309" width="24" height="28" rx="5" fill="#7c3aed"/>
                <line x1="352" y1="315" x2="370" y2="295" stroke="#8b5cf6" stroke-width="5" stroke-linecap="round"/>
                <rect x="390" y="330" width="30" height="20" rx="3" fill="#78716c"/>
                <ellipse cx="405" cy="320" rx="18" ry="25" fill="#22c55e"/>
            </svg>
        </div>
    </div>

    <div class="right-side">
        <a href="../index.php" class="back-link">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back
        </a>
        <div class="user-icon">
            <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="4" fill="#3b82f6"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6" fill="#3b82f6"/></svg>
        </div>
        <div class="welcome-text">Welcome to HR Panel</div>
        <form class="login-form" id="loginForm" onsubmit="return handleLogin(event)">
            <div class="error-msg" id="errorMsg">Invalid username or password!</div>
            <div class="success-msg" id="successMsg">Login successful! Redirecting...</div>
            <div class="input-group">
                <input type="text" id="username" placeholder="Username" autocomplete="username" required>
            </div>
            <div class="input-group">
                <input type="password" id="password" placeholder="Password" autocomplete="current-password" required>
            </div>
            <div class="forgot-password">
                <a href="#" onclick="alert('Contact admin to reset password'); return false;">Forgot Password?</a>
            </div>
            <button type="submit" class="login-btn">LOGIN</button>
        </form>
        <div class="footer-text">Copyrights Sipway Campus &copy; 2024</div>
    </div>

    <script>
        const VALID_USERNAME = "admin";
        const VALID_PASSWORD = "admin123";

        function handleLogin(e) {
            e.preventDefault();
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const errorMsg = document.getElementById('errorMsg');
            const successMsg = document.getElementById('successMsg');
            errorMsg.classList.remove('show');
            successMsg.classList.remove('show');

            if (username === VALID_USERNAME && password === VALID_PASSWORD) {
                successMsg.classList.add('show');
                sessionStorage.setItem('hr_logged_in', 'true');
                sessionStorage.setItem('hr_username', username);
                setTimeout(function() {
                    window.location.href = "dashboard.php";
                }, 800);
            } else {
                errorMsg.classList.add('show');
            }
            return false;
        }
    </script>
</body>
</html>
