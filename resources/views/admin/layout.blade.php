<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Portal') - ItroveJewel License Server</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Cinzel:wght@700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    
    <!-- Synchronous anti-flicker for sidebar collapsed state -->
    <script>
        (function() {
            if (localStorage.getItem('admin_sidebar_collapsed') === 'true') {
                document.documentElement.classList.add('sidebar-collapsed');
            }
        })();
    </script>

    <style>
        .font-mono-code {
            font-family: 'JetBrains Mono', monospace;
        }

        /* Sidebar sizing & smooth animations */
        #admin-sidebar {
            width: 16rem; /* 256px expanded */
            transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1), transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        #admin-main-wrapper {
            margin-left: 16rem;
            transition: margin-left 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Mobile drawer styles */
        @media (max-width: 767px) {
            #admin-sidebar {
                width: 16rem !important;
                transform: translateX(-100%);
            }
            #admin-sidebar.mobile-open {
                transform: translateX(0) !important;
            }
            #admin-main-wrapper {
                margin-left: 0 !important;
            }
        }

        /* Desktop minimized / collapsed state (80px icon-only mode) */
        @media (min-width: 768px) {
            html.sidebar-collapsed #admin-sidebar {
                width: 5rem !important; /* 80px */
            }
            html.sidebar-collapsed #admin-main-wrapper {
                margin-left: 5rem !important;
            }
            html.sidebar-collapsed .sidebar-text,
            html.sidebar-collapsed .sidebar-section-title,
            html.sidebar-collapsed .sidebar-chevron,
            html.sidebar-collapsed .sidebar-submenu,
            html.sidebar-collapsed .sidebar-brand-text,
            html.sidebar-collapsed .sidebar-badge {
                display: none !important;
            }
            html.sidebar-collapsed .sidebar-item {
                justify-content: center !important;
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }
            html.sidebar-collapsed .collapse-icon-rotate {
                transform: rotate(180deg);
            }
            html.sidebar-collapsed .sidebar-brand-logo-only {
                display: flex !important;
                justify-content: center;
                width: 100%;
            }
        }

        /* Tooltip style when minimized */
        .sidebar-tooltip {
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.15s ease;
        }
        html.sidebar-collapsed .sidebar-item:hover .sidebar-tooltip {
            visibility: visible;
            opacity: 1;
        }

        /* Submenu rotation */
        .submenu-chevron {
            transition: transform 0.2s ease;
        }
        .submenu-open .submenu-chevron {
            transform: rotate(180deg);
        }

        /* Light theme adjustments for sidebar */
        html.light #admin-sidebar {
            background-color: #FFFFFF !important;
            border-color: #E2E8F0 !important;
            box-shadow: 2px 0 15px rgba(0, 0, 0, 0.04) !important;
        }
        html.light .sidebar-nav-active {
            background-color: #FEF3C7 !important;
            color: #92400E !important;
            border-left-color: #D97706 !important;
            font-weight: 700;
        }
        html.light .sidebar-nav-inactive {
            color: #475569 !important;
        }
        html.light .sidebar-nav-inactive:hover {
            background-color: #F8FAFC !important;
            color: #0F172A !important;
        }
        html.light .sidebar-section-title {
            color: #94A3B8 !important;
        }
        html.light .sidebar-submenu-item {
            color: #64748B !important;
        }
        html.light .sidebar-submenu-item:hover {
            color: #D97706 !important;
            background-color: #F8FAFC !important;
        }
        html.light .sidebar-submenu-active {
            color: #B45309 !important;
            font-weight: 700;
        }
        html.light .sidebar-bottom-panel {
            background-color: #F8FAFC !important;
            border-color: #E2E8F0 !important;
        }
    </style>
    <x-theme-styles />
