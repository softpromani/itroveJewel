<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ItroveJewel - USB Hardware Lock Installation</title>
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
            border: 1px solid rgba(234, 179, 8, 0.3);
            box-shadow: 0 0 25px rgba(234, 179, 8, 0.08);
        }
        .glass-panel {
            background: rgba(13, 17, 26, 0.92);
            backdrop-filter: blur(20px);
        }
        .pulse-glow {
            box-shadow: 0 0 30px rgba(234, 179, 8, 0.25);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between p-6">

    <!-- Top Navigation -->
    <header class="flex items-center justify-between border-b border-slate-800/80 pb-4 max-w-4xl w-full mx-auto">
        <div class="flex items-center space-x-3">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-amber-600 via-amber-400 to-yellow-200 flex items-center justify-center shadow-lg shadow-amber-500/20">
                <svg class="w-6 h-6 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold tracking-wider gold-gradient font-serif">ItroveJewel</h1>
                <p class="text-[11px] text-slate-400 tracking-wider">HARDWARE INTEGRITY & COPY PROTECTION</p>
            </div>
        </div>

        <div class="flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-amber-950/40 border border-amber-500/40 text-xs">
            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
            <span class="text-amber-300 font-semibold tracking-wide">SETUP MODE: UNLOCKED DRIVE</span>
        </div>
    </header>

    <!-- Main Wizard Card -->
    <main class="my-auto max-w-xl w-full mx-auto">
        <div class="glass-panel gold-border rounded-3xl p-8 shadow-2xl space-y-6 relative overflow-hidden">
            
            <!-- Decorative Background Glow -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Title & Status Icon -->
            <div class="text-center space-y-3">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-tr from-amber-500 to-yellow-300 flex items-center justify-center shadow-xl shadow-amber-500/30">
                    <svg class="w-8 h-8 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <div>
                    <span class="text-[11px] font-bold tracking-widest text-amber-400 uppercase bg-amber-950/60 border border-amber-500/30 px-3 py-1 rounded-full">
                        First-Time Installation Required
                    </span>
                    <h2 class="text-2xl font-bold text-white tracking-wide mt-3">USB Hardware Serial Lock</h2>
                    <p class="text-xs text-slate-400 mt-1 max-w-md mx-auto">
                        Bind and permanently lock this software to your physical USB pendrive to prevent unauthorized distribution and copying.
                    </p>
                </div>
            </div>

            <!-- Detected Hardware Information Panel -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 space-y-3.5">
                <div class="flex items-center justify-between text-xs pb-2 border-b border-slate-800/80">
                    <span class="text-slate-400 font-medium flex items-center space-x-1.5">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                        </svg>
                        <span>Detected Storage Hardware</span>
                    </span>
                    <span class="text-emerald-400 font-mono text-[11px] bg-emerald-950/70 border border-emerald-500/40 px-2.5 py-0.5 rounded-full">
                        Scan Verified &bull; {{ $hardware['drive_letter'] }}:
                    </span>
                </div>

                <!-- Hardware Serial Number Card -->
                <div class="p-3.5 rounded-xl bg-amber-950/20 border border-amber-500/40 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] uppercase font-bold text-amber-300 tracking-wider">Physical Hardware Serial Number</p>
                        <p class="font-mono-code text-sm sm:text-base font-bold text-white tracking-wider mt-0.5 select-all">
                            {{ $hardware['serial_number'] ?: 'Scanning...' }}
                        </p>
                    </div>
                    <div class="w-8 h-8 rounded-lg bg-amber-500/20 flex items-center justify-center text-amber-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Hardware Specs Grid -->
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800/80">
                        <span class="text-slate-500 text-[10px] uppercase font-semibold block">Device Model</span>
                        <span class="text-slate-200 font-medium truncate block mt-0.5" title="{{ $hardware['model'] }}">
                            {{ $hardware['model'] }}
                        </span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800/80">
                        <span class="text-slate-500 text-[10px] uppercase font-semibold block">Bus Type / Interface</span>
                        <span class="text-slate-200 font-medium block mt-0.5">
                            {{ $hardware['bus_type'] }} ({{ $hardware['size_formatted'] }})
                        </span>
                    </div>
                </div>
            </div>

            <!-- Lock Details & Anti-Cloning Notice -->
            <div class="p-4 rounded-xl bg-slate-900/40 border border-slate-800 text-xs space-y-2 text-slate-300">
                <div class="flex items-start space-x-2.5">
                    <svg class="w-4 h-4 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-[11px] leading-relaxed text-slate-400">
                        Once locked, a cryptographic <strong class="text-amber-300">HMAC-SHA256 hardware license token</strong> will be generated. The application will <strong class="text-white">only run on this physical pendrive</strong>. If the code or folder is copied to another USB or PC, it will refuse to open.
                    </p>
                </div>
            </div>

            <!-- Install Form -->
            <form id="installForm" class="space-y-4">
                <div>
                    <label for="adminName" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Administrator / Store Owner Name
                    </label>
                    <input type="text" id="adminName" name="admin_name" value="Store Administrator" 
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-sm text-white focus:outline-none focus:border-amber-400 transition placeholder-slate-500">
                </div>

                <div class="flex items-start space-x-3 pt-1">
                    <input type="checkbox" id="confirmLock" name="confirm_lock" required checked
                        class="mt-1 h-4 w-4 rounded border-slate-700 bg-slate-900 text-amber-500 focus:ring-amber-400 focus:ring-offset-slate-900">
                    <label for="confirmLock" class="text-xs text-slate-300 leading-snug cursor-pointer">
                        I confirm that this is the authorized USB Pendrive. Permanently lock ItroveJewel to Hardware Serial <code class="text-amber-300 font-mono-code">{{ $hardware['serial_number'] }}</code>.
                    </label>
                </div>

                <!-- Alert banner (hidden by default) -->
                <div id="alertBox" class="hidden p-3 rounded-xl text-xs font-medium border"></div>

                <!-- Submit Button -->
                <button type="submit" id="submitBtn" 
                    class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-amber-500 via-amber-400 to-yellow-300 hover:from-amber-400 hover:to-yellow-200 text-slate-950 font-bold text-sm tracking-wide shadow-lg shadow-amber-500/25 transition-all transform active:scale-[0.98] flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <span id="btnText">INSTALL & LOCK TO THIS USB PENDRIVE</span>
                </button>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800/80 pt-3 text-center text-xs text-slate-500 flex items-center justify-between max-w-4xl w-full mx-auto">
        <span>ItroveJewel &copy; {{ date('Y') }} &bull; Anti-Piracy Dongle Architecture</span>
        <span class="font-mono text-slate-400 text-[11px]">Drive: {{ $hardware['drive_letter'] }}: (Physical Serial Lock)</span>
    </footer>

    <script>
        const form = document.getElementById('installForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const alertBox = document.getElementById('alertBox');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
            btnText.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-slate-950 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Cryptographically Sealing to USB Hardware...
            `;

            alertBox.classList.add('hidden');

            try {
                const response = await fetch("{{ route('usb.install') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        admin_name: document.getElementById('adminName').value,
                        confirm_lock: document.getElementById('confirmLock').checked
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alertBox.className = 'p-3 rounded-xl text-xs font-semibold bg-emerald-950/80 border-emerald-500/50 text-emerald-300 block text-center';
                    alertBox.innerText = 'Lock Activated! Redirecting to secure dashboard...';
                    
                    setTimeout(() => {
                        window.location.href = "{{ route('home') }}";
                    }, 1200);
                } else {
                    throw new Error(data.message || 'Installation failed.');
                }
            } catch (err) {
                alertBox.className = 'p-3 rounded-xl text-xs font-semibold bg-red-950/80 border-red-500/50 text-red-300 block';
                alertBox.innerText = err.message;
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                btnText.innerText = 'INSTALL & LOCK TO THIS USB PENDRIVE';
            }
        });
    </script>
</body>
</html>
