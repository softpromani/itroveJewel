@extends('admin.layout')

@section('title', 'Fresh USB Installers & Packages')

@section('content')
<div class="space-y-6">

    <!-- Top Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-800/80">
        <div>
            <div class="flex items-center space-x-2 text-xs text-amber-400 font-semibold mb-1">
                <span>Central License Server</span>
                <span>&bull;</span>
                <span class="text-slate-400">Software Distribution</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-wide font-serif">Fresh USB Installers & Software Packages</h1>
            <p class="text-xs text-slate-400 mt-0.5">
                Generate clean, uninstalled portable software packages or download pre-built ZIP packages for customer pendrives.
            </p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="#generate-package-card" class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-yellow-400 hover:from-amber-400 hover:to-yellow-300 text-slate-950 font-bold text-xs shadow-lg shadow-amber-500/20 transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Create New Fresh Package</span>
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-500/50 text-emerald-200 text-xs flex items-center space-x-3 shadow-lg">
            <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-red-950/80 border border-red-500/50 text-red-200 text-xs flex items-center space-x-3 shadow-lg">
            <svg class="w-5 h-5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-sm">
            <div class="flex items-center justify-between text-xs text-slate-400">
                <span>Available Packages</span>
                <span class="p-1.5 rounded-lg bg-amber-500/10 text-amber-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-white mt-2">{{ $metrics['total_packages'] }}</p>
            <p class="text-[11px] text-slate-500 mt-1">Ready for single-click download</p>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-sm">
            <div class="flex items-center justify-between text-xs text-slate-400">
                <span>Total Server Downloads</span>
                <span class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-white mt-2">{{ $metrics['total_downloads'] }}</p>
            <p class="text-[11px] text-slate-500 mt-1">Direct ZIP downloads by admin</p>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-sm">
            <div class="flex items-center justify-between text-xs text-slate-400">
                <span>Storage Space</span>
                <span class="p-1.5 rounded-lg bg-cyan-500/10 text-cyan-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-white mt-2">
                {{ round($metrics['storage_used'] / 1024 / 1024, 1) }} MB
            </p>
            <p class="text-[11px] text-slate-500 mt-1">In storage/app/fresh_installers</p>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-sm">
            <div class="flex items-center justify-between text-xs text-slate-400">
                <span>Latest Package Version</span>
                <span class="p-1.5 rounded-lg bg-purple-500/10 text-purple-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-amber-300 font-mono-code mt-2">v{{ $metrics['latest_version'] }}</p>
            <p class="text-[11px] text-slate-500 mt-1">Current distribution master</p>
        </div>
    </div>

    <!-- Generator Form Card -->
    <div id="generate-package-card" class="glass-panel gold-border rounded-3xl p-6 shadow-xl space-y-5">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800/80">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-yellow-300 flex items-center justify-center text-slate-950 font-bold shrink-0 shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-white">Create New Fresh Installation Software</h2>
                    <p class="text-xs text-slate-400">Package clean, uninstalled portable software bundle for customer pendrives.</p>
                </div>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-400 bg-amber-950/80 border border-amber-500/30 px-2.5 py-1 rounded-full">
                Zero Admin Code
            </span>
        </div>

        <form action="{{ route('admin.packages.create') }}" method="POST" class="space-y-4" onsubmit="handlePackagingSubmit(this)">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Version Name Input -->
                <div>
                    <label for="version" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Version Name <span class="text-amber-400">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500 font-mono-code text-xs">
                            v
                        </span>
                        <input type="text" name="version" id="version" required
                            value="{{ old('version') }}"
                            placeholder="1.0.0"
                            class="w-full pl-7 pr-3 py-2.5 rounded-xl bg-slate-950/80 border border-slate-800 text-white font-mono-code text-xs focus:border-amber-400 focus:outline-none transition @error('version') border-red-500 @enderror">
                    </div>
                    @error('version')
                        <p class="text-red-400 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Package Title Input -->
                <div class="md:col-span-2">
                    <label for="title" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Package Title / Label <span class="text-slate-500">(Optional)</span>
                    </label>
                    <input type="text" name="title" id="title"
                        value="{{ old('title') }}"
                        placeholder="e.g. ItroveJewel v1.0.0 Luxury Fresh USB Release"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950/80 border border-slate-800 text-white text-xs focus:border-amber-400 focus:outline-none transition">
                </div>
            </div>

            <!-- Package Type Selection -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-2">
                    Package Type <span class="text-amber-400">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="p-3.5 rounded-2xl bg-slate-950/70 border border-amber-500/40 hover:border-amber-400 cursor-pointer flex items-start space-x-3 transition">
                        <input type="radio" name="package_type" value="full_usb" checked class="mt-1 text-amber-500 focus:ring-amber-400">
                        <div>
                            <span class="text-xs font-bold text-white flex items-center space-x-1.5">
                                <span>Complete Portable USB Drive Bundle (.zip)</span>
                                <span class="text-[9px] font-bold bg-amber-400 text-slate-950 px-1.5 py-0.2 rounded uppercase">Recommended</span>
                            </span>
                            <p class="text-[11px] text-slate-400 mt-1">
                                Complete ready-to-run USB bundle with <code class="text-amber-300 font-mono">itrovejewel.exe</code>, clean database, and fresh configuration. Extract directly to pendrive.
                            </p>
                        </div>
                    </label>

                    <label class="p-3.5 rounded-2xl bg-slate-950/70 border border-slate-800 hover:border-slate-700 cursor-pointer flex items-start space-x-3 transition">
                        <input type="radio" name="package_type" value="app_core" class="mt-1 text-amber-500 focus:ring-amber-400">
                        <div>
                            <span class="text-xs font-bold text-white">Application Core Package (.zip)</span>
                            <p class="text-[11px] text-slate-400 mt-1">
                                Lightweight clean application files only (<code class="text-amber-300 font-mono">app/</code>, <code class="text-amber-300 font-mono">resources/</code>, etc.) without the 200MB executable.
                            </p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Notes Input -->
            <div>
                <label for="notes" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Release Notes / Internal Instructions <span class="text-slate-500">(Optional)</span>
                </label>
                <textarea name="notes" id="notes" rows="2"
                    placeholder="e.g. Master clean image for SanDisk 32GB pendrives. Core tables initialized."
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950/80 border border-slate-800 text-white text-xs focus:border-amber-400 focus:outline-none transition">{{ old('notes') }}</textarea>
            </div>

            <!-- Guarantee Callout -->
            <div class="p-3 rounded-xl bg-emerald-950/30 border border-emerald-500/30 flex items-start space-x-2.5 text-xs text-emerald-200">
                <svg class="w-4 h-4 text-emerald-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                <div class="text-[11px]">
                    <span class="font-bold text-emerald-300">Clean State Guarantee:</span>
                    The generated package is 100% free of admin code, has zero hardware lock records, and has a fresh SQLite database initialized. It will prompt the customer for first-time pendrive binding.
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end space-x-3 pt-2">
                <button type="submit" id="submit-package-btn"
                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-yellow-400 hover:from-amber-400 hover:to-yellow-300 text-slate-950 font-bold text-xs shadow-lg shadow-amber-500/25 transition transform hover:scale-[1.02] flex items-center space-x-2 cursor-pointer">
                    <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    <span>Generate Fresh Installation ZIP</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Table Card: Available Fresh Installation Packages -->
    <div class="glass-panel gold-border rounded-3xl p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800/80">
            <div>
                <h2 class="text-base font-bold text-white">Ready Fresh Installation Packages on Server</h2>
                <p class="text-xs text-slate-400">Click "Download ZIP" on any package to download it directly to your computer.</p>
            </div>
            <span class="text-xs text-slate-400 font-mono-code">{{ $packages->total() }} Total Packages</span>
        </div>

        @if($packages->isEmpty())
            <div class="text-center py-12 space-y-3">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-white">No Fresh Installation Packages Created Yet</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">
                    Use the form above to generate your first clean portable ZIP package for customer pendrives.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-800 text-[11px] uppercase tracking-wider">
                            <th class="py-3 px-3">Version</th>
                            <th class="py-3 px-3">Title & Notes</th>
                            <th class="py-3 px-3">Package Type</th>
                            <th class="py-3 px-3">File Size</th>
                            <th class="py-3 px-3">Created Date</th>
                            <th class="py-3 px-3">Downloads</th>
                            <th class="py-3 px-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @foreach($packages as $pkg)
                            <tr class="hover:bg-slate-900/40 transition">
                                <td class="py-3 px-3">
                                    <div class="flex items-center space-x-2">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                        <span class="font-mono-code font-bold text-amber-300 text-sm">v{{ $pkg->version }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-3 max-w-xs">
                                    <p class="font-semibold text-white truncate">{{ $pkg->title ?: "ItroveJewel v{$pkg->version}" }}</p>
                                    @if($pkg->notes)
                                        <p class="text-[11px] text-slate-400 truncate mt-0.5">{{ $pkg->notes }}</p>
                                    @endif
                                    <p class="font-mono-code text-[10px] text-slate-500 truncate mt-0.5">SHA: {{ substr($pkg->sha256, 0, 16) }}...</p>
                                </td>
                                <td class="py-3 px-3">
                                    @if($pkg->package_type === 'full_usb')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                            Full USB Bundle (.zip)
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">
                                            App Core (.zip)
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 font-mono-code text-slate-300">
                                    {{ $pkg->formatted_file_size }}
                                </td>
                                <td class="py-3 px-3 text-slate-400">
                                    {{ $pkg->created_at->format('M d, Y h:i A') }}
                                </td>
                                <td class="py-3 px-3">
                                    <span class="font-mono-code text-slate-300 font-bold">{{ $pkg->download_count }}</span>
                                    <span class="text-[10px] text-slate-500">times</span>
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <!-- Single-Click Download Button -->
                                        <a href="{{ route('admin.packages.download', $pkg) }}"
                                            class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-amber-500 to-yellow-400 hover:from-amber-400 hover:to-yellow-300 text-slate-950 font-bold text-xs shadow-md shadow-amber-500/15 transition flex items-center space-x-1.5 cursor-pointer">
                                            <svg class="w-3.5 h-3.5 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                            </svg>
                                            <span>Download ZIP</span>
                                        </a>

                                        <!-- Delete Button -->
                                        <form action="{{ route('admin.packages.destroy', $pkg) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete package v{{ $pkg->version }}? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-red-400 hover:bg-red-500/10 transition" title="Delete Package">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pt-3">
                {{ $packages->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    function handlePackagingSubmit(form) {
        const btn = document.getElementById('submit-package-btn');
        btn.disabled = true;
        btn.innerHTML = `
            <svg class="w-4 h-4 animate-spin text-slate-950" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span>Packaging Clean Portable ZIP (~15-20s)... Please wait</span>
        `;
    }
</script>
@endsection