</head>
<body class="min-h-screen flex flex-col justify-between">

    <!-- Mobile Overlay Backdrop -->
    <div id="sidebar-backdrop" 
        onclick="toggleMobileSidebar()" 
        class="fixed inset-0 z-40 bg-slate-950/70 backdrop-blur-sm hidden md:hidden transition-opacity">
    </div>

    <!-- Collapsible & Minimizable Sidebar -->
    <aside id="admin-sidebar" class="fixed top-0 bottom-0 left-0 z-50 flex flex-col justify-between bg-slate-950/95 border-r border-slate-800/80 backdrop-blur-xl select-none">
        
        <!-- Sidebar Header / Brand -->
        <div>
            <div class="h-16 px-4 border-b border-slate-800/80 flex items-center justify-between">
                <a href="{{ route('admin.clients.index') }}" class="flex items-center space-x-3 overflow-hidden">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-600 via-amber-400 to-yellow-200 flex items-center justify-center shadow-lg shadow-amber-500/20 shrink-0">
                        <svg class="w-5 h-5 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                        </svg>
                    </div>
                    <div class="sidebar-brand-text truncate">
                        <div class="flex items-center space-x-2">
                            <span class="text-base font-bold tracking-wider gold-gradient font-serif">ItroveJewel</span>
                            <span class="text-[9px] font-bold tracking-widest text-amber-400 bg-amber-950/80 border border-amber-500/30 px-1.5 py-0.5 rounded uppercase">
                                Admin
                            </span>
                        </div>
                        <p class="text-[9px] text-slate-400 tracking-wider">LICENSE & HARDWARE</p>
                    </div>
                </a>

                <!-- Desktop Minimize / Expand Toggle Button -->
                <button type="button" 
                    onclick="toggleSidebarCollapse()" 
                    class="hidden md:flex p-1.5 rounded-lg text-slate-400 hover:text-amber-400 hover:bg-slate-800/60 transition focus:outline-none"
                    title="Minimize / Expand Sidebar"
                    aria-label="Toggle Sidebar Size">
                    <svg class="w-4 h-4 collapse-icon-rotate transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                    </svg>
                </button>

                <!-- Mobile Close Button -->
                <button type="button" 
                    onclick="toggleMobileSidebar()" 
                    class="md:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/60 transition focus:outline-none"
                    aria-label="Close Sidebar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="p-3 space-y-1.5 overflow-y-auto max-h-[calc(100vh-140px)]">
                
                <!-- Section: Management -->
                <div class="sidebar-section-title px-3 pt-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                    Management
                </div>

                <!-- Menu Item: Dashboard Overview -->
                <div class="relative group">
                    <a href="{{ route('admin.dashboard') }}" 
                        class="sidebar-item flex items-center space-x-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition border-l-2 {{ request()->routeIs('admin.dashboard') && !request()->has('filter') ? 'bg-amber-500/10 text-amber-300 border-amber-400 sidebar-nav-active' : 'border-transparent text-slate-400 hover:text-white hover:bg-slate-900/60 sidebar-nav-inactive' }}">
                        <svg class="w-4 h-4 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                        <span class="sidebar-text flex-1 truncate">Dashboard</span>
                    </a>
                    <!-- Tooltip when minimized -->
                    <div class="sidebar-tooltip absolute left-16 top-2 z-50 px-2.5 py-1 rounded-md bg-slate-900 border border-slate-700 text-white text-xs font-medium shadow-xl pointer-events-none whitespace-nowrap">
                        Dashboard
                    </div>
                </div>

                <!-- Menu Item: Expandable Clients & Stores Group -->
                @php
                    $isClientsRoute = request()->routeIs('admin.clients.*');
                @endphp
                <div class="relative group" id="clients-menu-group">
                    <button type="button" 
                        onclick="toggleSubmenu('clients-submenu')" 
                        class="w-full sidebar-item flex items-center justify-between space-x-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition border-l-2 {{ $isClientsRoute ? 'bg-amber-500/10 text-amber-300 border-amber-400 sidebar-nav-active' : 'border-transparent text-slate-400 hover:text-white hover:bg-slate-900/60 sidebar-nav-inactive' }}">
                        <div class="flex items-center space-x-3 truncate">
                            <svg class="w-4 h-4 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <span class="sidebar-text truncate">Clients & Stores</span>
                        </div>
                        <svg id="clients-submenu-chevron" class="w-3.5 h-3.5 sidebar-chevron submenu-chevron transition-transform duration-200 {{ $isClientsRoute ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <!-- Tooltip when minimized -->
                    <div class="sidebar-tooltip absolute left-16 top-2 z-50 px-2.5 py-1 rounded-md bg-slate-900 border border-slate-700 text-white text-xs font-medium shadow-xl pointer-events-none whitespace-nowrap">
                        Clients & Stores
                    </div>

                    <!-- Expandable Submenu -->
                    <div id="clients-submenu" class="sidebar-submenu pl-7 pr-2 py-1 space-y-1 {{ $isClientsRoute ? '' : 'hidden' }}">
                        <a href="{{ route('admin.clients.index') }}" 
                            class="sidebar-submenu-item flex items-center space-x-2 px-3 py-1.5 rounded-lg text-xs transition {{ request()->routeIs('admin.clients.index') ? 'text-amber-300 font-bold sidebar-submenu-active' : 'text-slate-400 hover:text-white' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.clients.index') ? 'bg-amber-400' : 'bg-slate-600' }}"></span>
                            <span>All Clients List</span>
                        </a>

                        <a href="{{ route('admin.clients.create') }}" 
                            class="sidebar-submenu-item flex items-center space-x-2 px-3 py-1.5 rounded-lg text-xs transition {{ request()->routeIs('admin.clients.create') ? 'text-amber-300 font-bold sidebar-submenu-active' : 'text-slate-400 hover:text-white' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('admin.clients.create') ? 'bg-amber-400' : 'bg-slate-600' }}"></span>
                            <span>Register New Client</span>
                        </a>
                    </div>
                </div>

                <!-- Menu Item: USB Hardware Licenses -->
                <div class="relative group">
                    <a href="{{ route('admin.clients.index') }}" 
                        class="sidebar-item flex items-center space-x-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition border-l-2 border-transparent text-slate-400 hover:text-white hover:bg-slate-900/60 sidebar-nav-inactive">
                        <svg class="w-4 h-4 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        <span class="sidebar-text flex-1 truncate">USB Hardware Locks</span>
                    </a>
                    <!-- Tooltip when minimized -->
                    <div class="sidebar-tooltip absolute left-16 top-2 z-50 px-2.5 py-1 rounded-md bg-slate-900 border border-slate-700 text-white text-xs font-medium shadow-xl pointer-events-none whitespace-nowrap">
                        USB Hardware Locks
                    </div>
                </div>

                <!-- Menu Item: Fresh USB Installers & Packages -->
                <div class="relative group">
                    <a href="{{ route('admin.packages.index') }}" 
                        class="sidebar-item flex items-center space-x-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition border-l-2 {{ request()->routeIs('admin.packages.*') ? 'bg-amber-500/10 text-amber-300 border-amber-400 sidebar-nav-active' : 'border-transparent text-slate-400 hover:text-white hover:bg-slate-900/60 sidebar-nav-inactive' }}">
                        <svg class="w-4 h-4 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <span class="sidebar-text flex-1 truncate">Fresh USB Installers</span>
                        <span class="sidebar-badge text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300 border border-amber-500/30">
                            ZIP
                        </span>
                    </a>
                    <!-- Tooltip when minimized -->
                    <div class="sidebar-tooltip absolute left-16 top-2 z-50 px-2.5 py-1 rounded-md bg-slate-900 border border-slate-700 text-white text-xs font-medium shadow-xl pointer-events-none whitespace-nowrap">
                        Fresh USB Installers (.zip)
                    </div>
                </div>

                <!-- Menu Item: Client Updates & Releases -->
                <div class="relative group">
                    <a href="{{ route('admin.releases.index') }}" 
                        class="sidebar-item flex items-center space-x-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition border-l-2 {{ request()->routeIs('admin.releases.*') ? 'bg-amber-500/10 text-amber-300 border-amber-400 sidebar-nav-active' : 'border-transparent text-slate-400 hover:text-white hover:bg-slate-900/60 sidebar-nav-inactive' }}">
                        <svg class="w-4 h-4 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <span class="sidebar-text flex-1 truncate">Publish Client Updates</span>
                        <span class="sidebar-badge text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            LIVE
                        </span>
                    </a>
                    <!-- Tooltip when minimized -->
                    <div class="sidebar-tooltip absolute left-16 top-2 z-50 px-2.5 py-1 rounded-md bg-slate-900 border border-slate-700 text-white text-xs font-medium shadow-xl pointer-events-none whitespace-nowrap">
                        Publish Client Updates
                    </div>
                </div>

                <!-- Section: System & Server -->
                <div class="sidebar-section-title px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                    System & Server
                </div>

                <!-- Menu Item: License Verification API -->
                <div class="relative group">
                    <div class="sidebar-item flex items-center space-x-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-900/60 transition cursor-pointer"
                        onclick="alert('License Verification Endpoint: POST /api/license/verify-usb\n\nHardware pendrives query this central server API using HMAC-SHA256 signatures to authorize execution.');">
                        <svg class="w-4 h-4 shrink-0 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span class="sidebar-text flex-1 truncate">License Verify API</span>
                        <span class="sidebar-badge text-[9px] font-mono-code px-1.5 py-0.5 rounded bg-cyan-950/80 border border-cyan-500/40 text-cyan-300">
                            POST
                        </span>
                    </div>
                    <!-- Tooltip when minimized -->
                    <div class="sidebar-tooltip absolute left-16 top-2 z-50 px-2.5 py-1 rounded-md bg-slate-900 border border-slate-700 text-white text-xs font-medium shadow-xl pointer-events-none whitespace-nowrap">
                        License Verify API (POST /api/license/verify-usb)
                    </div>
                </div>

                <!-- Menu Item: Isolated Admin Database -->
                <div class="relative group">
                    <div class="sidebar-item flex items-center space-x-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-900/60 transition cursor-default">
                        <svg class="w-4 h-4 shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                        </svg>
                        <span class="sidebar-text flex-1 truncate">Admin DB: admin.sqlite</span>
                        <span class="sidebar-badge w-2 h-2 rounded-full bg-emerald-400"></span>
                    </div>
                    <!-- Tooltip when minimized -->
                    <div class="sidebar-tooltip absolute left-16 top-2 z-50 px-2.5 py-1 rounded-md bg-slate-900 border border-slate-700 text-white text-xs font-medium shadow-xl pointer-events-none whitespace-nowrap">
                        Isolated Database: admin.sqlite
                    </div>
                </div>

            </nav>
        </div>

        <!-- Sidebar Bottom Footer / Controls -->
        <div class="p-3 border-t border-slate-800/80 sidebar-bottom-panel space-y-2">
            
            <!-- Quick Expand / Minimize toggle button at bottom -->
            <button type="button" 
                onclick="toggleSidebarCollapse()" 
                class="w-full sidebar-item flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-amber-400 hover:bg-slate-800/60 transition focus:outline-none">
                <div class="flex items-center space-x-2 truncate">
                    <svg class="w-4 h-4 shrink-0 collapse-icon-rotate transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                    </svg>
                    <span class="sidebar-text sidebar-footer-text truncate">Minimize Sidebar</span>
                </div>
                <span class="sidebar-badge text-[10px] text-slate-500 font-mono-code">Ctrl+B</span>
            </button>

            <!-- Server Online Pill -->
            <div class="sidebar-item flex items-center space-x-2 px-3 py-1.5 rounded-lg bg-emerald-950/60 border border-emerald-500/30 text-[11px] font-mono-code text-emerald-300">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                <span class="sidebar-text truncate">SERVER ONLINE</span>
            </div>
        </div>
    </aside>

    <!-- Main Content Wrapper (Adjusts margin based on sidebar state) -->
    <div id="admin-main-wrapper" class="flex flex-col min-h-screen">
        
        <!-- Top App Bar -->
        <header class="border-b border-slate-800/80 bg-slate-950/70 backdrop-blur sticky top-0 z-30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                
                <!-- Left: Hamburger (Mobile) + Minimize Toggle (Desktop) + Breadcrumb -->
                <div class="flex items-center space-x-3">
                    
                    <!-- Mobile Hamburger -->
                    <button type="button" 
                        onclick="toggleMobileSidebar()" 
                        class="md:hidden p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/60 transition focus:outline-none"
                        aria-label="Open Sidebar Menu">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <!-- Desktop Minimize / Expand Toggle Button -->
                    <button type="button" 
                        onclick="toggleSidebarCollapse()" 
                        class="hidden md:flex p-2 rounded-xl text-slate-400 hover:text-amber-400 hover:bg-slate-800/60 transition focus:outline-none"
                        title="Toggle Minimize / Expand Sidebar (Ctrl+B)">
                        <svg class="w-5 h-5 collapse-icon-rotate transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h16"></path>
                        </svg>
                    </button>

                    <div class="hidden sm:block h-5 w-px bg-slate-800"></div>

                    <!-- Current Context / Section Indicator -->
                    <div class="flex items-center space-x-2 text-xs font-semibold text-slate-300">
                        <span class="gold-gradient font-serif">ItroveJewel</span>
                        <span class="text-slate-600">&bull;</span>
                        <span class="text-slate-400">@yield('title', 'Admin Dashboard')</span>
                    </div>
                </div>

                <!-- Right Actions: Theme Toggle + Add Client -->
                <div class="flex items-center space-x-3 sm:space-x-4">
                    
                    <!-- Dark / Light Theme Toggle -->
                    <x-theme-toggle />

                    <!-- Quick Add Client Button -->
                    <a href="{{ route('admin.clients.create') }}" 
                        class="px-3.5 sm:px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-yellow-400 hover:from-amber-400 hover:to-yellow-300 text-slate-950 font-bold text-xs tracking-wide shadow-md shadow-amber-500/20 transition flex items-center space-x-1.5">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span class="hidden sm:inline">Add New Client</span>
                        <span class="sm:hidden">Add New</span>
                    </a>
                </div>
            </div>
        </header>

        <!-- Flash Messages & Alerts -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 w-full">
            @if (session('success'))
                <div class="p-4 mb-4 rounded-2xl bg-emerald-950/70 border border-emerald-500/50 text-emerald-200 text-xs flex items-center space-x-3">
                    <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 mb-4 rounded-2xl bg-red-950/70 border border-red-500/50 text-red-200 text-xs space-y-1">
                    <div class="font-bold flex items-center space-x-2 text-red-300">
                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Please fix the following issues:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 text-[11px]">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- Main Content Area -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full flex-1">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-800/80 bg-slate-950/50 py-4 mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-2">
                <span>ItroveJewel &bull; Central License Verification & Pendrive Authorization Server</span>
                <span class="font-mono-code text-slate-400 text-[11px]">Server API: /api/license/verify-usb</span>
            </div>
        </footer>
    </div>

    <!-- Sidebar Script for Minimizing, Expanding, and Persistence -->
    <script>
        // Toggle desktop minimized / expanded mode
        function toggleSidebarCollapse() {
            const isCollapsed = document.documentElement.classList.toggle('sidebar-collapsed');
            localStorage.setItem('admin_sidebar_collapsed', isCollapsed ? 'true' : 'false');
        }

        // Toggle mobile drawer
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            const isOpen = sidebar.classList.toggle('mobile-open');
            if (isOpen) {
                backdrop.classList.remove('hidden');
            } else {
                backdrop.classList.add('hidden');
            }
        }

        // Toggle expandable submenus
        function toggleSubmenu(id) {
            // If sidebar is collapsed, auto-expand it first
            if (document.documentElement.classList.contains('sidebar-collapsed')) {
                toggleSidebarCollapse();
            }

            const submenu = document.getElementById(id);
            const chevron = document.getElementById(id + '-chevron');
            if (submenu) {
                const isHidden = submenu.classList.toggle('hidden');
                if (chevron) {
                    if (isHidden) {
                        chevron.classList.remove('rotate-180');
                    } else {
                        chevron.classList.add('rotate-180');
                    }
                }
            }
        }

        // Keyboard shortcut: Ctrl + B or Cmd + B to toggle sidebar
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && (e.key === 'b' || e.key === 'B')) {
                e.preventDefault();
                toggleSidebarCollapse();
            }
        });
    </script>

</body>
</html>
