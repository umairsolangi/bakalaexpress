<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Bakala Express')</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
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
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/styleHome.css') }}">
    @yield('styles')
</head>

<body>
    <!-- Modern Header -->
    <header
        class="fixed w-full top-0 z-50 transition-all duration-300 bg-white/90 backdrop-blur-md border-b border-gray-100"
        id="main-header">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center gap-3">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                        @include('components.logo')
                        <div class="flex flex-col">
                            <span
                                class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-primary-dark to-primary tracking-tight group-hover:opacity-90 transition-opacity">Bakala
                                Express</span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex space-x-8 items-center">
                    <a href="{{ route('home') }}"
                        class="text-gray-600 hover:text-primary font-medium transition-colors {{ request()->routeIs('home') ? 'text-primary' : '' }}">Home</a>
                    <a href="#about" class="text-gray-600 hover:text-primary font-medium transition-colors">About</a>
                    <a href="#sellers"
                        class="text-gray-600 hover:text-primary font-medium transition-colors">Partners</a>
                    <a href="{{ route('order.all') }}"
                        class="text-gray-600 hover:text-primary font-medium transition-colors {{ request()->routeIs('order.*') ? 'text-primary' : '' }}"><i
                            class="fas fa-box mr-1"></i> Orders</a>
                </nav>

                <!-- Actions -->
                <div class="hidden md:flex items-center space-x-6">
                    <!-- Cart -->
                    <a href="{{ route('cart.view') }}"
                        class="relative text-gray-600 hover:text-primary transition-colors group">
                        <div class="p-2 rounded-full group-hover:bg-primary-light/50 transition-colors">
                            <i class="fas fa-shopping-cart text-xl"></i>
                        </div>
                        <span
                            class="absolute -top-1 -right-1 bg-accent text-white text-xs font-bold px-1.5 py-0.5 rounded-full shadow-sm">0</span>
                    </a>

                    <!-- Auth -->
                    @auth
                        <div class="relative" id="user-menu-wrapper">
                            <button
                                id="user-menu-button"
                                type="button"
                                aria-haspopup="true"
                                aria-expanded="false"
                                class="flex items-center gap-2 text-gray-700 hover:text-primary font-medium focus:outline-none">
                                <div
                                    class="w-10 h-10 rounded-full bg-primary-light flex items-center justify-center text-primary border-2 border-transparent transition-all">
                                    <i class="fas fa-user"></i>
                                </div>
                                <span class="hidden lg:block">{{ Str::limit(Auth::user()->name, 10) }}</span>
                                <i
                                    class="fas fa-chevron-down text-xs text-gray-400 transition-colors"></i>
                            </button>
                            <!-- Dropdown -->
                            <div
                                id="user-menu-dropdown"
                                class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg py-2 border border-gray-100 hidden z-50">
                                @if((int) (Auth::user()->sellerType ?? 0) === 1)
                                    <a href="{{ route('admin.dashboard') }}"
                                        class="block px-4 py-2 text-gray-700 hover:bg-primary-light hover:text-primary"><i
                                            class="fas fa-tachometer-alt w-5"></i> Dashboard</a>
                                @else
                                    <a href="{{ route('profile.edit') }}"
                                        class="block px-4 py-2 text-gray-700 hover:bg-primary-light hover:text-primary"><i
                                            class="fas fa-user-circle w-5"></i> Profile</a>
                                    <a href="{{ route('order.history') }}"
                                        class="block px-4 py-2 text-gray-700 hover:bg-primary-light hover:text-primary"><i
                                            class="fas fa-history w-5"></i> Order History</a>
                                @endif
                                <div class="border-t border-gray-100 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50"><i
                                            class="fas fa-sign-out-alt w-5"></i> Logout</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}"
                            class="px-6 py-2.5 bg-primary hover:bg-primary-dark text-white font-semibold rounded-full shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                            Sign In
                        </a>
                    @endauth
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button
                        type="button"
                        class="mobile-menu-button text-gray-600 hover:text-primary focus:outline-none p-2 rounded-md hover:bg-gray-50"
                        aria-controls="mobile-menu"
                        aria-expanded="false">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>

            <div id="mobile-menu" class="mobile-menu hidden md:hidden border-t border-gray-100 py-4">
                <nav class="flex flex-col gap-2">
                    <a href="{{ route('home') }}" class="px-3 py-2 rounded-lg text-gray-700 hover:bg-primary-light hover:text-primary font-medium">Home</a>
                    <a href="#about" class="px-3 py-2 rounded-lg text-gray-700 hover:bg-primary-light hover:text-primary font-medium">About</a>
                    <a href="#sellers" class="px-3 py-2 rounded-lg text-gray-700 hover:bg-primary-light hover:text-primary font-medium">Partners</a>
                    <a href="{{ route('cart.view') }}" class="px-3 py-2 rounded-lg text-gray-700 hover:bg-primary-light hover:text-primary font-medium">
                        <i class="fas fa-shopping-cart w-5"></i> Cart
                    </a>
                    @auth
                        <a href="{{ route('order.all') }}" class="px-3 py-2 rounded-lg text-gray-700 hover:bg-primary-light hover:text-primary font-medium">
                            <i class="fas fa-box w-5"></i> Orders
                        </a>
                        @if((int) (Auth::user()->sellerType ?? 0) === 1)
                            <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 rounded-lg text-gray-700 hover:bg-primary-light hover:text-primary font-medium">
                                <i class="fas fa-tachometer-alt w-5"></i> Dashboard
                            </a>
                        @else
                            <a href="{{ route('profile.edit') }}" class="px-3 py-2 rounded-lg text-gray-700 hover:bg-primary-light hover:text-primary font-medium">
                                <i class="fas fa-user-circle w-5"></i> Profile
                            </a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-3 py-2 rounded-lg text-red-600 hover:bg-red-50 font-medium">
                                <i class="fas fa-sign-out-alt w-5"></i> Logout
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="px-3 py-2 rounded-lg bg-primary text-white font-semibold text-center">Sign In</a>
                    @endauth
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="pt-24 min-h-screen">
        @yield('content')
    </main>

    <!-- Modern Footer -->
    <footer class="bg-gray-900 text-white pt-16 pb-8 border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">
                <!-- Brand -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="bg-white/10 p-2 rounded-full">
                            <i class="fas fa-shopping-basket text-primary text-2xl"></i>
                        </div>
                        <span class="text-2xl font-bold">Bakala Express</span>
                    </div>
                    <p class="text-gray-400 text-sm leading-relaxed">
                        Connecting you with the best neighborhood Bakalas. Fresh groceries, delivered with care and
                        speed.
                    </p>
                    <div class="flex space-x-4 pt-2">
                        <a href="https://www.facebook.com/bakalaexpress" target="_blank"
                            class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center text-gray-400 hover:bg-primary hover:text-white transition-all transform hover:-translate-y-1"><i
                                class="fab fa-facebook-f"></i></a>
                        <a href="https://www.linkedin.com/company/bakala-express/" target="_blank"
                            class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center text-gray-400 hover:bg-primary hover:text-white transition-all transform hover:-translate-y-1"><i
                                class="fab fa-linkedin-in"></i></a>
                        <a href="https://www.instagram.com/bakalaexpress/" target="_blank"
                            class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center text-gray-400 hover:bg-primary hover:text-white transition-all transform hover:-translate-y-1"><i
                                class="fab fa-instagram"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-6 border-b border-gray-800 pb-2 inline-block">Quick Links</h3>
                    <ul class="space-y-3">
                        <li><a href="{{ route('home') }}"
                                class="text-gray-400 hover:text-primary transition-colors flex items-center gap-2"><i
                                    class="fas fa-chevron-right text-xs"></i> Home</a></li>
                        <li><a href="#about"
                                class="text-gray-400 hover:text-primary transition-colors flex items-center gap-2"><i
                                    class="fas fa-chevron-right text-xs"></i> About Us</a></li>
                        <li><a href="#sellers"
                                class="text-gray-400 hover:text-primary transition-colors flex items-center gap-2"><i
                                    class="fas fa-chevron-right text-xs"></i> Partners</a></li>
                        <li><a href="{{ url('/seller/login') }}"
                                class="text-gray-400 hover:text-primary transition-colors flex items-center gap-2"><i
                                    class="fas fa-store text-xs"></i> Seller Login</a></li>
                        <li><a href="{{ url('/rider/login') }}"
                                class="text-gray-400 hover:text-primary transition-colors flex items-center gap-2"><i
                                    class="fas fa-motorcycle text-xs"></i> Rider Login</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h3 class="text-lg font-semibold mb-6 border-b border-gray-800 pb-2 inline-block">Support</h3>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3 text-gray-400">
                            <i class="fas fa-map-marker-alt mt-1 text-primary"></i>
                            <span>123 Bakala Street, Karachi, Pakistan</span>
                        </li>
                        <li class="flex items-center gap-3 text-gray-400">
                            <i class="fas fa-phone-alt text-primary"></i>
                            <span>+92 300 1234567</span>
                        </li>
                        <li class="flex items-center gap-3 text-gray-400">
                            <i class="fas fa-envelope text-primary"></i>
                            <span>support@bakalaexpress.com</span>
                        </li>
                    </ul>
                </div>

                <!-- Newsletter -->
                <div>
                    <h3 class="text-lg font-semibold mb-6 border-b border-gray-800 pb-2 inline-block">Newsletter</h3>
                    <p class="text-gray-400 text-sm mb-4">Subscribe to get updates on new offers!</p>
                    <form class="flex gap-2">
                        <input type="email" placeholder="Your email"
                            class="bg-gray-800 border-gray-700 text-white px-4 py-2 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none w-full text-sm">
                        <button type="button"
                            class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg transition-colors"><i
                                class="fas fa-paper-plane"></i></button>
                    </form>
                </div>
            </div>

            <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-gray-500 text-sm">&copy; {{ date('Y') }} Bakala Express. All rights reserved.</p>
                <div class="flex gap-4 text-gray-400 text-sm">
                    <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
                    <a href="#" class="hover:text-white transition-colors">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const userMenuButton = document.getElementById('user-menu-button');
            const userMenuDropdown = document.getElementById('user-menu-dropdown');
            const userMenuWrapper = document.getElementById('user-menu-wrapper');

            if (userMenuButton && userMenuDropdown && userMenuWrapper) {
                userMenuButton.addEventListener('click', function (event) {
                    event.stopPropagation();

                    const isHidden = userMenuDropdown.classList.contains('hidden');
                    userMenuDropdown.classList.toggle('hidden', !isHidden);
                    userMenuButton.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
                });

                document.addEventListener('click', function (event) {
                    if (!userMenuWrapper.contains(event.target)) {
                        userMenuDropdown.classList.add('hidden');
                        userMenuButton.setAttribute('aria-expanded', 'false');
                    }
                });
            }

            const mobileMenuButton = document.querySelector('.mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');

            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function () {
                    const isHidden = mobileMenu.classList.toggle('hidden');
                    mobileMenuButton.setAttribute('aria-expanded', isHidden ? 'false' : 'true');
                });
            }
        });
    </script>

    <!-- Store Closed Modal -->
    <div id="closedStoreModal" class="fixed inset-0 z-[999] flex items-center justify-center bg-black/60 backdrop-blur-sm hidden transition-all duration-300">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 md:p-8 mx-4 text-center transform transition-all border border-gray-100 relative animate__animated animate__zoomIn">
            <!-- Close Button -->
            <button type="button" onclick="closeClosedStoreModal()" class="absolute top-4 right-4 w-9 h-9 rounded-full bg-gray-100 text-gray-400 hover:bg-gray-200 hover:text-gray-700 flex items-center justify-center transition-colors">
                <i class="fas fa-times"></i>
            </button>

            <!-- Animated Closed Badge Icon -->
            <div class="w-20 h-20 bg-rose-50 border-4 border-rose-100 rounded-full flex items-center justify-center mx-auto mb-5 text-rose-500 shadow-inner">
                <i class="fas fa-store-slash text-3xl"></i>
            </div>

            <h3 class="text-2xl font-bold font-display text-gray-900 mb-2">Store Currently Closed</h3>
            <p class="text-gray-600 text-sm mb-5 leading-relaxed">
                <span id="modalStoreName" class="font-bold text-gray-900">This shop</span> is currently closed and not accepting orders right now.
            </p>

            <!-- Hours Badge Box -->
            <div class="bg-rose-50/70 border border-rose-100 rounded-2xl p-4 mb-6 text-left flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-rose-500 text-white flex items-center justify-center flex-shrink-0 mt-0.5 shadow-sm">
                    <i class="fas fa-clock text-sm"></i>
                </div>
                <div>
                    <div class="text-xs font-semibold text-rose-900 uppercase tracking-wider">Operational Hours</div>
                    <div class="text-sm font-bold text-rose-700 mt-0.5" id="modalStoreHours">07:00 AM – 10:00 PM</div>
                    <div class="text-[11px] text-rose-600/80 mt-0.5">Please check back during store hours to browse items and order.</div>
                </div>
            </div>

            <button type="button" onclick="closeClosedStoreModal()" class="w-full py-3 bg-gray-900 hover:bg-gray-800 text-white rounded-xl font-bold text-sm transition-all shadow-lg">
                Got it, thanks!
            </button>
        </div>
    </div>

    <script>
        function openClosedStoreModal(storeName, storeHours) {
            document.getElementById('modalStoreName').textContent = storeName;
            document.getElementById('modalStoreHours').textContent = storeHours;
            const modal = document.getElementById('closedStoreModal');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeClosedStoreModal() {
            const modal = document.getElementById('closedStoreModal');
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    </script>

    @if(session('store_closed_modal'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                openClosedStoreModal('{{ session("store_closed_modal.name") }}', '{{ session("store_closed_modal.hours") }}');
            });
        </script>
    @endif

    @yield('scripts')
</body>

</html>
