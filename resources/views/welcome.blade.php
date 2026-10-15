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
        .font-mono-code {
            font-family: 'JetBrains Mono', monospace;
        }
        body {
            user-select: none;
        }
    </style>
    <x-theme-styles />
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

        <!-- Security Badge, Theme Toggle, Update Status & Admin Link -->
        <div class="flex items-center space-x-3">
            <!-- Version & Update Status Button -->
            <button id="update-btn" onclick="handleUpdateBtnClick()" title="Check for updates"
                class="flex items-center space-x-2 px-3 py-1.5 rounded-full bg-slate-900/90 border border-slate-700/70 hover:border-amber-500/50 text-xs font-semibold text-slate-300 transition shadow-sm cursor-pointer">
                <span id="update-indicator-dot" class="w-2 h-2 rounded-full bg-slate-500 transition-colors"></span>
                <span id="update-btn-label" class="font-mono-code">v{{ app(\App\Services\AppUpdateService::class)->getCurrentVersion() }}</span>
                <span id="update-pill" class="hidden px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-400 text-slate-950 uppercase tracking-wider animate-pulse">1-Click Upgrade</span>
            </button>

            <x-theme-toggle />

            @if(config('app.enable_admin_panel') && !app(\App\Services\UsbLockService::class)->isBuildEnvironment())
                <a href="{{ route('admin.clients.index') }}" 
                    class="px-3.5 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/40 text-amber-300 hover:bg-amber-500/20 text-xs font-semibold transition flex items-center space-x-1.5">
                    <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                    </svg>
                    <span>Web Admin Portal</span>
                </a>
            @endif

            @if(($verification['status'] ?? '') === 'DEV_BYPASS')
                <div class="flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-cyan-950/70 border border-cyan-500/40 text-xs">
                    <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                    <span class="text-cyan-300 font-semibold font-mono-code">
                        DEV MODE: HARDWARE LOCK BYPASSED
                    </span>
                </div>
            @else
                <div class="flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-emerald-950/70 border border-emerald-500/40 text-xs">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-emerald-300 font-semibold font-mono-code">
                        USB SERIAL LOCK: ACTIVE
                    </span>
                </div>
            @endif
        </div>
    </header>

    <!-- Center Card -->
    <main class="my-auto max-w-2xl w-full mx-auto">
        <!-- Live Update Available Alert Banner -->
        <div id="update-available-banner" class="hidden mb-4 p-4 rounded-2xl bg-gradient-to-r from-amber-500/15 via-yellow-500/10 to-amber-500/15 border border-amber-500/40 shadow-lg shadow-amber-500/5 flex items-center justify-between transition-all">
            <div class="flex items-center space-x-3 text-left">
                <div class="w-9 h-9 rounded-xl bg-amber-500/20 flex items-center justify-center text-amber-400 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs font-bold text-amber-300 tracking-wide uppercase">New Version Ready</span>
                        <span id="banner-new-version" class="text-[11px] font-mono-code font-bold bg-amber-400 text-slate-950 px-2 py-0.5 rounded-full">v...</span>
                    </div>
                    <p id="banner-title" class="text-xs text-slate-300 mt-0.5 font-medium">An update is available for your portable pendrive.</p>
                </div>
            </div>
            <button onclick="openUpdateModal()" class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-yellow-400 hover:from-amber-400 hover:to-yellow-300 text-slate-950 text-xs font-bold shadow-md shadow-amber-500/20 transition transform hover:scale-[1.02] cursor-pointer shrink-0">
                Upgrade in 1-Click
            </button>
        </div>

        <div class="glass-panel gold-border rounded-3xl p-8 shadow-2xl space-y-6 text-center">
            
            <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-tr from-amber-500 to-yellow-300 flex items-center justify-center shadow-xl shadow-amber-500/25">
                <svg class="w-8 h-8 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>

            <div>
                @if(($verification['status'] ?? '') === 'DEV_BYPASS')
                    <span class="text-[11px] font-bold tracking-widest text-cyan-400 uppercase bg-cyan-950/70 border border-cyan-500/40 px-3 py-1 rounded-full">
                        Developer Mode Enabled
                    </span>
                    <h2 class="text-2xl font-bold text-white tracking-wide mt-3">Developer Workspace (Unlocked)</h2>
                    <p class="text-xs text-slate-400 mt-1 max-w-md mx-auto">
                        USB Hardware Lock is active strictly in packaged build files. Developers can work on any PC without hardware locks.
                    </p>
                @else
                    <span class="text-[11px] font-bold tracking-widest text-emerald-400 uppercase bg-emerald-950/70 border border-emerald-500/40 px-3 py-1 rounded-full">
                        Hardware License Verified
                    </span>
                    <h2 class="text-2xl font-bold text-white tracking-wide mt-3">ItroveJewel Secured Workspace</h2>
                    <p class="text-xs text-slate-400 mt-1 max-w-md mx-auto">
                        This software copy is bound to your physical USB pendrive hardware serial number.
                    </p>
                @endif
            </div>

            <!-- Hardware Information Panel -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 text-left space-y-3">
                <div class="flex items-center justify-between text-xs pb-2 border-b border-slate-800/80">
                    <span class="text-slate-400 font-semibold flex items-center space-x-1.5">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <span>Storage & Hardware Details</span>
                    </span>
                    <span class="text-slate-400 font-mono-code text-[11px]">Drive: {{ $hardware['drive_letter'] }}:</span>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-950/70 border border-amber-500/30 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-amber-400">Current Physical Drive Serial</span>
                        <p class="font-mono-code text-sm font-bold text-white mt-0.5 select-all">
                            {{ $hardware['serial_number'] ?: 'NONE_DETECTED' }}
                        </p>
                    </div>
                    @if(($verification['status'] ?? '') === 'DEV_BYPASS')
                        <span class="text-[10px] font-semibold text-cyan-400 bg-cyan-950/80 border border-cyan-500/40 px-2.5 py-1 rounded-full">
                            DEV BYPASS
                        </span>
                    @else
                        <span class="text-[10px] font-semibold text-emerald-400 bg-emerald-950/80 border border-emerald-500/40 px-2.5 py-1 rounded-full">
                            MATCHED &bull; UNLOCKED
                        </span>
                    @endif
                </div>

                @if(!empty($lock_data['shop_name']))
                    <div class="p-3.5 rounded-xl bg-amber-950/20 border border-amber-500/40 flex items-center justify-between text-xs">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-amber-400">Licensed Jewel Client</span>
                            <p class="text-sm font-bold text-white mt-0.5">
                                {{ $lock_data['shop_name'] }}
                                @if(!empty($lock_data['client_name']))
                                    <span class="text-xs text-slate-400 font-normal">&bull; {{ $lock_data['client_name'] }}</span>
                                @endif
                            </p>
                        </div>
                        <span class="text-[10px] font-mono-code text-emerald-400 bg-emerald-950/80 border border-emerald-500/40 px-2.5 py-1 rounded-full">
                            VERIFIED LICENSE
                        </span>
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-xl bg-slate-950/50 border border-slate-800/70">
                        <span class="text-slate-500 text-[10px] uppercase font-semibold block">Device Model</span>
                        <span class="text-slate-300 font-medium truncate block mt-0.5">
                            {{ $hardware['model'] }}
                        </span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-950/50 border border-slate-800/70">
                        <span class="text-slate-500 text-[10px] uppercase font-semibold block">Lock Policy</span>
                        <span class="text-slate-300 font-medium block mt-0.5">
                            {{ ($verification['status'] ?? '') === 'DEV_BYPASS' ? 'Packaged Build Only' : 'Hardware Locked (HMAC-SHA256)' }}
                        </span>
                    </div>
                </div>

                @if(($verification['status'] ?? '') === 'DEV_BYPASS')
                    <div class="p-3 rounded-xl bg-slate-950/90 border border-slate-800 flex items-center justify-between text-xs">
                        <span class="text-slate-400 text-[11px]">Preview Built-in Screens:</span>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('home', ['preview' => 'install']) }}" class="px-2.5 py-1 rounded-lg bg-amber-500/20 text-amber-300 border border-amber-500/40 hover:bg-amber-500/30 text-[11px] font-semibold transition">
                                Test Install Wizard
                            </a>
                            <a href="{{ route('home', ['preview' => 'lockout']) }}" class="px-2.5 py-1 rounded-lg bg-red-500/20 text-red-300 border border-red-500/40 hover:bg-red-500/30 text-[11px] font-semibold transition">
                                Test Lockout Screen
                            </a>
                        </div>
                    </div>
                @endif
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
                    <p class="text-xs font-bold text-white">Build Protection</p>
                    <p class="text-[11px] text-slate-400 leading-tight">Blocks execution if copied from the original USB drive.</p>
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
        <span class="font-mono text-slate-400 text-[11px]">Status: {{ $verification['status'] ?? 'ACTIVE' }}</span>
    </footer>

    <!-- 1-Click Upgrade Modal -->
    <div id="update-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-black/80 backdrop-blur-md transition-all">
        <div class="glass-panel gold-border rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl relative space-y-5 text-left animate-in fade-in zoom-in-95 duration-200">
            <!-- Modal Header -->
            <div class="flex items-start justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-amber-600 via-amber-400 to-yellow-200 flex items-center justify-center shadow-lg shadow-amber-500/20 text-slate-950 font-bold shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white tracking-wide">ItroveJewel Software Update</h3>
                        <p class="text-xs text-slate-400">1-Click Upgrade directly on your portable USB drive</p>
                    </div>
                </div>
                <button id="modal-close-btn" onclick="closeUpdateModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Version Comparison Badges -->
            <div class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-900/80 border border-slate-800">
                <div>
                    <span class="text-[10px] text-slate-500 uppercase font-semibold block">Currently Installed</span>
                    <span id="modal-current-version" class="font-mono-code font-bold text-slate-300 text-sm">v{{ app(\App\Services\AppUpdateService::class)->getCurrentVersion() }}</span>
                </div>
                <div class="text-amber-400 font-bold text-sm">
                    <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-amber-400 uppercase font-bold block">New Version</span>
                    <span id="modal-new-version" class="font-mono-code font-extrabold text-amber-300 text-sm">v...</span>
                </div>
            </div>

            <!-- Release Notes & Summary -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <h4 id="modal-release-title" class="text-xs font-bold uppercase tracking-wider text-amber-300">New Release</h4>
                    <span id="modal-release-size" class="text-[11px] font-mono-code text-slate-500"></span>
                </div>
                <div id="modal-release-notes" class="p-3.5 rounded-xl bg-slate-950/60 border border-slate-800/80 text-xs text-slate-300 space-y-1.5 max-h-36 overflow-y-auto">
                    <!-- Populated dynamically via JS -->
                </div>
            </div>

            <!-- Safety Guarantee Badge -->
            <div class="p-3 rounded-xl bg-emerald-950/30 border border-emerald-500/30 flex items-start space-x-2.5 text-xs">
                <svg class="w-4 h-4 text-emerald-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                <div class="text-emerald-200/90 leading-relaxed text-[11px]">
                    <span class="font-bold text-emerald-300">Zero-Data-Loss Safety:</span>
                    Your local database (<code class="text-emerald-300 font-mono">data/database/database.sqlite</code>) is automatically backed up prior to upgrading. Your customer data and hardware lock are 100% protected.
                </div>
            </div>

            <!-- Upgrade In-Progress Progress Bar & Live Status (Hidden initially) -->
            <div id="upgrade-progress-container" class="hidden space-y-2.5">
                <div class="flex items-center justify-between text-xs">
                    <span id="upgrade-status-text" class="text-amber-300 font-semibold flex items-center space-x-2">
                        <svg class="w-3.5 h-3.5 animate-spin text-amber-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>Starting upgrade...</span>
                    </span>
                    <span id="upgrade-percentage" class="font-mono-code text-slate-400 text-[11px]">0%</span>
                </div>
                <div class="w-full bg-slate-900 rounded-full h-2.5 overflow-hidden border border-slate-800">
                    <div id="upgrade-progress-bar" class="bg-gradient-to-r from-amber-500 to-yellow-400 h-2.5 rounded-full transition-all duration-300" style="width: 5%"></div>
                </div>
                <p class="text-[11px] text-slate-500 text-center">Please do not disconnect your USB drive during upgrade.</p>
            </div>

            <!-- Error Notification Alert (Hidden initially) -->
            <div id="upgrade-error-box" class="hidden p-3 rounded-xl bg-red-950/40 border border-red-500/50 text-red-300 text-xs flex items-center justify-between">
                <span id="upgrade-error-message">Upgrade failed.</span>
                <button onclick="triggerOneClickUpgrade()" class="px-2.5 py-1 rounded-lg bg-red-500/20 hover:bg-red-500/30 text-red-200 text-[11px] font-semibold transition">Retry</button>
            </div>

            <!-- Action Buttons -->
            <div id="modal-actions" class="flex items-center justify-end space-x-3 pt-2">
                <button onclick="closeUpdateModal()" class="px-4 py-2 rounded-xl text-slate-400 hover:text-white text-xs font-semibold transition">
                    Remind Later
                </button>
                <button id="upgrade-now-btn" onclick="triggerOneClickUpgrade()"
                    class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-yellow-400 hover:from-amber-400 hover:to-yellow-300 text-slate-950 font-bold text-xs shadow-lg shadow-amber-500/25 transition transform hover:scale-[1.02] flex items-center space-x-2 cursor-pointer">
                    <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    <span>Upgrade in 1-Click</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notification for Offline / Up-to-date checks -->
    <div id="app-toast" class="fixed bottom-6 right-6 z-50 hidden max-w-sm p-4 rounded-2xl shadow-2xl border transition-all duration-300 text-xs">
        <div class="flex items-center space-x-3">
            <span id="toast-icon" class="text-base">ℹ️</span>
            <div>
                <p id="toast-title" class="font-bold text-white"></p>
                <p id="toast-message" class="text-slate-300 mt-0.5 text-[11px]"></p>
            </div>
        </div>
    </div>

    <!-- Client Update Controller Script -->
    <script>
        let cachedUpdateData = null;
        let isChecking = false;
        let isUpgrading = false;

        // Auto-check for updates on load (silently)
        document.addEventListener('DOMContentLoaded', () => {
            checkAppUpdates(true);
        });

        // Trigger manual check or open update modal
        function handleUpdateBtnClick() {
            if (isUpgrading) return;

            if (cachedUpdateData && cachedUpdateData.update_available) {
                openUpdateModal();
            } else {
                checkAppUpdates(false);
            }
        }

        // Check central server for available updates
        async function checkAppUpdates(silent = false) {
            if (isChecking || isUpgrading) return;
            isChecking = true;

            const dot = document.getElementById('update-indicator-dot');
            const label = document.getElementById('update-btn-label');
            const pill = document.getElementById('update-pill');
            const btn = document.getElementById('update-btn');

            if (!silent) {
                dot.className = 'w-2 h-2 rounded-full bg-amber-400 animate-ping';
                showToast('Checking updates...', 'Connecting to central update server...', 'info');
            }

            try {
                const response = await fetch('/api/app/update/check', {
                    headers: { 'Accept': 'application/json' }
                });

                if (!response.ok) throw new Error('Server returned ' + response.status);

                const data = await response.json();
                cachedUpdateData = data;

                if (data.update_available) {
                    // Update is available!
                    dot.className = 'w-2 h-2 rounded-full bg-emerald-400 animate-pulse';
                    label.textContent = `v${data.current_version} ➜ v${data.latest_version}`;
                    pill.classList.remove('hidden');
                    btn.classList.add('border-amber-500/60', 'bg-amber-950/30');

                    // Show top banner
                    const banner = document.getElementById('update-available-banner');
                    document.getElementById('banner-new-version').textContent = `v${data.latest_version}`;
                    document.getElementById('banner-title').textContent = data.title || 'New features and improvements are ready to install.';
                    banner.classList.remove('hidden');

                    if (!silent) {
                        openUpdateModal();
                    }
                } else if (!data.online) {
                    // Customer has no internet connection (offline portable mode)
                    dot.className = 'w-2 h-2 rounded-full bg-slate-500';
                    label.textContent = `v${data.current_version} (Offline)`;
                    pill.classList.add('hidden');

                    if (!silent) {
                        showToast('Offline Mode', 'No internet connection detected. You can safely use ItroveJewel offline.', 'warning');
                    }
                } else {
                    // Up to date
                    dot.className = 'w-2 h-2 rounded-full bg-emerald-500';
                    label.textContent = `v${data.current_version} (Latest)`;
                    pill.classList.add('hidden');

                    if (!silent) {
                        showToast('Up to Date', `You are running the latest version of ItroveJewel (v${data.current_version}).`, 'success');
                    }
                }
            } catch (err) {
                dot.className = 'w-2 h-2 rounded-full bg-slate-500';
                if (!silent) {
                    showToast('Connection Offline', 'Unable to reach the update server. You can continue using your software offline.', 'warning');
                }
            } finally {
                isChecking = false;
            }
        }

        // Open 1-Click Upgrade Modal
        function openUpdateModal() {
            if (!cachedUpdateData || !cachedUpdateData.update_available) return;

            document.getElementById('modal-current-version').textContent = `v${cachedUpdateData.current_version}`;
            document.getElementById('modal-new-version').textContent = `v${cachedUpdateData.latest_version}`;
            document.getElementById('modal-release-title').textContent = cachedUpdateData.title || `ItroveJewel v${cachedUpdateData.latest_version}`;

            if (cachedUpdateData.file_size) {
                const mb = (cachedUpdateData.file_size / (1024 * 1024)).toFixed(2);
                document.getElementById('modal-release-size').textContent = `${mb} MB`;
            }

            // Populate release notes
            const notesContainer = document.getElementById('modal-release-notes');
            notesContainer.innerHTML = '';
            if (Array.isArray(cachedUpdateData.release_notes) && cachedUpdateData.release_notes.length > 0) {
                cachedUpdateData.release_notes.forEach(note => {
                    const row = document.createElement('div');
                    row.className = 'flex items-start space-x-2';
                    row.innerHTML = `<span class="text-amber-400 font-bold">&bull;</span><span>${escapeHtml(note)}</span>`;
                    notesContainer.appendChild(row);
                });
            } else {
                notesContainer.innerHTML = '<p class="text-slate-400">Includes performance improvements and safety patches for portable USB operation.</p>';
            }

            document.getElementById('update-modal').classList.remove('hidden');
        }

        function closeUpdateModal() {
            if (isUpgrading) return; // Prevent closing during upgrade
            document.getElementById('update-modal').classList.add('hidden');
        }

        // Trigger 1-Click Upgrade Process
        async function triggerOneClickUpgrade() {
            if (isUpgrading || !cachedUpdateData) return;
            isUpgrading = true;

            const progressContainer = document.getElementById('upgrade-progress-container');
            const progressBar = document.getElementById('upgrade-progress-bar');
            const statusText = document.getElementById('upgrade-status-text');
            const percentageText = document.getElementById('upgrade-percentage');
            const actions = document.getElementById('modal-actions');
            const errorBox = document.getElementById('upgrade-error-box');
            const closeBtn = document.getElementById('modal-close-btn');

            errorBox.classList.add('hidden');
            progressContainer.classList.remove('hidden');
            actions.classList.add('hidden');
            closeBtn.classList.add('hidden');

            const steps = [
                { pct: 15, msg: 'Backing up local database (data/database/)...' },
                { pct: 40, msg: 'Downloading verified update package...' },
                { pct: 65, msg: 'Verifying SHA-256 cryptographic signature...' },
                { pct: 85, msg: 'Applying files to USB pendrive...' },
                { pct: 95, msg: 'Migrating database schemas...' }
            ];

            let stepIndex = 0;
            const progressTimer = setInterval(() => {
                if (stepIndex < steps.length) {
                    progressBar.style.width = steps[stepIndex].pct + '%';
                    percentageText.textContent = steps[stepIndex].pct + '%';
                    statusText.querySelector('span:last-child').textContent = steps[stepIndex].msg;
                    stepIndex++;
                }
            }, 600);

            try {
                const response = await fetch('/api/app/update/apply', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        download_url: cachedUpdateData.download_url,
                        sha256: cachedUpdateData.sha256
                    })
                });

                clearInterval(progressTimer);

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Upgrade failed.');
                }

                // Upgrade Completed Successfully!
                progressBar.style.width = '100%';
                progressBar.className = 'bg-emerald-500 h-2.5 rounded-full transition-all duration-300';
                percentageText.textContent = '100%';
                statusText.innerHTML = `
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-emerald-300 font-bold">Upgraded to v${result.new_version || cachedUpdateData.latest_version}! Reloading ItroveJewel...</span>
                `;

                // Auto reload after 2.2 seconds
                setTimeout(() => {
                    window.location.reload();
                }, 2200);

            } catch (err) {
                clearInterval(progressTimer);
                isUpgrading = false;
                progressContainer.classList.add('hidden');
                actions.classList.remove('hidden');
                closeBtn.classList.remove('hidden');
                errorBox.classList.remove('hidden');
                document.getElementById('upgrade-error-message').textContent = err.message || 'Error occurred while applying upgrade.';
            }
        }

        // Lightweight Toast Helper
        function showToast(title, message, type = 'info') {
            const toast = document.getElementById('app-toast');
            const tTitle = document.getElementById('toast-title');
            const tMsg = document.getElementById('toast-message');
            const tIcon = document.getElementById('toast-icon');

            tTitle.textContent = title;
            tMsg.textContent = message;

            if (type === 'success') {
                toast.className = 'fixed bottom-6 right-6 z-50 max-w-sm p-4 rounded-2xl shadow-2xl border bg-emerald-950/95 border-emerald-500/50 text-white';
                tIcon.textContent = '✅';
            } else if (type === 'warning') {
                toast.className = 'fixed bottom-6 right-6 z-50 max-w-sm p-4 rounded-2xl shadow-2xl border bg-amber-950/95 border-amber-500/50 text-white';
                tIcon.textContent = '⚠️';
            } else {
                toast.className = 'fixed bottom-6 right-6 z-50 max-w-sm p-4 rounded-2xl shadow-2xl border bg-slate-900/95 border-slate-700/80 text-white';
                tIcon.textContent = 'ℹ️';
            }

            toast.classList.remove('hidden');
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 4000);
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>
