@extends('admin.layout')

@section('title', 'Client Software Updates & Releases')

@section('content')
<div class="space-y-6">

    <!-- Top Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-800/80">
        <div>
            <div class="flex items-center space-x-2 text-xs text-amber-400 font-semibold mb-1">
                <span>Central Server</span>
                <span>&bull;</span>
                <span class="text-slate-400">Software Releases & Upgrades</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-wide font-serif">Client Software Updates & Releases</h1>
            <p class="text-xs text-slate-400 mt-0.5">
                Publish code updates to all client USB pendrives. When clients connect to the internet, they receive the 1-click upgrade prompt.
            </p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="#publish-release-form-card" class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-yellow-400 hover:from-amber-400 hover:to-yellow-300 text-slate-950 font-bold text-xs shadow-lg shadow-amber-500/20 transition flex items-center space-x-2">
                <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                <span>Publish New Release</span>
            </a>
        </div>
    </div>

    <!-- Alert Notifications -->
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

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-red-950/80 border border-red-500/50 text-red-200 text-xs shadow-lg space-y-1">
            <p class="font-bold">Please correct the following errors:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-sm">
            <div class="flex items-center justify-between text-xs text-slate-400">
                <span>Latest Live Release</span>
                <span class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-emerald-400 font-mono-code mt-2">
                @if($metrics['latest_active_version'] !== 'None')
                    v{{ $metrics['latest_active_version'] }}
                @else
                    None
                @endif
            </p>
            <p class="text-[11px] text-slate-500 mt-1">Actively offered to clients</p>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-sm">
            <div class="flex items-center justify-between text-xs text-slate-400">
                <span>Server Code Version</span>
                <span class="p-1.5 rounded-lg bg-amber-500/10 text-amber-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-amber-300 font-mono-code mt-2">v{{ $metrics['current_server_version'] }}</p>
            <p class="text-[11px] text-slate-500 mt-1">Recorded in version.json</p>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-sm">
            <div class="flex items-center justify-between text-xs text-slate-400">
                <span>Client Upgrades Served</span>
                <span class="p-1.5 rounded-lg bg-cyan-500/10 text-cyan-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-white mt-2">{{ $metrics['total_downloads'] }}</p>
            <p class="text-[11px] text-slate-500 mt-1">Total downloads by client apps</p>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-sm">
            <div class="flex items-center justify-between text-xs text-slate-400">
                <span>Total Releases</span>
                <span class="p-1.5 rounded-lg bg-purple-500/10 text-purple-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-white mt-2">{{ $metrics['total_releases'] }}</p>
            <p class="text-[11px] text-slate-500 mt-1">{{ $metrics['active_releases'] }} Active, {{ $metrics['total_releases'] - $metrics['active_releases'] }} Paused</p>
        </div>
    </div>

    <!-- Release Publishing Form Card -->
    <div id="publish-release-form-card" class="glass-panel gold-border rounded-3xl p-6 shadow-xl space-y-5">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800/80">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-yellow-300 flex items-center justify-center text-slate-950 font-bold shrink-0 shadow-md">
                    <svg class="w-5 h-5 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-white">Publish New Client Software Update</h2>
                    <p class="text-xs text-slate-400">Deploy updates from server codebase to customer USB drives with zero data loss.</p>
                </div>
            </div>
            <div class="hidden sm:flex items-center space-x-2 text-xs text-amber-300 font-mono-code bg-amber-500/10 px-3 py-1 rounded-lg border border-amber-500/20">
                <span>Suggested Version:</span>
                <span class="font-bold underline cursor-pointer" onclick="document.getElementById('version-input').value='{{ $metrics['suggested_version'] }}'">v{{ $metrics['suggested_version'] }}</span>
            </div>
        </div>

        <form action="{{ route('admin.releases.store') }}" method="POST" class="space-y-4" onsubmit="handleReleaseSubmit(this)">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Version Input -->
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-300 flex items-center justify-between">
                        <span>New Release Version <span class="text-red-400">*</span></span>
                        <span class="text-[10px] text-slate-400 font-normal">e.g. 1.1.0, 1.2.0</span>
                    </label>
                    <input type="text" name="version" id="version-input" required
                        value="{{ old('version', $metrics['suggested_version']) }}"
                        placeholder="1.1.0"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950/80 border border-slate-800 text-white text-xs font-mono-code focus:border-amber-400 focus:outline-none transition">
                </div>

                <!-- Release Title -->
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-300 flex items-center justify-between">
                        <span>Release Title <span class="text-red-400">*</span></span>
                        <span class="text-[10px] text-slate-400 font-normal">Shown in client upgrade modal</span>
                    </label>
                    <input type="text" name="title" required
                        value="{{ old('title', 'ItroveJewel v' . $metrics['suggested_version'] . ' Feature & Performance Update') }}"
                        placeholder="e.g. ItroveJewel v1.1.0 Luxury UI & Performance Release"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950/80 border border-slate-800 text-white text-xs focus:border-amber-400 focus:outline-none transition">
                </div>
            </div>

            <!-- Release Notes / Changelog -->
            <div class="space-y-1">
                <label class="text-xs font-semibold text-slate-300 flex items-center justify-between">
                    <span>Release Notes & Highlights (One per line)</span>
                    <span class="text-[10px] text-slate-400 font-normal">Bullet points shown to clients</span>
                </label>
                <textarea name="release_notes" rows="4"
                    placeholder="Added GST Invoice PDF export&#10;Optimized Barcode scanning speed&#10;Added light and dark luxury themes&#10;Performance improvements and bug fixes"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950/80 border border-slate-800 text-white text-xs focus:border-amber-400 focus:outline-none transition font-sans">{{ old('release_notes') }}</textarea>
                <p class="text-[10px] text-slate-400">Enter each feature or improvement on a separate line. Customers will see these in their 1-Click Upgrade modal.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                <!-- Minimum Supported Version -->
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-300 flex items-center justify-between">
                        <span>Minimum Required Version</span>
                        <span class="text-[10px] text-slate-400 font-normal">Default: 1.0.0</span>
                    </label>
                    <input type="text" name="min_supported_version"
                        value="{{ old('min_supported_version', '1.0.0') }}"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950/80 border border-slate-800 text-white text-xs font-mono-code focus:border-amber-400 focus:outline-none transition">
                </div>

                <!-- Active Immediately Checkbox -->
                <div class="pt-5 flex items-center space-x-3">
                    <label class="relative flex items-center cursor-pointer select-none space-x-3">
                        <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded text-amber-500 focus:ring-amber-400 bg-slate-950 border-slate-700">
                        <span class="text-xs font-semibold text-slate-200">
                            Make this release live immediately for all clients
                        </span>
                    </label>
                </div>
            </div>

            <!-- Guarantee Callout -->
            <div class="p-3 rounded-xl bg-emerald-950/30 border border-emerald-500/30 flex items-start space-x-2.5 text-xs text-emerald-200">
                <svg class="w-4 h-4 text-emerald-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                <div class="text-[11px]">
                    <span class="font-bold text-emerald-300">Zero Data Loss & Security Guarantee:</span>
                    The server packaging engine automatically strips all admin controllers, admin databases, and testing files. When clients click "Upgrade in 1-Click", their personal sales database (<code class="text-amber-300">data/database/database.sqlite</code>) and USB hardware lock credentials are 100% protected and never overwritten.
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end space-x-3 pt-2">
                <button type="submit" id="submit-release-btn"
                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-yellow-400 hover:from-amber-400 hover:to-yellow-300 text-slate-950 font-bold text-xs shadow-lg shadow-amber-500/25 transition transform hover:scale-[1.02] flex items-center space-x-2 cursor-pointer">
                    <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <span>Publish Release to All Clients</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Table Card: Published Releases History -->
    <div class="glass-panel gold-border rounded-3xl p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800/80">
            <div>
                <h2 class="text-base font-bold text-white">Published Client Releases on Server</h2>
                <p class="text-xs text-slate-400">All registered update packages. Clients automatically query this catalog to trigger 1-Click upgrades.</p>
            </div>
            <span class="text-xs text-slate-400 font-mono-code">{{ $releases->total() }} Total Releases</span>
        </div>

        @if($releases->isEmpty())
            <div class="text-center py-12 space-y-3">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-white">No Client Releases Published Yet</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">
                    Use the form above to package and publish your first client update after deploying your code.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-800 text-[11px] uppercase tracking-wider">
                            <th class="py-3 px-3">Version</th>
                            <th class="py-3 px-3">Title & Highlights</th>
                            <th class="py-3 px-3">Status</th>
                            <th class="py-3 px-3">Package Size</th>
                            <th class="py-3 px-3">Upgrades Served</th>
                            <th class="py-3 px-3">Published Date</th>
                            <th class="py-3 px-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @foreach($releases as $rel)
                            <tr class="hover:bg-slate-900/40 transition">
                                <td class="py-3 px-3">
                                    <div class="flex items-center space-x-2">
                                        <span class="w-2 h-2 rounded-full {{ $rel->is_active ? 'bg-emerald-400 shadow-sm shadow-emerald-400/50' : 'bg-slate-600' }}"></span>
                                        <span class="font-mono-code font-bold text-amber-300 text-sm">v{{ $rel->version }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-3 max-w-xs">
                                    <p class="font-semibold text-white truncate">{{ $rel->title }}</p>
                                    @if(!empty($rel->release_notes) && is_array($rel->release_notes))
                                        <ul class="text-[11px] text-slate-400 mt-0.5 space-y-0.5">
                                            @foreach(array_slice($rel->release_notes, 0, 2) as $note)
                                                <li class="truncate">• {{ $note }}</li>
                                            @endforeach
                                            @if(count($rel->release_notes) > 2)
                                                <li class="text-[10px] text-amber-400/80 font-medium">+{{ count($rel->release_notes) - 2 }} more highlights</li>
                                            @endif
                                        </ul>
                                    @endif
                                    <p class="font-mono-code text-[10px] text-slate-500 truncate mt-1" title="{{ $rel->file_hash }}">
                                        SHA: {{ substr((string) $rel->file_hash, 0, 16) }}...
                                    </p>
                                </td>
                                <td class="py-3 px-3">
                                    @if($rel->is_active)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 inline-flex items-center space-x-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                            <span>LIVE</span>
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700">
                                            PAUSED
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 font-mono-code text-slate-300">
                                    {{ $rel->formatted_file_size }}
                                </td>
                                <td class="py-3 px-3">
                                    <span class="font-mono-code text-white font-bold">{{ $rel->download_count }}</span>
                                    <span class="text-[10px] text-slate-500">clients</span>
                                </td>
                                <td class="py-3 px-3 text-slate-400">
                                    {{ $rel->created_at->format('M d, Y h:i A') }}
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <!-- Single-Click Download Button -->
                                        <a href="{{ route('admin.releases.download', $rel) }}"
                                            class="px-3 py-1.5 rounded-xl bg-gradient-to-r from-amber-500 to-yellow-400 hover:from-amber-400 hover:to-yellow-300 text-slate-950 font-bold text-xs shadow-md shadow-amber-500/15 transition flex items-center space-x-1 cursor-pointer"
                                            title="Download verified update package ZIP">
                                            <svg class="w-3.5 h-3.5 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                            </svg>
                                            <span>Download ZIP</span>
                                        </a>

                                        <!-- Toggle Status Button -->
                                        <form action="{{ route('admin.releases.toggle', $rel) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 rounded-xl text-xs font-semibold border transition {{ $rel->is_active ? 'bg-slate-900 border-slate-700 text-slate-300 hover:text-amber-300 hover:border-amber-400' : 'bg-emerald-950/60 border-emerald-500/50 text-emerald-300 hover:bg-emerald-900' }}"
                                                title="{{ $rel->is_active ? 'Pause this release so clients stop receiving it' : 'Make this release live' }}">
                                                {{ $rel->is_active ? 'Pause' : 'Activate' }}
                                            </button>
                                        </form>

                                        <!-- Delete Button -->
                                        <form action="{{ route('admin.releases.destroy', $rel) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete release v{{ $rel->version }}? Connected clients will no longer be able to download this update package.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-red-400 hover:bg-red-500/10 transition" title="Delete Release">
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
                {{ $releases->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    function handleReleaseSubmit(form) {
        const btn = document.getElementById('submit-release-btn');
        btn.disabled = true;
        btn.innerHTML = `
            <svg class="w-4 h-4 animate-spin text-slate-950" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span>Packaging & Publishing Release... Please wait</span>
        `;
    }
</script>
@endsection
