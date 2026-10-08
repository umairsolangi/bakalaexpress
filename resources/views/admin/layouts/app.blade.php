<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | Bakala Express Admin</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

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
                        },
                        secondary: {
                            DEFAULT: '#1F2937',
                            light: '#4B5563',
                        },
                        accent: {
                            DEFAULT: '#F59E0B',
                            hover: '#D97706',
                        },
                        sidebar: {
                            DEFAULT: '#0f172a',
                            hover: '#1e293b',
                            active: '#1e3a5f',
                            border: '#334155',
                            text: '#94a3b8',
                            heading: '#64748b',
                        },
                        gray: {
                            50: '#F9FAFB',
                            100: '#F3F4F6',
                            200: '#E5E7EB',
                            800: '#1F2937',
                            900: '#111827',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* ─── Design System Globals ────────────────────────────────── */
        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 72px;
            --topbar-height: 64px;
            --transition-speed: 0.25s;
        }

        * { scrollbar-width: thin; scrollbar-color: #334155 transparent; }
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }

        /* ─── Sidebar ──────────────────────────────────────────────── */
        .admin-sidebar {
            width: var(--sidebar-width);
            transition: width var(--transition-speed) cubic-bezier(0.4,0,0.2,1);
        }
        .admin-sidebar.collapsed { width: var(--sidebar-collapsed-width); }
        .admin-sidebar.collapsed .sidebar-label,
        .admin-sidebar.collapsed .sidebar-logo-text,
        .admin-sidebar.collapsed .sidebar-section-label,
        .admin-sidebar.collapsed .sidebar-user-info,
        .admin-sidebar.collapsed .sidebar-badge { display: none; }
        .admin-sidebar.collapsed .sidebar-nav-link { justify-content: center; padding-left: 0; padding-right: 0; }
        .admin-sidebar.collapsed .sidebar-nav-icon { margin-right: 0; }
        .admin-sidebar.collapsed .sidebar-user-block { justify-content: center; }

        /* ─── Content area ─────────────────────────────────────────── */
        .admin-content {
            margin-left: var(--sidebar-width);
            transition: margin-left var(--transition-speed) cubic-bezier(0.4,0,0.2,1);
        }
        .admin-content.sidebar-collapsed { margin-left: var(--sidebar-collapsed-width); }

        /* ─── Nav link active indicator ────────────────────────────── */
        .sidebar-nav-link { position: relative; transition: all 0.2s ease; }
        .sidebar-nav-link::before {
            content: '';
            position: absolute;
            left: 0; top: 6px; bottom: 6px;
            width: 3px;
            border-radius: 0 3px 3px 0;
            background: #00A651;
            opacity: 0;
            transform: scaleY(0);
            transition: all 0.2s ease;
        }
        .sidebar-nav-link.active::before { opacity: 1; transform: scaleY(1); }
        .sidebar-nav-link.active { background: rgba(0, 166, 81, 0.08); color: #00A651 !important; }

        /* ─── Card hover lift ──────────────────────────────────────── */
        .card-hover { transition: all 0.3s cubic-bezier(0.4,0,0.2,1); }
        .card-hover:hover { transform: translateY(-3px); box-shadow: 0 12px 24px -8px rgba(0,0,0,0.08); }

        /* ─── Backdrop overlay ─────────────────────────────────────── */
        .sidebar-backdrop {
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        .sidebar-backdrop.active {
            opacity: 1;
            visibility: visible;
        }

        /* ─── Mobile sidebar ───────────────────────────────────────── */
        @media (max-width: 1023px) {
            .admin-sidebar {
                transform: translateX(-100%);
                position: fixed;
                z-index: 50;
                width: var(--sidebar-width) !important;
            }
            .admin-sidebar.mobile-open { transform: translateX(0); }
            .admin-content { margin-left: 0 !important; }
        }

        /* ─── Stat card accent stripe ──────────────────────────────── */
        .stat-stripe {
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 4px;
            border-radius: 4px 0 0 4px;
        }

        /* ─── Smooth page enter ────────────────────────────────────── */
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .page-enter { animation: fadeSlideUp 0.4s ease-out forwards; }

        /* ─── Toast animation ──────────────────────────────────────── */
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to   { transform: translateX(0); opacity: 1; }
        }
        .toast-enter { animation: slideInRight 0.4s ease-out forwards; }
    </style>

    @yield('styles')
</head>

<body class="bg-gray-50 text-gray-900 font-sans antialiased">

    <!-- ═══════ SIDEBAR ═══════ -->
    <aside id="adminSidebar" class="admin-sidebar fixed top-0 left-0 h-screen bg-gradient-to-b from-sidebar to-[#131c2e] flex flex-col border-r border-sidebar-border z-50">

        <!-- Logo -->
        <div class="flex items-center gap-3 px-5 h-[72px] border-b border-sidebar-border flex-shrink-0">
            <div class="w-9 h-9 rounded-xl bg-primary/15 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-bolt text-primary text-sm"></i>
            </div>
            <div class="sidebar-logo-text flex flex-col min-w-0">
                <span class="text-[15px] font-bold text-white tracking-tight leading-tight truncate">Bakala Express</span>
                <span class="text-[10px] font-semibold text-sidebar-heading uppercase tracking-widest leading-none">Admin Panel</span>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-5 px-3 space-y-6">

            <!-- Main Section -->
            <div>
                <p class="sidebar-section-label text-[10px] font-bold text-sidebar-heading uppercase tracking-widest px-3 mb-2">Main</p>
                <div class="space-y-0.5">
                    <a href="{{ route('admin.dashboard') }}"
                       class="sidebar-nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium text-sidebar-text hover:bg-sidebar-hover hover:text-white {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-grid-2 sidebar-nav-icon w-5 text-center text-sm"></i>
                        <span class="sidebar-label">Dashboard</span>
                    </a>
                </div>
            </div>

            <!-- Management Section -->
            <div>
                <p class="sidebar-section-label text-[10px] font-bold text-sidebar-heading uppercase tracking-widest px-3 mb-2">Management</p>
                <div class="space-y-0.5">
                    <a href="{{ route('admin.sellers') }}"
                       class="sidebar-nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium text-sidebar-text hover:bg-sidebar-hover hover:text-white {{ request()->routeIs('admin.sellers') ? 'active' : '' }}">
                        <i class="fas fa-store sidebar-nav-icon w-5 text-center text-sm"></i>
                        <span class="sidebar-label">Sellers</span>
                    </a>
                    <a href="{{ route('admin.products') }}"
                       class="sidebar-nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium text-sidebar-text hover:bg-sidebar-hover hover:text-white {{ request()->routeIs('admin.products') ? 'active' : '' }}">
                        <i class="fas fa-box sidebar-nav-icon w-5 text-center text-sm"></i>
                        <span class="sidebar-label">Products</span>
                    </a>
                    <a href="{{ route('admin.catalog.index') }}"
                       class="sidebar-nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium text-sidebar-text hover:bg-sidebar-hover hover:text-white {{ request()->routeIs('admin.catalog.*') ? 'active' : '' }}">
                        <i class="fas fa-layer-group sidebar-nav-icon w-5 text-center text-sm"></i>
                        <span class="sidebar-label">Master Catalog</span>
                    </a>
                </div>
            </div>

            <!-- Approvals Section -->
            <div>
                <p class="sidebar-section-label text-[10px] font-bold text-sidebar-heading uppercase tracking-widest px-3 mb-2">Approvals</p>
                <div class="space-y-0.5">
                    <a href="{{ route('admin.moderation.queue') }}"
                       class="sidebar-nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium text-sidebar-text hover:bg-sidebar-hover hover:text-white {{ request()->routeIs('admin.moderation.queue') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-check sidebar-nav-icon w-5 text-center text-sm"></i>
                        <span class="sidebar-label">Moderation Queue</span>
                        @php
                            $queueCount = App\Models\Seller::where('accountIsApproved', 0)->where('is_deleted', 0)->count()
                                + App\Models\Rider::where('is_approved', false)->where('status', '!=', 'rejected')->count()
                                + App\Models\Product::where('is_approved', 0)->count();
                        @endphp
                        @if($queueCount > 0)
                            <span class="sidebar-badge ml-auto px-1.5 py-0.5 text-[10px] font-bold bg-red-500 text-white rounded-full leading-none">{{ $queueCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.verifications.index') }}"
                       class="sidebar-nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium text-sidebar-text hover:bg-sidebar-hover hover:text-white {{ request()->routeIs('admin.verifications.*') ? 'active' : '' }}">
                        <i class="fas fa-check-circle sidebar-nav-icon w-5 text-center text-sm"></i>
                        <span class="sidebar-label">Verifications</span>
                        @php
                            $pendingCount = App\Models\Seller::where('accountIsApproved', 0)->where('is_deleted', 0)->count();
                        @endphp
                        @if($pendingCount > 0)
                            <span class="sidebar-badge ml-auto px-1.5 py-0.5 text-[10px] font-bold bg-amber-500 text-white rounded-full leading-none">{{ $pendingCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.riders.pending') }}"
                       class="sidebar-nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium text-sidebar-text hover:bg-sidebar-hover hover:text-white {{ request()->routeIs('admin.riders.*') ? 'active' : '' }}">
                        <i class="fas fa-motorcycle sidebar-nav-icon w-5 text-center text-sm"></i>
                        <span class="sidebar-label">Riders</span>
                        @php
                            $pendingRiderCount = App\Models\Rider::where('is_approved', false)->where('status', '!=', 'rejected')->count();
                        @endphp
                        @if($pendingRiderCount > 0)
                            <span class="sidebar-badge ml-auto px-1.5 py-0.5 text-[10px] font-bold bg-red-500 text-white rounded-full leading-none">{{ $pendingRiderCount }}</span>
                        @endif
                    </a>
                </div>
            </div>
        </nav>

        <!-- User Block -->
        <div class="border-t border-sidebar-border p-3 flex-shrink-0">
            <div class="sidebar-user-block flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-sidebar-hover transition-colors cursor-pointer group"
                 onclick="document.getElementById('sidebarLogoutForm').submit();">
                <div class="w-8 h-8 rounded-lg overflow-hidden flex-shrink-0 ring-2 ring-sidebar-border group-hover:ring-primary/30 transition-all">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=00A651&color=fff&bold=true&size=64"
                         alt="Admin" class="w-full h-full object-cover">
                </div>
                <div class="sidebar-user-info min-w-0 flex-1">
                    <p class="text-xs font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] text-sidebar-heading truncate">Administrator</p>
                </div>
                <i class="fas fa-sign-out-alt sidebar-label text-sidebar-heading text-xs group-hover:text-red-400 transition-colors"></i>
            </div>
            <form id="sidebarLogoutForm" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
        </div>
    </aside>

    <!-- ═══════ SIDEBAR MOBILE BACKDROP ═══════ -->
    <div id="sidebarBackdrop" class="sidebar-backdrop fixed inset-0 bg-black/50 backdrop-blur-sm z-40 lg:hidden" onclick="toggleMobileSidebar()"></div>

    <!-- ═══════ MAIN CONTENT AREA ═══════ -->
    <div id="adminContent" class="admin-content min-h-screen flex flex-col">

        <!-- ─── Top Bar ─── -->
        <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-xl border-b border-gray-100">
            <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                <!-- Left: Mobile toggle + Collapse toggle + Breadcrumb -->
                <div class="flex items-center gap-3">
                    <!-- Mobile toggle -->
                    <button id="mobileMenuToggle" class="lg:hidden p-2 -ml-2 rounded-xl text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors" onclick="toggleMobileSidebar()">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                    <!-- Desktop collapse toggle -->
                    <button id="sidebarCollapseToggle" class="hidden lg:flex p-2 -ml-2 rounded-xl text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors" onclick="toggleSidebarCollapse()">
                        <i id="collapseIcon" class="fas fa-chevron-left text-xs"></i>
                    </button>
                    <!-- Breadcrumb -->
                    <div class="hidden sm:flex items-center gap-2 text-sm">
                        <span class="text-gray-400 font-medium">Admin</span>
                        <i class="fas fa-chevron-right text-[8px] text-gray-300"></i>
                        <span class="text-gray-700 font-semibold">@yield('title', 'Dashboard')</span>
                    </div>
                </div>

                <!-- Right: User menu -->
                <div class="flex items-center gap-3">
                    <div class="relative group">
                        <button class="flex items-center gap-2.5 p-1.5 rounded-xl hover:bg-gray-50 transition-colors">
                            <div class="text-right hidden md:block">
                                <div class="text-xs font-bold text-gray-800">{{ Auth::user()->name }}</div>
                                <div class="text-[10px] text-gray-400 font-medium">Administrator</div>
                            </div>
                            <div class="w-9 h-9 rounded-xl overflow-hidden ring-2 ring-gray-100 group-hover:ring-primary/20 transition-all">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=00A651&color=fff&bold=true&size=64"
                                     alt="Admin" class="w-full h-full object-cover">
                            </div>
                        </button>
                        <!-- Dropdown -->
                        <div class="absolute right-0 mt-1.5 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1.5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-right z-50">
                            <div class="px-4 py-2 border-b border-gray-50">
                                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-bold">Account</p>
                            </div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors font-medium flex items-center gap-2">
                                    <i class="fas fa-sign-out-alt w-4 text-center"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- ─── Flash Messages ─── -->
        <div class="px-4 sm:px-6 lg:px-8">
            @if(session('success') || session('status'))
                <div class="mt-5 p-4 rounded-xl bg-green-50 border border-green-200 flex items-center gap-3 text-green-800 toast-enter">
                    <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                    <span class="text-sm font-medium">{{ session('success') ?? session('status') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="mt-5 p-4 rounded-xl bg-red-50 border border-red-200 flex items-center gap-3 text-red-800 toast-enter">
                    <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-600"></i>
                    </div>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            @endif
        </div>

        <!-- ─── Page Content ─── -->
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6 page-enter">
            @yield('content')
        </main>

        <!-- ─── Footer ─── -->
        <footer class="border-t border-gray-100 py-5 px-4 sm:px-6 lg:px-8 mt-auto">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
                <p class="text-gray-400 text-xs font-medium">&copy; {{ date('Y') }} Bakala Express. All rights reserved.</p>
                <p class="text-gray-300 text-[10px] font-medium">Admin Panel v2.0</p>
            </div>
        </footer>
    </div>

    <!-- ═══════ SCRIPTS ═══════ -->
    <script>
        // ─── Sidebar Collapse (Desktop) ───
        function toggleSidebarCollapse() {
            const sidebar = document.getElementById('adminSidebar');
            const content = document.getElementById('adminContent');
            const icon = document.getElementById('collapseIcon');
            sidebar.classList.toggle('collapsed');
            content.classList.toggle('sidebar-collapsed');
            icon.classList.toggle('fa-chevron-left');
            icon.classList.toggle('fa-chevron-right');
        }

        // ─── Sidebar Mobile Toggle ───
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            sidebar.classList.toggle('mobile-open');
            backdrop.classList.toggle('active');
        }
    </script>

    @yield('scripts')
</body>

</html>
