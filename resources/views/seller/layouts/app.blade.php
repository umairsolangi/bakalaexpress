<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Seller Panel') - Bakala Express</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            DEFAULT: '#00A651',
                            dark: '#008c44',
                            light: '#e6f7ef',
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#00A651',
                            600: '#008c44',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        },
                        sidebar: {
                            DEFAULT: '#0f172a',
                            light: '#1e293b',
                            lighter: '#334155',
                        },
                        accent: {
                            DEFAULT: '#F59E0B',
                            hover: '#D97706',
                        },
                    },
                    keyframes: {
                        'fade-in-up': {
                            '0%': { opacity: '0', transform: 'translateY(16px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        'slide-in-right': {
                            '0%': { opacity: '0', transform: 'translateX(-16px)' },
                            '100%': { opacity: '1', transform: 'translateX(0)' },
                        },
                        'pulse-dot': {
                            '0%, 100%': { opacity: '1' },
                            '50%': { opacity: '0.4' },
                        },
                        'count-up': {
                            '0%': { opacity: '0', transform: 'translateY(8px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                    },
                    animation: {
                        'fade-in-up': 'fade-in-up 0.5s ease-out forwards',
                        'slide-in-right': 'slide-in-right 0.4s ease-out forwards',
                        'pulse-dot': 'pulse-dot 2s ease-in-out infinite',
                        'count-up': 'count-up 0.6s ease-out forwards',
                    },
                }
            }
        }
    </script>

    <style>
        /* ===== CORE LAYOUT ===== */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            overflow-x: hidden;
        }

        /* ===== SIDEBAR ===== */
        .seller-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 270px;
            height: 100vh;
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            z-index: 100;
            display: flex;
            flex-direction: column;
            transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
            overflow-x: hidden;
        }

        .seller-sidebar::-webkit-scrollbar { width: 4px; }
        .seller-sidebar::-webkit-scrollbar-track { background: transparent; }
        .seller-sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 99px; }

        .sidebar-logo {
            padding: 28px 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }

        .sidebar-nav { padding: 16px 12px; flex: 1; }

        .sidebar-section-label {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: rgba(255,255,255,0.3);
            padding: 16px 14px 8px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 500;
            color: rgba(255,255,255,0.55);
            text-decoration: none;
            transition: all 0.25s ease;
            margin-bottom: 2px;
            position: relative;
        }

        .sidebar-link:hover {
            color: rgba(255,255,255,0.9);
            background: rgba(255,255,255,0.06);
        }

        .sidebar-link.active {
            color: #fff;
            background: linear-gradient(135deg, rgba(0,166,81,0.25), rgba(0,166,81,0.12));
            box-shadow: inset 0 0 0 1px rgba(0,166,81,0.2);
        }

        .sidebar-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 24px;
            background: #00A651;
            border-radius: 0 4px 4px 0;
        }

        .sidebar-link i {
            width: 20px;
            text-align: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .sidebar-link.active i { color: #4ade80; }

        .sidebar-badge {
            margin-left: auto;
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 99px;
            min-width: 22px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid rgba(255,255,255,0.06);
        }

        /* ===== MAIN CONTENT ===== */
        .main-wrapper {
            margin-left: 270px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ===== TOP BAR ===== */
        .top-bar {
            position: sticky;
            top: 0;
            z-index: 90;
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 0 32px;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .top-bar-left { display: flex; align-items: center; gap: 16px; }
        .top-bar-right { display: flex; align-items: center; gap: 12px; }

        /* ===== USER DROPDOWN ===== */
        .user-dropdown { position: relative; }

        .user-dropdown-menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 220px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.12), 0 0 0 1px rgba(0,0,0,0.04);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-8px) scale(0.97);
            transition: all 0.2s ease;
            padding: 8px;
            z-index: 200;
        }

        .user-dropdown.open .user-dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
        }

        .dropdown-item-custom {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            text-decoration: none;
            transition: all 0.15s ease;
            border: none;
            background: none;
            width: 100%;
            cursor: pointer;
        }

        .dropdown-item-custom:hover { background: #f3f4f6; }
        .dropdown-item-custom.danger { color: #ef4444; }
        .dropdown-item-custom.danger:hover { background: #fef2f2; }

        /* ===== MOBILE OVERLAY ===== */
        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
            z-index: 99;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .sidebar-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* ===== HAMBURGER ===== */
        .hamburger-btn {
            display: none;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            background: #fff;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #374151;
            font-size: 1.1rem;
            transition: all 0.2s ease;
        }

        .hamburger-btn:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }

        /* ===== PAGE CONTENT ===== */
        .page-content {
            padding: 32px;
            flex: 1;
        }

        /* ===== FLASH MESSAGES ===== */
        .flash-message {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            border-radius: 14px;
            margin-bottom: 24px;
            font-size: 0.9rem;
            font-weight: 500;
            animation: fade-in-up 0.4s ease-out;
        }

        .flash-success {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .flash-error {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* ===== FOOTER ===== */
        .panel-footer {
            padding: 24px 32px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #9ca3af;
            font-size: 0.8rem;
        }

        /* ===== SCROLLBAR ===== */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1024px) {
            .seller-sidebar {
                transform: translateX(-100%);
            }

            .seller-sidebar.mobile-open {
                transform: translateX(0);
            }

            .main-wrapper {
                margin-left: 0;
            }

            .hamburger-btn {
                display: flex;
            }

            .page-content {
                padding: 20px 16px;
            }

            .top-bar {
                padding: 0 16px;
            }
        }

        @media (max-width: 640px) {
            .page-content { padding: 16px 12px; }
            .top-bar { height: 60px; }
        }

        /* ===== ANIMATIONS ===== */
        .animate-stagger > * {
            opacity: 0;
            animation: fade-in-up 0.5s ease-out forwards;
        }

        .animate-stagger > *:nth-child(1) { animation-delay: 0.05s; }
        .animate-stagger > *:nth-child(2) { animation-delay: 0.1s; }
        .animate-stagger > *:nth-child(3) { animation-delay: 0.15s; }
        .animate-stagger > *:nth-child(4) { animation-delay: 0.2s; }
        .animate-stagger > *:nth-child(5) { animation-delay: 0.25s; }
        .animate-stagger > *:nth-child(6) { animation-delay: 0.3s; }
        .animate-stagger > *:nth-child(7) { animation-delay: 0.35s; }
        .animate-stagger > *:nth-child(8) { animation-delay: 0.4s; }

        /* ===== UTILITY CLASSES ===== */
        .glass-card {
            background: rgba(255,255,255,0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.6);
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.04);
        }

        .card-elevated {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #f1f5f9;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 4px 16px rgba(0,0,0,0.03);
            transition: all 0.3s ease;
        }

        .card-elevated:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.06), 0 8px 32px rgba(0,0,0,0.05);
            transform: translateY(-1px);
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #00A651, #008c44);
            color: #fff;
            padding: 10px 24px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.875rem;
            border: none;
            cursor: pointer;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(0,166,81,0.25);
        }

        .btn-primary-custom:hover {
            background: linear-gradient(135deg, #008c44, #007a3d);
            box-shadow: 0 4px 16px rgba(0,166,81,0.35);
            transform: translateY(-1px);
        }

        .btn-secondary-custom {
            background: #f8fafc;
            color: #475569;
            padding: 10px 24px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.875rem;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-secondary-custom:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        @keyframes fade-in-up {
            0% { opacity: 0; transform: translateY(16px); }
            100% { opacity: 1; transform: translateY(0); }
        }
    </style>
    @yield('styles')
</head>

<body class="bg-slate-100">
    <!-- Mobile Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="seller-sidebar" id="sellerSidebar">
        <!-- Logo -->
        <div class="sidebar-logo">
            <a href="{{ route('seller.panel') }}" class="flex items-center gap-3 no-underline">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-green-600 flex items-center justify-center shadow-lg shadow-green-500/20">
                    <i class="fas fa-store text-white text-lg"></i>
                </div>
                <div>
                    <div class="text-white font-bold text-lg font-display leading-tight tracking-tight">Bakala Express</div>
                    <div class="text-emerald-400 text-[0.65rem] font-semibold uppercase tracking-widest">Seller Panel</div>
                </div>
            </a>
        </div>

        <!-- Navigation -->
        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Main Menu</div>

            <a href="{{ route('seller.panel') }}"
               class="sidebar-link {{ request()->routeIs('seller.panel') ? 'active' : '' }}">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('seller.earnings') }}"
               class="sidebar-link {{ request()->routeIs('seller.earnings') ? 'active' : '' }}">
                <i class="fas fa-wallet"></i>
                <span>Earnings</span>
            </a>

            <div class="sidebar-section-label">Products</div>

            <a href="{{ route('add.service') }}"
               class="sidebar-link {{ request()->routeIs('add.service') ? 'active' : '' }}">
                <i class="fas fa-plus-circle"></i>
                <span>Add Product</span>
            </a>

            <a href="{{ route('seller.catalog.index') }}"
               class="sidebar-link {{ request()->routeIs('seller.catalog.*') ? 'active' : '' }}">
                <i class="fas fa-layer-group"></i>
                <span>My Catalog</span>
            </a>

            <div class="sidebar-section-label">Account</div>

            @if(auth()->guard('seller')->user() && !auth()->guard('seller')->user()->is_verified)
                <a href="{{ route('seller.verification.apply') }}"
                   class="sidebar-link {{ request()->routeIs('seller.verification.*') ? 'active' : '' }}">
                    <i class="fas fa-certificate"></i>
                    <span>Get Verified</span>
                    <span class="sidebar-badge" style="background: rgba(245,158,11,0.2); color: #fbbf24;">New</span>
                </a>
            @else
                <a href="{{ route('seller.verification.apply') }}"
                   class="sidebar-link {{ request()->routeIs('seller.verification.*') ? 'active' : '' }}">
                    <i class="fas fa-shield-halved"></i>
                    <span>Verification</span>
                    <span class="sidebar-badge" style="background: rgba(34,197,94,0.2); color: #4ade80;"><i class="fas fa-check text-[0.6rem]"></i></span>
                </a>
            @endif
        </nav>

        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
            @if(session('admin_id'))
                <form action="{{ route('admin.returnToAdmin') }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="sidebar-link w-full" style="color: rgba(96,165,250,0.9); margin-bottom: 0;">
                        <i class="fas fa-user-shield"></i>
                        <span>Return to Admin</span>
                    </button>
                </form>
            @endif
            <form id="logout-form" action="{{ route('logout.seller') }}" method="POST">
                @csrf
                <button type="submit" class="sidebar-link w-full" style="color: rgba(248,113,113,0.8); margin-bottom: 0;">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="main-wrapper">
        <!-- Top Bar -->
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="hamburger-btn" id="sidebarToggle" type="button" aria-label="Toggle sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <h1 class="text-lg font-bold text-gray-900 font-display leading-tight">@yield('page-title', 'Dashboard')</h1>
                    <p class="text-xs text-gray-400 mt-0.5">@yield('page-subtitle', 'Welcome to your seller panel')</p>
                </div>
            </div>

            <div class="top-bar-right">
                <!-- Store Status -->
                @if(auth()->guard('seller')->user())
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full {{ auth()->guard('seller')->user()->is_open ? 'bg-emerald-50 border border-emerald-200' : 'bg-red-50 border border-red-200' }}">
                        <span class="relative flex h-2.5 w-2.5">
                            @if(auth()->guard('seller')->user()->is_open)
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                            @else
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-400"></span>
                            @endif
                        </span>
                        <span class="text-xs font-semibold {{ auth()->guard('seller')->user()->is_open ? 'text-emerald-700' : 'text-red-600' }}">
                            {{ auth()->guard('seller')->user()->is_open ? 'Store Open' : 'Store Closed' }}
                        </span>
                    </div>
                @endif

                <!-- User Dropdown -->
                <div class="user-dropdown" id="userDropdown">
                    <button class="flex items-center gap-3 cursor-pointer bg-transparent border-none" id="userDropdownBtn" type="button">
                        <div class="text-right hidden sm:block">
                            <div class="text-sm font-bold text-gray-900 leading-tight">
                                {{ auth()->guard('seller')->user() ? auth()->guard('seller')->user()->name : 'Seller' }}
                            </div>
                            @if(auth()->guard('seller')->user() && auth()->guard('seller')->user()->is_verified)
                                <div class="text-[0.65rem] text-emerald-600 font-semibold flex items-center justify-end gap-1">
                                    <i class="fas fa-check-circle"></i> Verified Seller
                                </div>
                            @else
                                <div class="text-[0.65rem] text-gray-400 font-medium">Seller Account</div>
                            @endif
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center border-2 border-transparent hover:border-primary/30 transition-all overflow-hidden">
                            @if(auth()->guard('seller')->user() && auth()->guard('seller')->user()->profile_image)
                                <img src="{{ asset('storage/' . auth()->guard('seller')->user()->profile_image) }}"
                                     alt="Profile" class="w-full h-full object-cover">
                            @else
                                <i class="fas fa-user text-gray-400"></i>
                            @endif
                        </div>
                        <i class="fas fa-chevron-down text-gray-400 text-[0.6rem] hidden sm:block"></i>
                    </button>

                    <div class="user-dropdown-menu">
                        <div class="px-4 py-3 border-b border-gray-100 mb-1">
                            <p class="text-sm font-bold text-gray-900">{{ auth()->guard('seller')->user() ? auth()->guard('seller')->user()->name : 'Seller' }}</p>
                            <p class="text-xs text-gray-400">{{ auth()->guard('seller')->user() ? auth()->guard('seller')->user()->email : '' }}</p>
                        </div>
                        <a href="{{ route('seller.panel') }}" class="dropdown-item-custom">
                            <i class="fas fa-th-large text-gray-400 w-4 text-center"></i> Dashboard
                        </a>
                        <a href="{{ route('seller.earnings') }}" class="dropdown-item-custom">
                            <i class="fas fa-wallet text-gray-400 w-4 text-center"></i> Earnings
                        </a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <form action="{{ route('logout.seller') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item-custom danger">
                                <i class="fas fa-sign-out-alt w-4 text-center"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="page-content">
            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="flash-message flash-success">
                    <i class="fas fa-check-circle text-lg"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="flash-message flash-error">
                    <i class="fas fa-exclamation-circle text-lg"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="panel-footer">
            <p>&copy; {{ date('Y') }} Bakala Express. All rights reserved.</p>
        </footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sellerSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const toggleBtn = document.getElementById('sidebarToggle');
            const userDropdown = document.getElementById('userDropdown');
            const userDropdownBtn = document.getElementById('userDropdownBtn');

            // Sidebar toggle (mobile)
            if (toggleBtn && sidebar && overlay) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('mobile-open');
                    overlay.classList.toggle('active');
                    document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
                });

                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('mobile-open');
                    overlay.classList.remove('active');
                    document.body.style.overflow = '';
                });
            }

            // User dropdown toggle
            if (userDropdownBtn && userDropdown) {
                userDropdownBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userDropdown.classList.toggle('open');
                });

                document.addEventListener('click', function(e) {
                    if (!userDropdown.contains(e.target)) {
                        userDropdown.classList.remove('open');
                    }
                });
            }

            // Auto-hide flash messages
            const flashMessages = document.querySelectorAll('.flash-message');
            flashMessages.forEach(function(msg) {
                setTimeout(function() {
                    msg.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                    msg.style.opacity = '0';
                    msg.style.transform = 'translateY(-8px)';
                    setTimeout(function() { msg.remove(); }, 400);
                }, 5000);
            });
        });
    </script>

    @yield('scripts')
</body>

</html>
