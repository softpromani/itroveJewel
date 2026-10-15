<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ItroveJewel - Unauthorized Hardware Lockout</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Cinzel:wght@700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        .font-mono-code {
            font-family: 'JetBrains Mono', monospace;
        }
        body {
            user-select: none;
        }
        .danger-pulse {
            animation: dangerPulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes dangerPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: .85; transform: scale(1.03); }
        }
    </style>
    <x-theme-styles />
</head>
<body class="min-h-screen flex flex-col justify-between p-6">

    <!-- Header -->
    <header class="flex items-center justify-between border-b border-red-950/80 pb-4 max-w-4xl w-full mx-auto">
        <div class="flex items-center space-x-3">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-red-600 via-rose-500 to-amber-500 flex items-center justify-center shadow-lg shadow-red-500/30">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold tracking-wider text-white font-serif">ItroveJewel</h1>
                <p class="text-[11px] text-red-400 tracking-wider">HARDWARE LOCK ENFORCEMENT</p>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <x-theme-toggle />

            <div class="flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-red-950/80 border border-red-500/60 text-xs">
                <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-ping"></span>
                <span class="text-red-300 font-bold tracking-wider">ACCESS DENIED</span>
            </div>
        </div>
    </header>

    <!-- Main Lockout Card -->
    <main class="my-auto max-w-xl w-full mx-auto">
        <div class="lockout-panel rounded-3xl p-8 shadow-2xl space-y-6 text-center relative overflow-hidden">
            
            <!-- Red Warning Glow -->
            <div class="absolute -top-20 -left-20 w-44 h-44 bg-red-600/15 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Big Pulsing Security Shield Icon -->
            <div class="w-20 h-20 mx-auto rounded-3xl bg-gradient-to-tr from-red-600 to-rose-400 flex items-center justify-center shadow-2xl shadow-red-600/40 danger-pulse">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>

            <!-- Lockout Headers -->
            <div>
                <span class="text-[11px] font-extrabold tracking-widest text-red-400 uppercase bg-red-950/90 border border-red-500/50 px-3.5 py-1 rounded-full">
                    Copy-Protection Violation Detected
                </span>
                <h2 class="text-2xl font-bold text-white tracking-wide mt-3">Unauthorized Hardware Lockout</h2>
                <p class="text-xs text-red-200/80 mt-1.5 max-w-md mx-auto">
                    This installation of ItroveJewel is cryptographically locked to a specific physical USB drive and refuses to execute on unauthorized storage hardware.
                </p>
            </div>

            <!-- Comparison Table: Locked Serial vs Detected Serial -->
            <div class="bg-black/40 border border-red-950 rounded-2xl p-5 space-y-3.5 text-left">
                <div class="flex items-center justify-between text-xs pb-2 border-b border-red-950">
                    <span class="text-red-400 font-semibold flex items-center space-x-1.5">
                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <span>Hardware Serial Comparison</span>
                    </span>
                    <span class="text-red-400 font-mono text-[11px]">Drive: {{ $hardware['drive_letter'] }}:</span>
                </div>

                <!-- Authorized Serial (Locked) -->
                <div class="p-3.5 rounded-xl bg-slate-900/80 border border-slate-700/60">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] uppercase font-bold text-slate-400">Authorized USB Serial (Licensed)</span>
                        <span class="text-[10px] font-semibold text-emerald-400 bg-emerald-950/70 border border-emerald-500/30 px-2 py-0.5 rounded-full">
                            Expected Hardware
                        </span>
                    </div>
                    <p class="font-mono-code text-sm font-bold text-slate-200 mt-1">
                        {{ $verification['locked_serial'] ?? 'NOT_AVAILABLE' }}
                    </p>
                </div>

                <!-- Detected Serial (Current Drive) -->
                <div class="p-3.5 rounded-xl bg-red-950/30 border border-red-500/50">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] uppercase font-bold text-red-300">Detected Drive Serial (Current)</span>
                        <span class="text-[10px] font-semibold text-red-400 bg-red-950/80 border border-red-500/40 px-2 py-0.5 rounded-full">
                            Mismatch &bull; Rejected
                        </span>
                    </div>
                    <p class="font-mono-code text-sm font-bold text-red-200 mt-1">
                        {{ $hardware['serial_number'] ?: 'UNKNOWN / UNAUTHORIZED' }}
                    </p>
                </div>
            </div>

            <!-- Instruction Notice -->
            <div class="p-4 rounded-xl bg-red-950/40 border border-red-800/40 text-xs text-left space-y-2">
                <div class="flex items-start space-x-2.5">
                    <svg class="w-4 h-4 text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <p class="text-[11px] leading-relaxed text-red-200/90">
                        <strong>Anti-Piracy Dongle Enforcement:</strong> Copying this software folder to another USB pendrive or local hard drive (C:\, D:\) is strictly prohibited. Please insert the <strong>original physical USB pendrive</strong> and re-launch the application.
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-2 flex flex-col sm:flex-row gap-3">
                <button onclick="window.location.reload()" 
                    class="flex-1 py-3 px-5 rounded-2xl bg-slate-900 border border-slate-700 hover:border-slate-500 text-slate-200 font-semibold text-xs tracking-wider transition">
                    RE-SCAN HARDWARE
                </button>
                <button onclick="window.close()" 
                    class="flex-1 py-3 px-5 rounded-2xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-bold text-xs tracking-wider shadow-lg shadow-red-600/30 transition">
                    EXIT APPLICATION
                </button>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-red-950/80 pt-3 text-center text-xs text-red-500/70 flex items-center justify-between max-w-4xl w-full mx-auto">
        <span>ItroveJewel &bull; Tamper-Proof Cryptographic Lock</span>
        <span class="font-mono text-red-400/80 text-[11px]">Hardware Violation Code: 0xHW_MISMATCH</span>
    </footer>

</body>
</html>
