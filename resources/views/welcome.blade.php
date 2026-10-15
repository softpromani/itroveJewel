<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ItroveJewel - Secured Portable Edition</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Cinzel:wght@700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #07090E;
            color: #F1F5F9;
            font-family: 'Plus Jakarta Sans', sans-serif;
            user-select: none;
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
            box-shadow: 0 0 30px rgba(234, 179, 8, 0.08);
        }
        .glass-panel {
            background: rgba(13, 17, 26, 0.9);
            backdrop-filter: blur(20px);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between p-6">

    <!-- Header -->
    <header class="flex items-center justify-between border-b border-slate-800 pb-4 max-w-5xl w-full mx-auto">
        <div class="flex items-center space-x-3">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-amber-600 via-amber-400 to-yellow-200 flex items-center justify-center shadow-lg shadow-amber-500/20">
                <svg class="w-6 h-6 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold tracking-wider gold-gradient font-serif">ItroveJewel</h1>
                <p class="text-[11px] text-slate-400 tracking-wider">HARDWARE LOCKED * ZERO-TRACE PORTABLE</p>
            </div>
        </div>

        <!-- Security Badge: USB Hardware Locked -->
        <div class="flex items-center space-x-3">
            <div class="flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-emerald-950/70 border border-emerald-500/40 text-xs">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="text-emerald-300 font-semibold font-mono-code">
                    USB SERIAL LOCK: ACTIVE
                </span>
            </div>
        </div>
    </header>

    <!-- Center Card -->
    <main class="my-auto max-w-2xl w-full mx-auto">
        <div class="glass-panel gold-border rounded-3xl p-8 shadow-2xl space-y-6 text-center">
            
            <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-tr from-amber-500 to-yellow-300 flex items-center justify-center shadow-xl shadow-amber-500/25">
                <svg class="w-8 h-8 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>

            <div>
                <span class="text-[11px] font-bold tracking-widest text-emerald-400 uppercase bg-emerald-950/70 border border-emerald-500/40 px-3 py-1 rounded-full">
                    Hardware License Verified
                </span>
                <h2 class="text-2xl font-bold text-white tracking-wide mt-3">ItroveJewel Secured Workspace</h2>
                <p class="text-xs text-slate-400 mt-1 max-w-md mx-auto">
                    This software copy is bound to your physical USB pendrive hardware serial number.
                </p>
            </div>

            <!-- Active Hardware Binding Details -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 text-left space-y-3">
                <div class="flex items-center justify-between text-xs pb-2 border-b border-slate-800/80">
                    <span class="text-slate-400 font-semibold flex items-center space-x-1.5">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <span>Active Hardware Lock Binding</span>
                    </span>
                    <span class="text-emerald-400 font-mono-code text-[11px]">Drive: {{ $hardware['drive_letter'] }}:</span>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-950/70 border border-amber-500/30 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-amber-400">Bound Physical USB Serial</span>
                        <p class="font-mono-code text-sm font-bold text-white mt-0.5 select-all">
                            {{ $verification['locked_serial'] ?? $hardware['serial_number'] }}
                        </p>
                    </div>
                    <span class="text-[10px] font-semibold text-emerald-400 bg-emerald-950/80 border border-emerald-500/40 px-2.5 py-1 rounded-full">
                        MATCHED * UNLOCKED
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-xl bg-slate-950/50 border border-slate-800/70">
                        <span class="text-slate-500 text-[10px] uppercase font-semibold block">Device Model</span>
                        <span class="text-slate-300 font-medium truncate block mt-0.5">
                            {{ $hardware['model'] }}
                        </span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-950/50 border border-slate-800/70">
                        <span class="text-slate-500 text-[10px] uppercase font-semibold block">Security Protection</span>
                        <span class="text-emerald-400 font-medium block mt-0.5">
                            HMAC Anti-Cloning Shield
                        </span>
                    </div>
                </div>
            </div>

            <!-- Features Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-left">
                <div class="p-3.5 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-1">
                    <div class="w-2.5 h-2.5 rounded-full bg-amber-400"></div>
                    <p class="text-xs font-bold text-white">Pendrive Database</p>
                    <p class="text-[11px] text-slate-400 leading-tight">Zero-trace SQLite stored strictly in <code class="text-amber-300 font-mono">data/database/</code>.</p>
                </div>

                <div class="p-3.5 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-1">
                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-400"></div>
                    <p class="text-xs font-bold text-white">Copy-Protection</p>
                    <p class="text-[11px] text-slate-400 leading-tight">Blocks execution if copied to unauthorized pendrives or PCs.</p>
                </div>

                <div class="p-3.5 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-1">
                    <div class="w-2.5 h-2.5 rounded-full bg-cyan-400"></div>
                    <p class="text-xs font-bold text-white">Dead-Man's Switch</p>
                    <p class="text-[11px] text-slate-400 leading-tight">Unplugging USB shuts down application within 500ms.</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800/80 pt-3 text-center text-xs text-slate-500 flex items-center justify-between max-w-5xl w-full mx-auto">
        <span>ItroveJewel &copy; {{ date('Y') }} * Portable Jewel Management System</span>
        <span class="font-mono text-slate-400 text-[11px]">Hardware Serial: {{ $verification['locked_serial'] ?? $hardware['serial_number'] }}</span>
    </footer>

</body>
</html>
