<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Rider Dashboard') | Bakala Express</title>

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
                        },
                        rider: {
                            DEFAULT: '#f59e0b',
                            dark: '#d97706',
                            light: '#fef3c7',
                            50: '#fffbeb',
                        },
                        sidebar: {
                            DEFAULT: '#1c1917',
                            light: '#292524',
                            lighter: '#44403c',
                        },
                    },
                    keyframes: {
                        'fade-in-up': {
                            '0%': { opacity: '0', transform: 'translateY(16px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                    },
                    animation: {
                        'fade-in-up': 'fade-in-up 0.5s ease-out forwards',
                    },
                }
            }
        }
    </script>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background-color: #f5f5f4; overflow-x: hidden; }

        /* ===== SIDEBAR ===== */
        .rider-sidebar {
            position: fixed; top: 0; left: 0; width: 270px; height: 100vh;
            background: linear-gradient(180deg, #1c1917 0%, #292524 100%);
            z-index: 100; display: flex; flex-direction: column;
            transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto; overflow-x: hidden;
        }
        .rider-sidebar::-webkit-scrollbar { width: 4px; }
        .rider-sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.12); border-radius: 99px; }

        .sidebar-logo { padding: 28px 24px 20px; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .sidebar-nav { padding: 16px 12px; flex: 1; }

        .sidebar-section-label {
            font-size: 0.65rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.1em; color: rgba(255,255,255,0.25); padding: 16px 14px 8px;
        }

        .sidebar-link {
            display: flex; align-items: center; gap: 14px; padding: 12px 14px;
            border-radius: 12px; font-size: 0.875rem; font-weight: 500;
            color: rgba(255,255,255,0.5); text-decoration: none;
            transition: all 0.25s ease; margin-bottom: 2px; position: relative;
        }
        .sidebar-link:hover { color: rgba(255,255,255,0.85); background: rgba(255,255,255,0.06); }
        .sidebar-link.active {
            color: #fff;
            background: linear-gradient(135deg, rgba(245,158,11,0.25), rgba(245,158,11,0.1));
            box-shadow: inset 0 0 0 1px rgba(245,158,11,0.2);
        }
        .sidebar-link.active::before {
            content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%);
            width: 3px; height: 24px; background: #f59e0b; border-radius: 0 4px 4px 0;
        }
        .sidebar-link i { width: 20px; text-align: center; font-size: 1rem; flex-shrink: 0; }
        .sidebar-link.active i { color: #fbbf24; }

        .sidebar-footer { padding: 16px 12px; border-top: 1px solid rgba(255,255,255,0.06); }

        /* ===== MAIN WRAPPER ===== */
        .main-wrapper {
            margin-left: 270px; min-height: 100vh; display: flex; flex-direction: column;
            transition: margin-left 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ===== TOP BAR ===== */
        .top-bar {
            position: sticky; top: 0; z-index: 90;
            background: rgba(255,255,255,0.85); backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 0 32px; height: 72px;
            display: flex; align-items: center; justify-content: space-between;
        }

        /* ===== USER DROPDOWN ===== */
        .user-dropdown { position: relative; }
        .user-dropdown-menu {
            position: absolute; top: calc(100% + 8px); right: 0; width: 220px;
            background: #fff; border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.12), 0 0 0 1px rgba(0,0,0,0.04);
            opacity: 0; visibility: hidden; transform: translateY(-8px) scale(0.97);
            transition: all 0.2s ease; padding: 8px; z-index: 200;
        }
        .user-dropdown.open .user-dropdown-menu { opacity: 1; visibility: visible; transform: translateY(0) scale(1); }

        .dropdown-item-custom {
            display: flex; align-items: center; gap: 10px; padding: 10px 14px;
            border-radius: 10px; font-size: 0.875rem; font-weight: 500;
            color: #374151; text-decoration: none; transition: all 0.15s ease;
            border: none; background: none; width: 100%; cursor: pointer;
        }
        .dropdown-item-custom:hover { background: #f3f4f6; }
        .dropdown-item-custom.danger { color: #ef4444; }
        .dropdown-item-custom.danger:hover { background: #fef2f2; }

        /* ===== MOBILE ===== */
        .sidebar-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px); z-index: 99;
            opacity: 0; visibility: hidden; transition: all 0.3s ease;
        }
        .sidebar-overlay.active { opacity: 1; visibility: visible; }

        .hamburger-btn {
            display: none; width: 40px; height: 40px; border-radius: 10px;
            border: 1px solid #e5e7eb; background: #fff; align-items: center;
            justify-content: center; cursor: pointer; color: #374151;
            font-size: 1.1rem; transition: all 0.2s ease;
        }
        .hamburger-btn:hover { background: #f9fafb; border-color: #d1d5db; }

        .page-content { padding: 32px; flex: 1; }

        /* ===== FLASH MESSAGES ===== */
        .flash-message {
            display: flex; align-items: center; gap: 12px; padding: 16px 20px;
            border-radius: 14px; margin-bottom: 24px; font-size: 0.9rem; font-weight: 500;
            animation: fade-in-up 0.4s ease-out;
        }
        .flash-success { background: linear-gradient(135deg, #f0fdf4, #dcfce7); color: #166534; border: 1px solid #bbf7d0; }
        .flash-error { background: linear-gradient(135deg, #fef2f2, #fee2e2); color: #991b1b; border: 1px solid #fecaca; }

        .panel-footer { padding: 24px 32px; border-top: 1px solid #e5e7eb; text-align: center; color: #9ca3af; font-size: 0.8rem; }

        /* ===== UTILITY ===== */
        .card-elevated {
            background: #fff; border-radius: 16px; border: 1px solid #f1f5f9;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 4px 16px rgba(0,0,0,0.03);
            transition: all 0.3s ease;
        }
        .card-elevated:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.06), 0 8px 32px rgba(0,0,0,0.05); transform: translateY(-1px); }

        .btn-rider {
            background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff;
            padding: 10px 24px; border-radius: 12px; font-weight: 600; font-size: 0.875rem;
            border: none; cursor: pointer; transition: all 0.25s ease;
            display: inline-flex; align-items: center; gap: 8px; text-decoration: none;
            box-shadow: 0 2px 8px rgba(245,158,11,0.3);
        }
        .btn-rider:hover { background: linear-gradient(135deg, #d97706, #b45309); box-shadow: 0 4px 16px rgba(245,158,11,0.4); transform: translateY(-1px); }

        .btn-secondary-custom {
            background: #f8fafc; color: #475569; padding: 10px 24px; border-radius: 12px;
            font-weight: 600; font-size: 0.875rem; border: 1px solid #e2e8f0;
            cursor: pointer; transition: all 0.25s ease;
            display: inline-flex; align-items: center; gap: 8px; text-decoration: none;
        }
        .btn-secondary-custom:hover { background: #f1f5f9; border-color: #cbd5e1; }

        .animate-stagger > * { opacity: 0; animation: fade-in-up 0.5s ease-out forwards; }
        .animate-stagger > *:nth-child(1) { animation-delay: 0.05s; }
        .animate-stagger > *:nth-child(2) { animation-delay: 0.1s; }
        .animate-stagger > *:nth-child(3) { animation-delay: 0.15s; }
        .animate-stagger > *:nth-child(4) { animation-delay: 0.2s; }
        .animate-stagger > *:nth-child(5) { animation-delay: 0.25s; }
        .animate-stagger > *:nth-child(6) { animation-delay: 0.3s; }

        @keyframes fade-in-up { 0% { opacity: 0; transform: translateY(16px); } 100% { opacity: 1; transform: translateY(0); } }

        @media (max-width: 1024px) {
            .rider-sidebar { transform: translateX(-100%); }
            .rider-sidebar.mobile-open { transform: translateX(0); }
            .main-wrapper { margin-left: 0; }
            .hamburger-btn { display: flex; }
            .page-content { padding: 20px 16px; }
            .top-bar { padding: 0 16px; }
        }
        @media (max-width: 640px) { .page-content { padding: 16px 12px; } .top-bar { height: 60px; } }
    </style>
    @yield('styles')
</head>

<body class="bg-stone-100">
    <!-- Mobile Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="rider-sidebar" id="riderSidebar">
        <div class="sidebar-logo">
            <a href="{{ route('rider.dashboard') }}" class="flex items-center gap-3 no-underline">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/25">
                    <i class="fas fa-motorcycle text-white text-lg"></i>
                </div>
                <div>
                    <div class="text-white font-bold text-lg font-display leading-tight tracking-tight">Bakala Express</div>
                    <div class="text-amber-400 text-[0.65rem] font-semibold uppercase tracking-widest">Rider Panel</div>
                </div>
            </a>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Navigation</div>

            <a href="{{ route('rider.dashboard') }}"
               class="sidebar-link {{ request()->routeIs('rider.dashboard') ? 'active' : '' }}">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('rider.available-orders') }}"
               class="sidebar-link {{ request()->routeIs('rider.available-orders') ? 'active' : '' }}">
                <i class="fas fa-list-ul"></i>
                <span>Available Orders</span>
            </a>

            <div class="sidebar-section-label">Rider Info</div>

            <div class="mx-3 p-4 rounded-xl bg-white/5 border border-white/5">
                <div class="flex items-center gap-3 mb-3">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->guard('rider')->user()?->name ?? 'R') }}&background=F59E0B&color=fff&size=36"
                         alt="Avatar" class="w-9 h-9 rounded-lg">
                    <div>
                        <p class="text-white text-sm font-semibold leading-tight">{{ auth()->guard('rider')->user()?->name ?? 'Rider' }}</p>
                        <p class="text-white/40 text-[0.65rem]">Delivery Partner</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fas fa-motorcycle text-white/30 text-xs"></i>
                    <span class="text-white/40 text-xs">{{ auth()->guard('rider')->user()?->vehicle_type ?? 'Bike' }} · {{ auth()->guard('rider')->user()?->vehicle_number ?? '' }}</span>
                </div>
            </div>
        </nav>

        <div class="sidebar-footer">
            <form action="{{ route('rider.logout') }}" method="POST">
                @csrf
                <button type="submit" class="sidebar-link w-full" style="color: rgba(248,113,113,0.8); margin-bottom: 0;">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <header class="top-bar">
            <div class="flex items-center gap-4">
                <button class="hamburger-btn" id="sidebarToggle" type="button" aria-label="Toggle sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <h1 class="text-lg font-bold text-gray-900 font-display leading-tight">@yield('page-title', 'Dashboard')</h1>
                    <p class="text-xs text-gray-400 mt-0.5">@yield('page-subtitle', 'Manage your deliveries')</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <!-- Rider Status -->
                @if(auth()->guard('rider')->user())
                    @php $isOnline = auth()->guard('rider')->user()->status === 'online'; @endphp
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full {{ $isOnline ? 'bg-emerald-50 border border-emerald-200' : 'bg-red-50 border border-red-200' }}">
                        <span class="relative flex h-2.5 w-2.5">
                            @if($isOnline)
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                            @else
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-400"></span>
                            @endif
                        </span>
                        <span class="text-xs font-semibold {{ $isOnline ? 'text-emerald-700' : 'text-red-600' }}">
                            {{ $isOnline ? 'Online' : 'Offline' }}
                        </span>
                    </div>
                @endif

                <!-- Dropdown -->
                <div class="user-dropdown" id="userDropdown">
                    <button class="flex items-center gap-3 cursor-pointer bg-transparent border-none" id="userDropdownBtn" type="button">
                        <div class="w-10 h-10 rounded-xl overflow-hidden border-2 border-transparent hover:border-rider/30 transition-all">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->guard('rider')->user()?->name ?? 'R') }}&background=F59E0B&color=fff"
                                 alt="Avatar" class="w-full h-full object-cover">
                        </div>
                        <i class="fas fa-chevron-down text-gray-400 text-[0.6rem] hidden sm:block"></i>
                    </button>
                    <div class="user-dropdown-menu">
                        <div class="px-4 py-3 border-b border-gray-100 mb-1">
                            <p class="text-sm font-bold text-gray-900">{{ auth()->guard('rider')->user()?->name ?? 'Rider' }}</p>
                            <p class="text-xs text-gray-400">{{ auth()->guard('rider')->user()?->email ?? '' }}</p>
                        </div>
                        <a href="{{ route('rider.dashboard') }}" class="dropdown-item-custom">
                            <i class="fas fa-th-large text-gray-400 w-4 text-center"></i> Dashboard
                        </a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <form action="{{ route('rider.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item-custom danger">
                                <i class="fas fa-sign-out-alt w-4 text-center"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="page-content">
            @if(session('success'))
                <div class="flash-message flash-success">
                    <i class="fas fa-check-circle text-lg"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="flash-message flash-error">
                    <i class="fas fa-exclamation-circle text-lg"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            @yield('content')
        </main>

        <footer class="panel-footer">
            <p>&copy; {{ date('Y') }} Bakala Express. All rights reserved.</p>
        </footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('riderSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const toggleBtn = document.getElementById('sidebarToggle');
            const userDropdown = document.getElementById('userDropdown');
            const userDropdownBtn = document.getElementById('userDropdownBtn');

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

            if (userDropdownBtn && userDropdown) {
                userDropdownBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userDropdown.classList.toggle('open');
                });
                document.addEventListener('click', function(e) {
                    if (!userDropdown.contains(e.target)) userDropdown.classList.remove('open');
                });
            }

            document.querySelectorAll('.flash-message').forEach(function(msg) {
                setTimeout(function() {
                    msg.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                    msg.style.opacity = '0'; msg.style.transform = 'translateY(-8px)';
                    setTimeout(function() { msg.remove(); }, 400);
                }, 5000);
            });
        });
    </script>
    @yield('scripts')
</body>
</html>
