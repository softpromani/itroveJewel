@extends('admin.layout')

@section('title', 'Clients & USB Licenses')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-wide">Client & Pendrive Licenses</h1>
            <p class="text-xs text-slate-400 mt-1">
                Manage registered jewel store clients and their authorized USB hardware serial numbers.
            </p>
        </div>

        <a href="{{ route('admin.clients.create') }}" 
            class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-yellow-400 hover:from-amber-400 hover:to-yellow-300 text-slate-950 font-bold text-xs tracking-wider shadow-lg shadow-amber-500/20 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>REGISTER NEW CLIENT</span>
        </a>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="glass-panel gold-border rounded-2xl p-5 space-y-1">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Clients</span>
            <p class="text-2xl font-extrabold text-white">{{ $metrics['total_clients'] }}</p>
            <span class="text-[10px] text-emerald-400 font-medium">Registered Jewel Stores</span>
        </div>

        <div class="glass-panel gold-border rounded-2xl p-5 space-y-1">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Active Clients</span>
            <p class="text-2xl font-extrabold text-emerald-400">{{ $metrics['active_clients'] }}</p>
            <span class="text-[10px] text-slate-400">Authorized accounts</span>
        </div>

        <div class="glass-panel gold-border rounded-2xl p-5 space-y-1">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Pendrives</span>
            <p class="text-2xl font-extrabold text-white">{{ $metrics['total_pendrives'] }}</p>
            <span class="text-[10px] text-amber-300 font-medium">Hardware Bound</span>
        </div>

        <div class="glass-panel gold-border rounded-2xl p-5 space-y-1">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Active USB Licenses</span>
            <p class="text-2xl font-extrabold text-amber-400">{{ $metrics['active_pendrives'] }}</p>
            <span class="text-[10px] text-emerald-400">Live Server Verified</span>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="glass-panel rounded-2xl p-4 border border-slate-800">
        <form method="GET" action="{{ route('admin.clients.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <input type="text" name="search" value="{{ $search }}" 
                    placeholder="Search by client name, shop name, mobile, email, or pendrive serial..." 
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-800 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-400 transition">
                <svg class="w-4 h-4 text-slate-500 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>

            <div class="flex items-center space-x-2">
                <select name="status" class="px-3.5 py-2.5 rounded-xl bg-slate-900/90 border border-slate-800 text-xs text-slate-300 focus:outline-none focus:border-amber-400">
                    <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Active Only</option>
                    <option value="suspended" {{ $statusFilter === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>

                <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold transition">
                    Filter
                </button>

                @if($search !== '' || $statusFilter !== 'all')
                    <a href="{{ route('admin.clients.index') }}" class="px-3 py-2.5 rounded-xl bg-slate-900 text-slate-400 hover:text-white text-xs transition">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Clients Table -->
    <div class="glass-panel rounded-2xl border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-900/90 border-b border-slate-800 text-[11px] uppercase font-bold text-slate-400 tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Client & Shop Details</th>
                        <th class="py-3.5 px-4">Contact Information</th>
                        <th class="py-3.5 px-4">Assigned USB Pendrives</th>
                        <th class="py-3.5 px-4">Account Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($clients as $client)
                        <tr class="hover:bg-slate-900/40 transition">
                            <!-- Client & Shop -->
                            <td class="py-4 px-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-600 to-yellow-400 flex items-center justify-center font-bold text-slate-950 font-serif shadow-sm">
                                        {{ strtoupper(substr($client->shop_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.clients.show', $client) }}" class="font-bold text-white text-sm hover:text-amber-300 transition">
                                            {{ $client->shop_name }}
                                        </a>
                                        <p class="text-slate-400 text-[11px]">{{ $client->client_name }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Contact -->
                            <td class="py-4 px-4 space-y-0.5">
                                <div class="flex items-center space-x-1.5 text-slate-200">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    <span class="font-mono-code">{{ $client->mobile_number }}</span>
                                </div>
                                <div class="flex items-center space-x-1.5 text-slate-400 text-[11px]">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <span>{{ $client->email }}</span>
                                </div>
                            </td>

                            <!-- Assigned Pendrive Serials -->
                            <td class="py-4 px-4">
                                @if($client->pendriveRegistrations->count() > 0)
                                    <div class="space-y-1.5">
                                        @foreach($client->pendriveRegistrations->take(3) as $pendrive)
                                            <div class="flex items-center space-x-2">
                                                <span class="font-mono-code text-[11px] px-2 py-0.5 rounded bg-slate-900 border border-slate-700 text-amber-300">
                                                    {{ $pendrive->serial_number }}
                                                </span>
                                                <span class="w-1.5 h-1.5 rounded-full {{ $pendrive->status === 'active' ? 'bg-emerald-400' : 'bg-red-500' }}" title="{{ $pendrive->status }}"></span>
                                                <span class="text-slate-500 text-[10px]">{{ $pendrive->pendrive_model }}</span>
                                            </div>
                                        @endforeach
                                        @if($client->pendriveRegistrations->count() > 3)
                                            <span class="text-[10px] text-amber-400 font-semibold block">
                                                +{{ $client->pendriveRegistrations->count() - 3 }} more pendrive(s)
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-500 italic text-[11px]">No pendrives assigned yet</span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-4 px-4">
                                @if($client->status === 'active')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-950/70 text-emerald-300 border border-emerald-500/40">
                                        ACTIVE
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-950/70 text-red-300 border border-red-500/40">
                                        {{ strtoupper($client->status) }}
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-4 text-right space-x-2">
                                <a href="{{ route('admin.clients.show', $client) }}" 
                                    class="px-3 py-1.5 rounded-lg bg-amber-500/15 hover:bg-amber-500/25 text-amber-300 border border-amber-500/40 font-semibold transition text-xs inline-block">
                                    Manage USBs ({{ $client->pendriveRegistrations->count() }})
                                </a>

                                <a href="{{ route('admin.clients.edit', $client) }}" 
                                    class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold transition text-xs inline-block">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-500">
                                <svg class="w-10 h-10 mx-auto text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <p class="text-sm font-semibold text-slate-400">No clients found</p>
                                <p class="text-xs text-slate-500 mt-1">Add your first jeweller client to register their pendrive serial.</p>
                                <div class="mt-4">
                                    <a href="{{ route('admin.clients.create') }}" class="px-4 py-2 rounded-xl bg-amber-500 text-slate-950 font-bold text-xs inline-block">
                                        Add Client Now
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($clients->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $clients->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
