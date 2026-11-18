<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sipway Campus - HRM Selection</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 10px;
            position: relative;
        }

        .logo {
            font-size: 48px;
            font-weight: 700;
            background: linear-gradient(90deg, #e63946, #1d3557);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -1px;
            position: relative;
            display: inline-block;
        }

        .logo .cloud {
            position: absolute;
            top: -18px;
            right: -35px;
            width: 50px;
            height: 35px;
        }

        .logo .cloud svg {
            width: 100%;
            height: 100%;
        }

        .subtitle {
            font-size: 28px;
            font-weight: 500;
            color: #1d3557;
            margin-top: 25px;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .underline {
            width: 60px;
            height: 2px;
            background: #1d3557;
            margin: 0 auto 50px auto;
            border-radius: 2px;
        }

        .cards-container {
            display: flex;
            gap: 35px;
            flex-wrap: wrap;
            justify-content: center;
            max-width: 1100px;
        }

        .card {
            background: #ffffff;
            border-radius: 16px;
            width: 280px;
            height: 300px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid rgba(0, 0, 0, 0.04);
            position: relative;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-12px);
            box-shadow: 0 20px 40px rgba(29, 53, 87, 0.15);
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #e63946, #1d3557);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .card:hover::before {
            opacity: 1;
        }

        .icon-wrapper {
            width: 160px;
            height: 160px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            position: relative;
        }

        .icon-wrapper svg {
            width: 140px;
            height: 140px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 500;
            color: #2563eb;
            text-decoration: underline;
            text-underline-offset: 4px;
            transition: color 0.3s ease;
        }

        .card:hover .card-title {
            color: #1d3557;
        }

        .footer {
            margin-top: 60px;
            font-size: 13px;
            color: #6b7280;
            text-align: center;
        }

        /* Responsive */
        @media (max-width: 950px) {
            .cards-container {
                gap: 25px;
            }
            .card {
                width: 250px;
                height: 280px;
            }
        }

        @media (max-width: 600px) {
            .logo {
                font-size: 36px;
            }
            .subtitle {
                font-size: 22px;
            }
            .cards-container {
                flex-direction: column;
                align-items: center;
            }
            .card {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
</head>
<body>

    <div class="logo-container">
        <div class="logo">
            Sipway Campus
            <div class="cloud">
                <svg viewBox="0 0 64 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M50 28C54.4183 28 58 24.4183 58 20C58 15.5817 54.4183 12 50 12C49.4477 12 48.909 12.067 48.3906 12.1934C46.6878 7.5698 42.201 4.5 37 4.5C30.3726 4.5 25 9.87258 25 16.5C25 16.833 25.0135 17.1625 25.0398 17.4878C22.0423 18.4863 19.75 21.3055 19.75 24.75C19.75 28.754 22.996 32 27 32H50C54.4183 32 58 28.4183 58 24C58 21.2386 56.2386 18.9 53.75 18.1" 
                          stroke="url(#cloudGrad)" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                    <defs>
                        <linearGradient id="cloudGrad" x1="0" y1="0" x2="64" y2="40">
                            <stop offset="0%" stop-color="#e63946"/>
                            <stop offset="100%" stop-color="#1d3557"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>
        </div>
    </div>

    <div class="subtitle">Selection</div>
    <div class="underline"></div>

    <div class="cards-container">

        <!-- Dashboard Card -->
        <a href="dashboard.php" class="card">
            <div class="icon-wrapper">
                <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Dashed circle -->
                    <circle cx="100" cy="100" r="85" stroke="#94a3b8" stroke-width="2" stroke-dasharray="6 6" fill="none" opacity="0.6"/>
                    <!-- Soft teal circle -->
                    <circle cx="100" cy="105" r="55" fill="#ccfbf1" opacity="0.7"/>
                    
                    <!-- Dashboard window -->
                    <rect x="45" y="55" width="70" height="50" rx="4" stroke="#64748b" stroke-width="2.5" fill="none"/>
                    <rect x="50" y="62" width="20" height="12" rx="2" fill="#94a3b8" opacity="0.5"/>
                    <rect x="74" y="62" width="20" height="12" rx="2" fill="#94a3b8" opacity="0.5"/>
                    <path d="M50 85 L60 78 L70 88 L85 70" stroke="#64748b" stroke-width="2" fill="none"/>
                    
                    <!-- Bar chart -->
                    <rect x="125" y="75" width="10" height="35" rx="2" fill="#64748b" opacity="0.7"/>
                    <rect x="140" y="60" width="10" height="50" rx="2" fill="#64748b" opacity="0.5"/>
                    <rect x="155" y="70" width="10" height="40" rx="2" fill="#64748b" opacity="0.6"/>
                    
                    <!-- Pie chart -->
                    <circle cx="150" cy="50" r="18" stroke="#64748b" stroke-width="2.5" fill="none"/>
                    <path d="M150 50 L150 32 A18 18 0 0 1 165 55 Z" fill="#64748b" opacity="0.4"/>
                    
                    <!-- Browser with gear -->
                    <rect x="60" y="120" width="55" height="40" rx="3" stroke="#64748b" stroke-width="2.5" fill="none"/>
                    <line x1="60" y1="130" x2="115" y2="130" stroke="#64748b" stroke-width="2"/>
                    <circle cx="70" cy="125" r="2" fill="#94a3b8"/>
                    <circle cx="78" cy="125" r="2" fill="#94a3b8"/>
                    <circle cx="86" cy="125" r="2" fill="#94a3b8"/>
                    
                    <!-- Gear -->
                    <g transform="translate(95, 145)">
                        <circle cx="0" cy="0" r="12" stroke="#64748b" stroke-width="2.5" fill="none"/>
                        <circle cx="0" cy="0" r="5" fill="#64748b" opacity="0.4"/>
                        <line x1="0" y1="-16" x2="0" y2="-12" stroke="#64748b" stroke-width="3" stroke-linecap="round"/>
                        <line x1="0" y1="12" x2="0" y2="16" stroke="#64748b" stroke-width="3" stroke-linecap="round"/>
                        <line x1="-16" y1="0" x2="-12" y2="0" stroke="#64748b" stroke-width="3" stroke-linecap="round"/>
                        <line x1="12" y1="0" x2="16" y2="0" stroke="#64748b" stroke-width="3" stroke-linecap="round"/>
                        <line x1="-11" y1="-11" x2="-8.5" y2="-8.5" stroke="#64748b" stroke-width="3" stroke-linecap="round"/>
                        <line x1="8.5" y1="8.5" x2="11" y2="11" stroke="#64748b" stroke-width="3" stroke-linecap="round"/>
                        <line x1="11" y1="-11" x2="8.5" y2="-8.5" stroke="#64748b" stroke-width="3" stroke-linecap="round"/>
                        <line x1="-8.5" y1="8.5" x2="-11" y2="11" stroke="#64748b" stroke-width="3" stroke-linecap="round"/>
                    </g>
                </svg>
            </div>
            <span class="card-title">Dashboard</span>
        </a>

        <!-- Self Service Portal Card -->
        <a href="self-service/index.php" class="card">
            <div class="icon-wrapper">
                <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Dashed circle -->
                    <circle cx="100" cy="100" r="85" stroke="#a78bfa" stroke-width="2" stroke-dasharray="6 6" fill="none" opacity="0.5"/>
                    
                    <!-- Gear with circuit -->
                    <g transform="translate(70, 85)">
                        <circle cx="0" cy="0" r="28" stroke="#7c3aed" stroke-width="2.5" fill="none"/>
                        <circle cx="0" cy="0" r="12" stroke="#7c3aed" stroke-width="2" fill="none"/>
                        <!-- Gear teeth -->
                        <line x1="0" y1="-36" x2="0" y2="-28" stroke="#7c3aed" stroke-width="4" stroke-linecap="round"/>
                        <line x1="0" y1="28" x2="0" y2="36" stroke="#7c3aed" stroke-width="4" stroke-linecap="round"/>
                        <line x1="-36" y1="0" x2="-28" y2="0" stroke="#7c3aed" stroke-width="4" stroke-linecap="round"/>
                        <line x1="28" y1="0" x2="36" y2="0" stroke="#7c3aed" stroke-width="4" stroke-linecap="round"/>
                        <line x1="-25" y1="-25" x2="-20" y2="-20" stroke="#7c3aed" stroke-width="4" stroke-linecap="round"/>
                        <line x1="20" y1="20" x2="25" y2="25" stroke="#7c3aed" stroke-width="4" stroke-linecap="round"/>
                        <line x1="25" y1="-25" x2="20" y2="-20" stroke="#7c3aed" stroke-width="4" stroke-linecap="round"/>
                        <line x1="-20" y1="20" x2="-25" y2="25" stroke="#7c3aed" stroke-width="4" stroke-linecap="round"/>
                        
                        <!-- Circuit nodes -->
                        <circle cx="-45" cy="-15" r="3" fill="#7c3aed"/>
                        <circle cx="-50" cy="10" r="3" fill="#7c3aed"/>
                        <circle cx="-40" cy="30" r="3" fill="#7c3aed"/>
                        <line x1="-36" y1="-10" x2="-45" y2="-15" stroke="#7c3aed" stroke-width="1.5"/>
                        <line x1="-36" y1="5" x2="-50" y2="10" stroke="#7c3aed" stroke-width="1.5"/>
                        <line x1="-30" y1="20" x2="-40" y2="30" stroke="#7c3aed" stroke-width="1.5"/>
                    </g>
                    
                    <!-- Chat bubbles -->
                    <g transform="translate(130, 55)">
                        <rect x="-5" y="-15" width="45" height="30" rx="8" stroke="#7c3aed" stroke-width="2" fill="none"/>
                        <text x="5" y="5" font-family="Arial" font-size="14" fill="#7c3aed" font-weight="bold">Q</text>
                        <path d="M10 15 L5 25 L18 15" stroke="#7c3aed" stroke-width="2" fill="none"/>
                    </g>
                    <g transform="translate(145, 90)">
                        <rect x="-5" y="-12" width="40" height="26" rx="7" stroke="#7c3aed" stroke-width="2" fill="none"/>
                        <text x="5" y="5" font-family="Arial" font-size="13" fill="#7c3aed" font-weight="bold">A</text>
                    </g>
                    
                    <!-- Graduate person with laptop -->
                    <g transform="translate(110, 140)">
                        <!-- Body -->
                        <path d="M-15 25 Q-15 5 0 5 Q15 5 15 25" stroke="#7c3aed" stroke-width="2.5" fill="none"/>
                        <!-- Head -->
                        <circle cx="0" cy="-5" r="12" stroke="#7c3aed" stroke-width="2.5" fill="none"/>
                        <!-- Graduation cap -->
                        <path d="M-16 -12 L0 -20 L16 -12 L0 -8 Z" stroke="#7c3aed" stroke-width="2" fill="none"/>
                        <line x1="14" y1="-13" x2="14" y2="-5" stroke="#7c3aed" stroke-width="2"/>
                        <!-- Arms up -->
                        <path d="M-12 10 Q-25 0 -30 -5" stroke="#7c3aed" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                        <path d="M12 10 Q25 0 30 -5" stroke="#7c3aed" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                        <!-- Laptop -->
                        <rect x="-20" y="28" width="40" height="25" rx="2" stroke="#7c3aed" stroke-width="2" fill="none"/>
                        <line x1="-22" y1="53" x2="22" y2="53" stroke="#7c3aed" stroke-width="2.5" stroke-linecap="round"/>
                    </g>
                </svg>
            </div>
            <span class="card-title">Self Service Portal</span>
        </a>

        <!-- HR Panel Card -->
        <a href="hr/index.php" class="card">
            <div class="icon-wrapper">
                <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Dashed circle -->
                    <circle cx="100" cy="100" r="85" stroke="#60a5fa" stroke-width="2" stroke-dasharray="6 6" fill="none" opacity="0.5"/>
                    <!-- Soft blue circle -->
                    <circle cx="100" cy="110" r="50" fill="#dbeafe" opacity="0.6"/>
                    
                    <!-- ID Card / Profile -->
                    <g transform="translate(60, 70)">
                        <rect x="0" y="0" width="55" height="70" rx="6" stroke="#3b82f6" stroke-width="2.5" fill="none"/>
                        <!-- Avatar -->
                        <circle cx="27.5" cy="22" r="12" stroke="#3b82f6" stroke-width="2" fill="none"/>
                        <path d="M12 50 Q12 35 27.5 35 Q43 35 43 50" stroke="#3b82f6" stroke-width="2" fill="none"/>
                        <!-- Checkbox -->
                        <rect x="15" y="55" width="12" height="10" rx="2" stroke="#3b82f6" stroke-width="1.5" fill="none"/>
                        <path d="M17 60 L20 63 L25 57" stroke="#3b82f6" stroke-width="1.5" fill="none"/>
                        <!-- Lines -->
                        <line x1="32" y1="58" x2="48" y2="58" stroke="#3b82f6" stroke-width="1.5"/>
                        <line x1="32" y1="63" x2="45" y2="63" stroke="#3b82f6" stroke-width="1.5"/>
                    </g>
                    
                    <!-- Hand pointing -->
                    <g transform="translate(75, 130)">
                        <path d="M0 0 Q-5 -15 5 -25 Q10 -30 15 -20 L20 -5" stroke="#3b82f6" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                        <path d="M5 -5 L15 -15" stroke="#3b82f6" stroke-width="2" stroke-linecap="round"/>
                    </g>
                    
                    <!-- Mobile phone -->
                    <g transform="translate(130, 55)">
                        <rect x="0" y="0" width="40" height="70" rx="6" stroke="#3b82f6" stroke-width="2.5" fill="none"/>
                        <line x1="12" y1="8" x2="28" y2="8" stroke="#3b82f6" stroke-width="2" stroke-linecap="round"/>
                        <circle cx="20" cy="62" r="4" stroke="#3b82f6" stroke-width="1.5" fill="none"/>
                        <!-- Hand holding -->
                        <path d="M5 70 Q-5 75 -8 85" stroke="#3b82f6" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                    </g>
                    
                    <!-- Calendar with clock -->
                    <g transform="translate(115, 135)">
                        <rect x="0" y="5" width="45" height="40" rx="4" stroke="#3b82f6" stroke-width="2.5" fill="none"/>
                        <line x1="0" y1="15" x2="45" y2="15" stroke="#3b82f6" stroke-width="2"/>
                        <line x1="12" y1="0" x2="12" y2="10" stroke="#3b82f6" stroke-width="2.5" stroke-linecap="round"/>
                        <line x1="33" y1="0" x2="33" y2="10" stroke="#3b82f6" stroke-width="2.5" stroke-linecap="round"/>
                        <!-- Clock -->
                        <circle cx="50" cy="40" r="14" stroke="#3b82f6" stroke-width="2" fill="#eff6ff"/>
                        <line x1="50" y1="40" x2="50" y2="32" stroke="#3b82f6" stroke-width="2" stroke-linecap="round"/>
                        <line x1="50" y1="40" x2="56" y2="44" stroke="#3b82f6" stroke-width="2" stroke-linecap="round"/>
                    </g>
                </svg>
            </div>
            <span class="card-title">HR Panel</span>
        </a>

    </div>

    <div class="footer">
        Copyrights Sipway Campus &copy; 2024
    </div>

</body>
</html>
