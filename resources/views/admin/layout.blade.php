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
    <style>
        body {
            background-color: #07090E;
            color: #F1F5F9;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .font-mono-code {
            font-family: 'JetBrains Mono', monospace;
        }
        .gold-gradient {
            background: linear-gradient(135deg, #FDE047 0%, #EAB308 50%, #B45309 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .gold-border {
            border: 1px solid rgba(234, 179, 8, 0.25);
            box-shadow: 0 0 30px rgba(234, 179, 8, 0.06);
        }
        .glass-panel {
            background: rgba(13, 17, 26, 0.88);
            backdrop-filter: blur(20px);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between">

    <!-- Top Navigation Bar -->
    <header class="border-b border-slate-800/80 bg-slate-950/70 backdrop-blur sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            
            <!-- Brand Logo -->
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.clients.index') }}" class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-600 via-amber-400 to-yellow-200 flex items-center justify-center shadow-lg shadow-amber-500/20">
                        <svg class="w-5 h-5 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="text-lg font-bold tracking-wider gold-gradient font-serif">ItroveJewel</span>
                            <span class="text-[10px] font-bold tracking-widest text-amber-400 bg-amber-950/80 border border-amber-500/30 px-2 py-0.5 rounded-md uppercase">
                                Server Admin
                            </span>
                        </div>
                        <p class="text-[10px] text-slate-400">CLIENT & USB HARDWARE LICENSE SYSTEM</p>
                    </div>
                </a>
            </div>

            <!-- Server Navigation Links & Actions -->
            <div class="flex items-center space-x-4">
                <nav class="hidden md:flex items-center space-x-2">
                    <a href="{{ route('admin.clients.index') }}" 
                        class="px-3.5 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.clients.*') ? 'bg-amber-500/10 text-amber-300 border border-amber-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                        Clients & USB Licenses
                    </a>
                </nav>

                <div class="h-5 w-px bg-slate-800 hidden md:block"></div>

                <div class="flex items-center space-x-3">
                    <a href="{{ route('admin.clients.create') }}" 
                        class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-yellow-400 hover:from-amber-400 hover:to-yellow-300 text-slate-950 font-bold text-xs tracking-wide shadow-md shadow-amber-500/20 transition flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Add New Client</span>
                    </a>
                    
                    <div class="hidden sm:flex items-center space-x-1.5 px-3 py-1.5 rounded-full bg-emerald-950/60 border border-emerald-500/40 text-[11px] font-mono-code text-emerald-300">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>SERVER ONLINE</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Notifications / Alerts -->
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

</body>
</html>
